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
        $channel = $request->query('channel');
        $search = $request->query('search');

        $orders = Order::with(['customer', 'branch', 'cashier'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('production_status', $status))
            ->when($payStatus, fn ($q) => $q->where('payment_status', $payStatus))
            ->when($channel, fn ($q) => $q->where('order_type', $channel))
            ->when($search, function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhere('customer_name_walkin', 'like', "%{$search}%");
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
            'channel',
            'search',
            'productionStatuses',
            'isGlobalUser'
        ));
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Semua Cabang';
        $status = $request->query('status');
        $payStatus = $request->query('pay_status');
        $search = $request->query('search');

        $orders = Order::with(['customer', 'branch', 'cashier', 'items.service'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('production_status', $status))
            ->when($payStatus, fn ($q) => $q->where('payment_status', $payStatus))
            ->when($search, function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.orders_pdf', compact('orders', 'branchName'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('orders_' . now()->format('Ymd_His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Semua Cabang';
        $status = $request->query('status');
        $payStatus = $request->query('pay_status');
        $search = $request->query('search');

        $orders = Order::with(['customer', 'branch', 'cashier', 'items.service'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('production_status', $status))
            ->when($payStatus, fn ($q) => $q->where('payment_status', $payStatus))
            ->when($search, function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\GenericViewExport('exports.orders_pdf', compact('orders', 'branchName'), 'Order Transactions'),
            'orders_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
