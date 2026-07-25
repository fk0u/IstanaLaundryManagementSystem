<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\OrderSequenceCounter;
use App\Services\CRM\LoyaltyService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class POSController extends Controller
{
    protected LoyaltyService $loyaltyService;
    protected AuditLogService $auditLogService;

    public function __construct(LoyaltyService $loyaltyService, AuditLogService $auditLogService)
    {
        $this->loyaltyService = $loyaltyService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * Show POS Interface.
     */
    public function index(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        
        // Default to first branch if none scoped (e.g. Developer/Owner)
        if (!$branchId) {
            $firstBranch = Branch::first();
            $branchId = $firstBranch?->id;
        }

        $branch = Branch::find($branchId);

        // Fetch Services with branch-specific prices
        $services = Service::where('is_active', true)->get()->map(function ($service) use ($branchId) {
            $branchPrice = $service->branchPrices()->where('branch_id', $branchId)->first();
            $service->price = $branchPrice ? $branchPrice->price : $service->base_price;
            return $service;
        });

        // Fetch Customers (automatically filtered if non-super due to BranchScoped global scope)
        $customers = Customer::where('branch_id', $branchId)->get();

        // Fetch active Promotions
        $promotions = Promotion::where('is_active', true)
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->get();

        return view('pos.index', compact('branch', 'services', 'customers', 'promotions'));
    }

    /**
     * Store new Order from POS.
     */
    public function store(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        if (!$branchId) {
            $firstBranch = Branch::first();
            $branchId = $firstBranch?->id;
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
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
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        return DB::transaction(function () use ($data, $branchId, $request) {
            $customer = $data['customer_id'] ? Customer::find($data['customer_id']) : null;
            
            // Calculate Subtotal
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
                    'discount' => 0, // Item level discount (not used here)
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            // Calculate Promo Discount
            $discountAmount = 0;
            $promo = null;
            if (!empty($data['promo_id'])) {
                $promo = Promotion::findOrFail($data['promo_id']);
                
                // Validate promo criteria
                if ($subtotal >= $promo->min_transaction) {
                    if ($promo->type === 'percent') {
                        $discountAmount = $subtotal * ($promo->value / 100);
                    } elseif ($promo->type === 'nominal') {
                        $discountAmount = $promo->value;
                    }
                    
                    // Cap discount
                    if ($discountAmount > $subtotal) {
                        $discountAmount = $subtotal;
                    }
                }
            }

            // Calculate Loyalty Points Discount (1 point = Rp 1)
            $pointsUsed = 0;
            if ($customer && !empty($data['points_used']) && $data['points_used'] > 0) {
                $pointsUsed = min($data['points_used'], $customer->loyalty_points);
                
                // Maximum discount cannot exceed subtotal - promo discount
                $maxPointsDiscount = $subtotal - $discountAmount;
                if ($pointsUsed > $maxPointsDiscount) {
                    $pointsUsed = $maxPointsDiscount;
                }
            }

            // Calculate Total
            $taxAmount = 0; // Tax is 0 for simplicity
            $total = max(0, $subtotal - $discountAmount - $pointsUsed + $taxAmount);

            // Enforce paid_amount >= total for cash and transfer
            if (in_array($data['payment_method'], ['cash', 'transfer']) && $data['paid_amount'] < $total) {
                throw ValidationException::withMessages([
                    'paid_amount' => ['Jumlah bayar tidak boleh kurang dari total tagihan untuk metode tunai atau transfer.'],
                ]);
            }

            // Determine Payment Status
            $paidAmount = $data['paid_amount'];
            $changeAmount = 0;
            $paymentStatus = 'pending';
            $paidAt = null;

            if ($data['payment_method'] === 'invoice') {
                $paymentStatus = 'pending';
            } else {
                if ($paidAmount >= $total) {
                    $paymentStatus = 'paid';
                    $changeAmount = $paidAmount - $total;
                    $paidAt = now();
                } elseif ($paidAmount > 0) {
                    $paymentStatus = 'partial';
                }
            }

            // Generate Order Number (SMD01-YYYYMM-XXXX)
            $orderNumber = $this->generateOrderNumber($branchId);

            // Create Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'branch_id' => $branchId,
                'customer_id' => $customer?->id,
                'cashier_id' => Auth::id(),
                'promo_id' => $promo?->id,
                'production_status' => 'TERIMA',
                'payment_method' => $data['payment_method'],
                'payment_status' => $paymentStatus,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'points_used' => $pointsUsed,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => min($paidAmount, $total),
                'change_amount' => $changeAmount,
                'notes' => $data['notes'] ?? null,
                'paid_at' => $paidAt,
                'estimated_done_at' => now()->addDays(2), // Default 2 days delivery
            ]);

            // Create Order Items
            foreach ($itemsToCreate as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            // Deduct loyalty points if used
            if ($pointsUsed > 0 && $customer) {
                $this->loyaltyService->redeemPoints($customer, $pointsUsed, $order);
            }

            // Award loyalty points if paid
            if ($paymentStatus === 'paid') {
                $this->loyaltyService->awardPoints($order);
            }

            // Update promo usage limit
            if ($promo) {
                $promo->increment('usage_count');
            }

            // Log activity to audit_logs
            $this->auditLogService->log('create_order', $order);

            return redirect()->route('pos.index')->with('success', "Order #{$orderNumber} berhasil dibuat!");
        });
    }

    /**
     * Generate Unique Order Number.
     */
    protected function generateOrderNumber($branchId): string
    {
        $branch = Branch::findOrFail($branchId);
        $yearMonth = now()->format('Ym');

        return DB::transaction(function () use ($branch, $yearMonth) {
            $counter = OrderSequenceCounter::where('branch_id', $branch->id)
                ->where('year_month', $yearMonth)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
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

            return sprintf('%s-%s-%04d', $branch->code, $yearMonth, $seq);
        });
    }
}
