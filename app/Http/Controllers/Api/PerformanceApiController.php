<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceApiController extends Controller
{
    public function cashiers()
    {
        $cashiers = User::role(['Cashier', 'Branch_Admin', 'Developer', 'Super_Admin', 'Owner'])->get();

        $performance = $cashiers->map(function ($cashier) {
            $orders = Order::where('cashier_id', $cashier->id)
                ->whereNotIn('production_status', ['BATAL'])
                ->get();

            return [
                'cashier_id' => $cashier->id,
                'name' => $cashier->name,
                'branch_id' => $cashier->branch_id,
                'total_orders' => $orders->count(),
                'total_revenue' => (float) $orders->sum('total'),
                'avg_order_value' => $orders->count() > 0 ? (float) ($orders->sum('total') / $orders->count()) : 0,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $performance,
        ]);
    }

    public function branches()
    {
        $branches = Branch::where('is_active', true)->get();

        $performance = $branches->map(function ($branch) {
            $orders = Order::where('branch_id', $branch->id)
                ->whereNotIn('production_status', ['BATAL'])
                ->get();

            return [
                'branch_id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'total_orders' => $orders->count(),
                'total_revenue' => (float) $orders->sum('total'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $performance,
        ]);
    }

    public function auditLogs(Request $request)
    {
        $logs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }
}
