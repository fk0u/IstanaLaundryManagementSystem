<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branches = $isGlobalUser ? Branch::orderBy('name')->get() : collect();
        $status = $request->query('status');
        $payStatus = $request->query('pay_status');
        $search = $request->query('search');

        $orders = Order::with(['customer', 'branch', 'cashier'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('production_status', $status))
            ->when($payStatus, fn ($q) => $q->where('payment_status', $payStatus))
            ->when($search, function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $productionStatuses = ['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK', 'SIAP', 'DIAMBIL'];

        return view('orders.index', compact(
            'orders',
            'branches',
            'branchId',
            'status',
            'payStatus',
            'search',
            'productionStatuses',
            'isGlobalUser'
        ));
    }
}
