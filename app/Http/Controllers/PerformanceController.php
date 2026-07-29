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
}
