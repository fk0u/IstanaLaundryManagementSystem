<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderApiController extends Controller
{
    /**
     * GET /api/v1/orders
     * List transactions with pagination & filters.
     */
    public function index(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;

        $query = Order::with(['customer', 'branch', 'cashier'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name_walkin', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->query('status')) {
            $query->where('production_status', $status);
        }

        if ($paymentStatus = $request->query('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($startDate = $request->query('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->query('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    /**
     * GET /api/v1/orders/{order}
     * Order detail.
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'branch', 'cashier', 'items.service', 'payments', 'productionStatusLogs.updater']);

        return response()->json([
            'status' => 'success',
            'data' => $order,
        ]);
    }

    /**
     * POST /api/v1/orders/{order}/payments
     * Add payment / settlement for an order.
     */
    public function storePayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer,qr_qris,debit,credit',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $order) {
            $amount = (float) $validated['amount'];
            $newPaid = (float) $order->paid_amount + $amount;
            $total = (float) $order->total;

            $newStatus = 'partial';
            if ($newPaid >= $total) {
                $newStatus = 'paid';
            }

            OrderPayment::create([
                'order_id' => $order->id,
                'cashier_id' => auth()->id() ?? $order->cashier_id,
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? 'Pelunasan via API',
                'paid_at' => now(),
            ]);

            $order->update([
                'paid_amount' => $newPaid,
                'payment_status' => $newStatus,
                'paid_at' => $newStatus === 'paid' ? now() : $order->paid_at,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil ditambahkan!',
                'data' => [
                    'order_id' => $order->id,
                    'paid_amount' => $order->paid_amount,
                    'remaining_balance' => $order->remaining_balance,
                    'payment_status' => $order->payment_status,
                ],
            ]);
        });
    }
}
