<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\ProductionStatusLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        // Date filter: default to current month
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->query('date_to', now()->toDateString());

        $branches = Branch::orderBy('name')->get();

        // 1. Cashiers Leaderboard with daily breakdown
        $cashiersQuery = User::role(['Cashier', 'Branch_Admin', 'Owner', 'Super_Admin', 'Developer'])
            ->withCount(['orders as total_orders' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }])
            ->withSum(['orders as total_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->where('payment_status', 'paid')
                  ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->withSum(['orders as total_pending_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->whereIn('payment_status', ['pending', 'partial'])
                  ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total');

        $cashiers = $cashiersQuery->get()
            ->filter(fn ($u) => $u->total_orders > 0)
            ->sortByDesc('total_revenue');

        // 2. Cashier daily transaction details (last 7 days for selected cashier, or top cashier)
        $cashierDailyBreakdown = collect();
        if ($branchId || $isGlobalUser) {
            $cashierDailyBreakdown = Order::query()
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    'cashier_id',
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as paid_revenue'),
                    DB::raw('SUM(CASE WHEN payment_status IN ("pending","partial") THEN total ELSE 0 END) as pending_revenue'),
                    DB::raw('SUM(discount_amount) as total_discount'),
                )
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
                ->groupBy(DB::raw('DATE(created_at)'), 'cashier_id')
                ->with('cashier:id,name')
                ->orderByDesc('date')
                ->get();
        }

        // 3. Workshop Staff Productivity with date filter
        $staffProductivity = ProductionStatusLog::query()
            ->join('users', 'production_status_logs.updated_by', '=', 'users.id')
            ->join('orders', 'production_status_logs.order_id', '=', 'orders.id')
            ->when($branchId, fn ($q) => $q->where('orders.branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(production_status_logs.created_at)'), [$dateFrom, $dateTo])
            ->select(
                'users.name as staff_name',
                DB::raw('COUNT(production_status_logs.id) as total_actions'),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'SIAP' THEN 1 ELSE 0 END) as completed_orders"),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'DIAMBIL' THEN 1 ELSE 0 END) as picked_up_orders"),
                DB::raw("COUNT(DISTINCT production_status_logs.order_id) as unique_orders"),
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_actions')
            ->take(20)
            ->get();

        // 4. Operational Performance Metrics
        $totalActiveOrders = Order::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotIn('production_status', ['DIAMBIL'])
            ->count();

        $overdueOrders = Order::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotIn('production_status', ['DIAMBIL'])
            ->where('estimated_done_at', '<', now())
            ->count();

        $overdueRate = $totalActiveOrders > 0 ? round(($overdueOrders / $totalActiveOrders) * 100, 1) : 0;

        // 5. Period summary stats
        $periodRevenue = Order::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('payment_status', 'paid')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->sum('total');

        $periodOrders = Order::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->count();

        $periodAvgOrder = $periodOrders > 0 ? $periodRevenue / $periodOrders : 0;

        return view('performance.index', compact(
            'branches',
            'branchId',
            'dateFrom',
            'dateTo',
            'cashiers',
            'cashierDailyBreakdown',
            'staffProductivity',
            'totalActiveOrders',
            'overdueOrders',
            'overdueRate',
            'periodRevenue',
            'periodOrders',
            'periodAvgOrder'
        ));
    }

    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->query('date_to', now()->toDateString());

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Seluruh Cabang';

        $fileName = 'laporan-kinerja-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Gather Data
        $cashiers = User::role(['Cashier', 'Branch_Admin', 'Owner', 'Super_Admin', 'Developer'])
            ->withCount(['orders as total_orders' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }])
            ->withSum(['orders as total_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->where('payment_status', 'paid')
                  ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->withSum(['orders as total_pending_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->whereIn('payment_status', ['pending', 'partial'])
                  ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->get()
            ->filter(fn ($u) => $u->total_orders > 0)
            ->sortByDesc('total_revenue');

        $cashierDailyBreakdown = Order::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                'cashier_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as paid_revenue'),
                DB::raw('SUM(CASE WHEN payment_status IN ("pending","partial") THEN total ELSE 0 END) as pending_revenue'),
                DB::raw('SUM(discount_amount) as total_discount'),
            )
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->groupBy(DB::raw('DATE(created_at)'), 'cashier_id')
            ->with('cashier:id,name')
            ->orderByDesc('date')
            ->get();

        $staffProductivity = ProductionStatusLog::query()
            ->join('users', 'production_status_logs.updated_by', '=', 'users.id')
            ->join('orders', 'production_status_logs.order_id', '=', 'orders.id')
            ->when($branchId, fn ($q) => $q->where('orders.branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(production_status_logs.created_at)'), [$dateFrom, $dateTo])
            ->select(
                'users.name as staff_name',
                DB::raw('COUNT(production_status_logs.id) as total_actions'),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'SIAP' THEN 1 ELSE 0 END) as completed_orders"),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'DIAMBIL' THEN 1 ELSE 0 END) as picked_up_orders"),
                DB::raw("COUNT(DISTINCT production_status_logs.order_id) as unique_orders"),
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_actions')
            ->take(50)
            ->get();

        $periodRevenue = Order::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('payment_status', 'paid')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->sum('total');

        $periodOrders = Order::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->count();

        $periodAvgOrder = $periodOrders > 0 ? $periodRevenue / $periodOrders : 0;

        $callback = function () use ($branchName, $dateFrom, $dateTo, $cashiers, $cashierDailyBreakdown, $staffProductivity, $periodRevenue, $periodOrders, $periodAvgOrder) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ISTANA LAUNDRY ERP — LAPORAN KINERJA & PRODUKTIVITAS']);
            fputcsv($file, ["Cabang: {$branchName}", "Periode: {$dateFrom} s/d {$dateTo}"]);
            fputcsv($file, []);

            // Summary
            fputcsv($file, ['METRIK KPI', 'NILAI']);
            fputcsv($file, ['Total Pendapatan Lunas (Rp)', number_format($periodRevenue, 2, '.', '')]);
            fputcsv($file, ['Total Nota Terbuat', $periodOrders]);
            fputcsv($file, ['Rata-rata Nota / Basket Size (Rp)', number_format($periodAvgOrder, 2, '.', '')]);
            fputcsv($file, []);

            // Leaderboard Kasir
            fputcsv($file, ['--- LEADERBOARD OMSET KASIR ---']);
            fputcsv($file, ['NAMA KASIR', 'TOTAL NOTA', 'OMSET LUNAS (RP)', 'OMSET PENDING (RP)']);
            foreach ($cashiers as $c) {
                fputcsv($file, [$c->name, $c->total_orders, number_format($c->total_revenue ?? 0, 2, '.', ''), number_format($c->total_pending_revenue ?? 0, 2, '.', '')]);
            }
            fputcsv($file, []);

            // Daily breakdown
            fputcsv($file, ['--- RINCIAN TRANSAKSI HARIAN PER KASIR ---']);
            fputcsv($file, ['TANGGAL', 'NAMA KASIR', 'JUMLAH NOTA', 'OMSET LUNAS (RP)', 'OMSET PENDING (RP)', 'TOTAL DISKON (RP)']);
            foreach ($cashierDailyBreakdown as $row) {
                fputcsv($file, [$row->date, $row->cashier?->name ?? '-', $row->total_orders, number_format($row->paid_revenue, 2, '.', ''), number_format($row->pending_revenue, 2, '.', ''), number_format($row->total_discount, 2, '.', '')]);
            }
            fputcsv($file, []);

            // Staff workshop
            fputcsv($file, ['--- PRODUKTIVITAS STAF WORKSHOP ---']);
            fputcsv($file, ['NAMA STAF', 'TOTAL AKSI STATUS', 'ORDER SIAP', 'ORDER DIAMBIL', 'ORDER UNIK']);
            foreach ($staffProductivity as $st) {
                fputcsv($file, [$st->staff_name, $st->total_actions, $st->completed_orders, $st->picked_up_orders, $st->unique_orders]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
