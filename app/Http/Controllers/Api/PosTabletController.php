<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSequenceCounter;
use App\Models\Promotion;
use App\Models\Service;
use App\Services\AuditLogService;
use App\Services\CRM\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PosTabletController extends Controller
{
    protected LoyaltyService $loyaltyService;

    protected AuditLogService $auditLogService;

    public function __construct(LoyaltyService $loyaltyService, AuditLogService $auditLogService)
    {
        $this->loyaltyService = $loyaltyService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get active services with branch specific prices for POS Tablet.
     */
    public function services(Request $request)
    {
        $branchId = $request->user()->branch_id ?? Branch::first()?->id;

        $services = Service::where('is_active', true)->get()->map(function ($service) use ($branchId) {
            $branchPrice = $service->branchPrices()->where('branch_id', $branchId)->first();

            return [
                'id' => $service->id,
                'name' => $service->name,
                'unit' => $service->unit,
                'category' => $service->category,
                'price' => (float) ($branchPrice ? $branchPrice->price : $service->base_price),
            ];
        });

        return response()->json([
            'status' => 'success',
            'branch_id' => $branchId,
            'data' => $services,
        ]);
    }

    /**
     * Get customers list for tablet autocomplete.
     */
    public function customers(Request $request)
    {
        $branchId = $request->user()->branch_id ?? Branch::first()?->id;
        $search = trim($request->query('q', ''));

        $customers = Customer::where('branch_id', $branchId)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            })
            ->select(['id', 'name', 'phone', 'member_code', 'loyalty_points', 'loyalty_tier'])
            ->limit(50)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $customers,
        ]);
    }

    /**
     * Create order from POS Tablet.
     */
    public function storeOrder(Request $request)
    {
        $user = $request->user();
        $branchId = $user->branch_id ?? Branch::first()?->id;

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
            'promo_id' => 'nullable|exists:promotions,id',
            'points_used' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,transfer,invoice',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            $order = DB::transaction(function () use ($data, $branchId, $user) {
                $customer = Customer::findOrFail($data['customer_id']);

                $subtotal = 0;
                $itemsToCreate = [];

                foreach ($data['items'] as $item) {
                    $service = Service::findOrFail($item['service_id']);
                    $price = $service->branchPrices()->where('branch_id', $branchId)->first()?->price ?? $service->base_price;

                    $itemSubtotal = $price * $item['quantity'];
                    $subtotal += $itemSubtotal;

                    $itemsToCreate[] = [
                        'service_id' => $service->id,
                        'quantity' => $item['quantity'],
                        'unit' => $service->unit,
                        'unit_price' => $price,
                        'discount' => 0,
                        'subtotal' => $itemSubtotal,
                        'notes' => $item['notes'] ?? null,
                    ];
                }

                $discountAmount = 0;
                $promo = null;
                if (! empty($data['promo_id'])) {
                    $promo = Promotion::findOrFail($data['promo_id']);
                    if ($subtotal >= $promo->min_transaction) {
                        $discountAmount = $promo->type === 'percent'
                            ? min($subtotal, $subtotal * ($promo->value / 100))
                            : min($subtotal, $promo->value);
                    }
                }

                $pointsUsed = 0;
                if (! empty($data['points_used']) && $data['points_used'] > 0) {
                    $pointsUsed = min($data['points_used'], $customer->loyalty_points, max(0, $subtotal - $discountAmount));
                }

                $total = max(0, $subtotal - $discountAmount - $pointsUsed);
                $paidAmount = (float) $data['paid_amount'];
                $changeAmount = 0;
                $paymentStatus = 'pending';
                $paidAt = null;

                if ($data['payment_method'] !== 'invoice') {
                    if ($paidAmount >= $total) {
                        $paymentStatus = 'paid';
                        $changeAmount = $paidAmount - $total;
                        $paidAt = now();
                    } elseif ($paidAmount > 0) {
                        $paymentStatus = 'partial';
                    }
                }

                $branch = Branch::findOrFail($branchId);
                $yearMonth = now()->format('Ym');

                $counter = OrderSequenceCounter::where('branch_id', $branch->id)
                    ->where('year_month', $yearMonth)
                    ->lockForUpdate()
                    ->first();

                if (! $counter) {
                    $counter = OrderSequenceCounter::create([
                        'branch_id' => $branch->id,
                        'year_month' => $yearMonth,
                        'last_sequence' => 1,
                    ]);
                    $seq = 1;
                } else {
                    $counter->increment('last_sequence');
                    $seq = $counter->last_sequence;
                }

                $orderNumber = sprintf('%s-%s-%04d', $branch->code, $yearMonth, $seq);

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'cashier_id' => $user->id,
                    'promo_id' => $promo?->id,
                    'production_status' => 'TERIMA',
                    'payment_method' => $data['payment_method'],
                    'payment_status' => $paymentStatus,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'points_used' => $pointsUsed,
                    'tax_amount' => 0,
                    'total' => $total,
                    'paid_at' => $paidAt,
                    'estimated_done_at' => now()->addDays(2),
                ]);

                foreach ($itemsToCreate as $itemData) {
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }

                if ($pointsUsed > 0) {
                    $this->loyaltyService->redeemPoints($customer, $pointsUsed, $order);
                }
                if ($paymentStatus === 'paid') {
                    $this->loyaltyService->awardPoints($order);
                }
                if ($promo) {
                    $promo->increment('usage_count');
                }

                $this->auditLogService->log('create_order_api', $order);

                return $order;
            });

            return response()->json([
                'status' => 'success',
                'message' => "Order #{$order->order_number} berhasil dibuat!",
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => (float) $order->total,
                    'payment_status' => $order->payment_status,
                    'tracking_url' => url("/track?order_number={$order->order_number}"),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
