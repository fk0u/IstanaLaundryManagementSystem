<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Refund;
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

    /**
     * POST /api/v1/orders/{order}/refund
     * Submit order refund request.
     */
    public function refund(Request $request, Order $order)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:1000',
        ]);

        if ($validated['amount'] > $order->total) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah refund tidak boleh melebihi total transaksi order.',
            ], 422);
        }

        $exists = Refund::where('order_id', $order->id)->where('status', '!=', 'rejected')->exists();
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permohonan refund untuk order ini sudah diajukan.',
            ], 422);
        }

        $refund = Refund::create([
            'order_id' => $order->id,
            'branch_id' => $order->branch_id,
            'requested_by' => auth()->id() ?? 1,
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'status' => 'pending',
            'cashier_approved_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Permintaan refund order berhasil diajukan!',
            'data' => $refund,
        ], 201);
    }

    /**
     * GET /api/v1/orders/{order}/receipt-data
     * Return structured JSON for Bluetooth Thermal Printer printing (ESC/POS).
     */
    public function receiptData(Order $order)
    {
        $order->load(['customer', 'branch', 'cashier', 'items.service', 'payments']);

        $customerName = $order->customer ? $order->customer->name : ($order->customer_name_walkin ?? 'Pelanggan Walk-in');
        $customerPhone = $order->customer ? $order->customer->phone : ($order->customer_phone_walkin ?? '-');

        $receiptItems = $order->items->map(function ($item) {
            return [
                'name' => $item->service ? $item->service->name : 'Layanan Laundry',
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit ?? 'kg',
                'price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'store' => [
                    'name' => 'ISTANA LAUNDRY',
                    'branch' => $order->branch ? $order->branch->name : 'Samarinda Utama',
                    'address' => $order->branch ? $order->branch->address : 'Samarinda',
                    'phone' => $order->branch ? $order->branch->phone : '-',
                ],
                'order_number' => $order->order_number,
                'created_at' => $order->created_at->format('d/m/Y H:i').' WITA',
                'cashier' => $order->cashier ? $order->cashier->name : 'Kasir',
                'customer' => [
                    'name' => $customerName,
                    'phone' => $customerPhone,
                ],
                'items' => $receiptItems,
                'totals' => [
                    'subtotal' => (float) $order->subtotal,
                    'discount' => (float) $order->discount,
                    'grand_total' => (float) $order->total,
                    'paid_amount' => (float) $order->paid_amount,
                    'remaining' => (float) $order->remaining_balance,
                    'payment_status' => strtoupper($order->payment_status),
                ],
            ],
        ]);
    }
}
