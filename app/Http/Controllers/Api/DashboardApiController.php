<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DashboardApiController extends Controller
{
    /**
     * GET /api/v1/dashboard/stats
     * Return executive KPI stats for dashboard cards.
     */
    public function stats()
    {
        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;

        $query = Order::query()->whereNotIn('production_status', ['BATAL']);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $todayQuery = (clone $query)->whereDate('created_at', now()->toDateString());

        $totalRevenueToday = (float) (clone $todayQuery)->sum('paid_amount');
        $totalOrdersToday = (clone $todayQuery)->count();
        $activeOrdersCount = (clone $query)->whereNotIn('production_status', ['SIAP', 'DIAMBIL'])->count();
        $readyForPickupCount = (clone $query)->where('production_status', 'SIAP')->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'revenue_today' => $totalRevenueToday,
                'orders_today_count' => $totalOrdersToday,
                'active_orders_count' => $activeOrdersCount,
                'ready_for_pickup_count' => $readyForPickupCount,
                'timestamp' => now()->format('Y-m-d H:i:s').' (UTC+8)',
            ],
        ]);
    }

    /**
     * GET /api/v1/dashboard/charts
     * Return monthly sales & top services chart data.
     */
    public function charts()
    {
        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;

        $orders = Order::query()
            ->whereNotIn('production_status', ['BATAL'])
            ->whereYear('created_at', now()->year)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $monthlySales = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlySales[$m] = (float) $orders->filter(fn ($o) => $o->created_at->month === $m)->sum('total');
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'year' => now()->year,
                'monthly_sales' => $monthlySales,
            ],
        ]);
    }
}
