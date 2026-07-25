<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Models\DepreciationSchedule;
use App\Models\Branch;
use App\Models\ChartOfAccount;
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
        if (!$isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branches = Branch::orderBy('name')->get();

        $assets = FixedAsset::with(['branch', 'account'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('asset_code')
            ->paginate(15);

        $accounts = ChartOfAccount::where('type', 'asset')->orderBy('code')->get();

        return view('assets.index', compact('assets', 'branches', 'branchId', 'accounts'));
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
            ]);

            // Generate Depreciation Schedule
            $cost = $request->acquisition_cost;
            $salvage = $request->salvage_value;
            $months = $request->useful_life_months;
            $monthlyDep = ($cost - $salvage) / $months;

            $accumulated = 0;
            $startDate = \Carbon\Carbon::parse($request->acquisition_date)->startOfMonth();

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
}
