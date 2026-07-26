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

        $branches = Branch::orderBy('name')->get();

        // 1. Cashiers Leaderboard (Omset & Total Order)
        $cashiersQuery = User::role(['Cashier', 'Branch_Admin', 'Owner', 'Super_Admin', 'Developer'])
            ->withCount(['orders as total_orders' => function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            }])
            ->withSum(['orders as total_revenue' => function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->where('payment_status', 'paid');
            }], 'total');

        $cashiers = $cashiersQuery->get()->filter(fn ($u) => $u->total_orders > 0)->sortByDesc('total_revenue');

        // 2. Workshop Staff Productivity (Production Status Updates)
        $staffProductivity = ProductionStatusLog::query()
            ->join('users', 'production_status_logs.updated_by', '=', 'users.id')
            ->join('orders', 'production_status_logs.order_id', '=', 'orders.id')
            ->when($branchId, fn ($q) => $q->where('orders.branch_id', $branchId))
            ->select(
                'users.name as staff_name',
                DB::raw('COUNT(production_status_logs.id) as total_actions'),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'SIAP' THEN 1 ELSE 0 END) as completed_orders")
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_actions')
            ->take(10)
            ->get();

        // 3. Operational Performance Metrics
        $totalActiveOrders = Order::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotIn('production_status', ['DIAMBIL'])
            ->count();

        $overdueOrders = Order::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotIn('production_status', ['DIAMBIL'])
            ->where('estimated_done_at', '<', now())
            ->count();

        $overdueRate = $totalActiveOrders > 0 ? round(($overdueOrders / $totalActiveOrders) * 100, 1) : 0;

        return view('performance.index', compact(
            'branches',
            'branchId',
            'cashiers',
            'staffProductivity',
            'totalActiveOrders',
            'overdueOrders',
            'overdueRate'
        ));
    }
}
