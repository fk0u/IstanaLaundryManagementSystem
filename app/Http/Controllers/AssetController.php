<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\DepreciationSchedule;
use App\Models\FixedAsset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branches = Branch::orderBy('name')->get();

        $assetsQuery = FixedAsset::with(['branch', 'account'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $allAssets = (clone $assetsQuery)->get();
        $assets = $assetsQuery->orderBy('asset_code')->paginate(15);

        $accounts = ChartOfAccount::where('type', 'asset')->orderBy('code')->get();

        // Analytics & Category Breakdown
        $totalCost = $allAssets->sum('acquisition_cost');
        $totalBookValue = $allAssets->sum('book_value');
        $totalDepreciation = $allAssets->sum('accumulated_depreciation');

        $conditionCounts = [
            'good' => $allAssets->where('condition', 'good')->count(),
            'fair' => $allAssets->where('condition', 'fair')->count(),
            'poor' => $allAssets->where('condition', 'poor')->count(),
            'scrapped' => $allAssets->where('condition', 'scrapped')->count(),
        ];

        $categoriesSummary = $allAssets->groupBy('category')->map(function ($items, $catName) {
            return [
                'name' => $catName,
                'count' => $items->count(),
                'total_cost' => $items->sum('acquisition_cost'),
                'total_book_value' => $items->sum('book_value'),
            ];
        });

        // Maintenance Alert Assets & Detailed Metrics
        $urgentMaintenanceAssets = $allAssets->filter(function ($ast) {
            $isOverdue = $ast->next_maintenance_date && $ast->next_maintenance_date->isPast();
            $isPoor = $ast->condition === 'poor';
            return $isOverdue || $isPoor;
        });

        $maintenanceUpcoming30Days = $allAssets->filter(function ($ast) {
            return $ast->next_maintenance_date &&
                ! $ast->next_maintenance_date->isPast() &&
                $ast->next_maintenance_date->diffInDays(now()) <= 30;
        });

        // 12-Month Depreciation Expense Forecast for Current Year
        $year = now()->year;
        $monthlyDepreciationForecast = [];
        for ($m = 1; $m <= 12; $m++) {
            $amount = DepreciationSchedule::whereHas('asset', function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->whereYear('period_date', $year)
            ->whereMonth('period_date', $m)
            ->sum('depreciation_amount');

            $monthlyDepreciationForecast[] = [
                'month_name' => Carbon::create()->month($m)->translatedFormat('M'),
                'amount' => (float) $amount,
            ];
        }

        return view('assets.index', compact(
            'assets',
            'branches',
            'branchId',
            'accounts',
            'totalCost',
            'totalBookValue',
            'totalDepreciation',
            'conditionCounts',
            'categoriesSummary',
            'urgentMaintenanceAssets',
            'maintenanceUpcoming30Days',
            'monthlyDepreciationForecast'
        ));
    }

    public function updateMaintenance(Request $request, FixedAsset $asset)
    {
        $request->validate([
            'last_maintenance_date' => 'required|date',
            'next_maintenance_date' => 'nullable|date|after_or_equal:last_maintenance_date',
            'condition' => 'required|in:good,fair,poor,scrapped',
            'maintenance_notes' => 'nullable|string',
        ]);

        $asset->update([
            'last_maintenance_date' => $request->last_maintenance_date,
            'next_maintenance_date' => $request->next_maintenance_date,
            'condition' => $request->condition,
            'maintenance_notes' => $request->maintenance_notes,
        ]);

        return redirect()->back()->with('success', 'Catatan maintenance & kondisi aset ' . $asset->asset_code . ' berhasil diperbarui!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_code' => 'required|string|unique:fixed_assets,asset_code',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'salvage_value' => 'required|numeric|min:0',
            'useful_life_months' => 'required|integer|min:1',
            'depreciation_method' => 'required|in:straight_line,declining_balance',
            'branch_id' => 'required|exists:branches,id',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'serial_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:100',
            'condition' => 'nullable|in:good,fair,poor,scrapped',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $asset = FixedAsset::create([
                'branch_id' => $request->branch_id,
                'account_id' => $request->account_id,
                'asset_code' => $request->asset_code,
                'name' => $request->name,
                'category' => $request->category,
                'acquisition_date' => $request->acquisition_date,
                'acquisition_cost' => $request->acquisition_cost,
                'salvage_value' => $request->salvage_value,
                'useful_life_months' => $request->useful_life_months,
                'depreciation_method' => $request->depreciation_method,
                'accumulated_depreciation' => 0,
                'book_value' => $request->acquisition_cost,
                'is_active' => true,
                'serial_number' => $request->serial_number,
                'supplier' => $request->supplier,
                'condition' => $request->condition ?? 'good',
                'notes' => $request->notes,
            ]);

            // Generate Depreciation Schedule
            $cost = $request->acquisition_cost;
            $salvage = $request->salvage_value;
            $months = $request->useful_life_months;
            $monthlyDep = ($cost - $salvage) / $months;

            $accumulated = 0;
            $startDate = Carbon::parse($request->acquisition_date)->startOfMonth();

            for ($i = 1; $i <= $months; $i++) {
                $accumulated += $monthlyDep;
                $bookValue = max($salvage, $cost - $accumulated);

                DepreciationSchedule::create([
                    'asset_id' => $asset->id,
                    'period_date' => $startDate->copy()->addMonths($i - 1)->format('Y-m-d'),
                    'depreciation_amount' => round($monthlyDep, 2),
                    'accumulated' => round($accumulated, 2),
                    'book_value' => round($bookValue, 2),
                    'is_posted' => false,
                ]);
            }
        });

        return redirect()->route('assets.index')->with('success', 'Aset tetap baru & jadwal depresiasi berhasil dibuat!');
    }

    public function show(FixedAsset $asset)
    {
        $asset->load(['branch', 'account', 'depreciationSchedules']);

        return view('assets.show', compact('asset'));
    }

    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Seluruh Cabang';

        $assets = FixedAsset::with(['branch', 'account'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('asset_code')
            ->get();

        $fileName = 'laporan-aset-tetap-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($branchName, $assets) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ISTANA LAUNDRY ERP — REKAPITULASI DAFTAR ASET TETAP & DEPRESIASI']);
            fputcsv($file, ["Cabang: {$branchName}", 'Tanggal Cetak: ' . now()->format('d/m/Y H:i')]);
            fputcsv($file, []);

            fputcsv($file, [
                'KODE ASET',
                'NAMA ASET',
                'KATEGORI',
                'CABANG',
                'TGL PEROLEHAN',
                'HARGA PEROLEHAN (RP)',
                'NILAI SISA (RP)',
                'UMUR MANFAAT (BLN)',
                'METODE DEPRESIASI',
                'AKUMULASI DEPRESIASI (RP)',
                'NILAI BUKU / BOOK VALUE (RP)',
                'NO. SERI',
                'SUPPLIER',
                'KONDISI',
                'STATUS'
            ]);

            $totalCost = 0;
            $totalBookValue = 0;

            foreach ($assets as $asset) {
                $totalCost += $asset->acquisition_cost;
                $totalBookValue += $asset->book_value;

                fputcsv($file, [
                    $asset->asset_code,
                    $asset->name,
                    $asset->category,
                    $asset->branch?->name ?? '-',
                    $asset->acquisition_date?->format('d/m/Y') ?? '-',
                    number_format($asset->acquisition_cost, 2, '.', ''),
                    number_format($asset->salvage_value, 2, '.', ''),
                    $asset->useful_life_months,
                    $asset->depreciation_method === 'straight_line' ? 'Garis Lurus' : 'Saldo Menurun',
                    number_format($asset->accumulated_depreciation, 2, '.', ''),
                    number_format($asset->book_value, 2, '.', ''),
                    $asset->serial_number ?? '-',
                    $asset->supplier ?? '-',
                    strtoupper($asset->condition ?? 'GOOD'),
                    $asset->is_active ? 'AKTIF' : 'NON-AKTIF'
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['', 'TOTAL KESELURUHAN', '', '', '', number_format($totalCost, 2, '.', ''), '', '', '', '', number_format($totalBookValue, 2, '.', '')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Seluruh Cabang';

        $assets = FixedAsset::with(['branch', 'account'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('asset_code')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.assets_pdf', compact('branchName', 'assets'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-aset-tetap-' . now()->format('Ymd-His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Seluruh Cabang';

        $assets = FixedAsset::with(['branch', 'account'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('asset_code')
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\GenericViewExport('exports.assets_pdf', compact('branchName', 'assets'), 'Aset Tetap'),
            'laporan-aset-tetap-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
