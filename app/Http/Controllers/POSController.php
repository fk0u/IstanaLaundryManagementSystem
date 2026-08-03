<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\DraftOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\OrderSequenceCounter;
use App\Models\PettyCashRecord;
use App\Models\Promotion;
use App\Models\Service;
use App\Services\AuditLogService;
use App\Services\CRM\LoyaltyService;
use App\Services\Finance\JournalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
        if (! $branchId) {
            $firstBranch = Branch::first();
            $branchId = $firstBranch?->id;
            if ($branchId) {
                session(['scoped_branch_id' => $branchId]);
            }
        }

        $branch = Branch::find($branchId);

        // Fetch active cashier shift for current user in this branch
        $activeShift = CashierShift::where('branch_id', $branchId)
            ->where('cashier_id', Auth::id())
            ->where('status', 'OPEN')
            ->first();

        // Fetch Services with branch-specific prices
        $services = Service::where('is_active', true)->get()->map(function ($service) use ($branchId) {
            $branchPrice = $service->branchPrices()->where('branch_id', $branchId)->first();
            $service->price = $branchPrice ? $branchPrice->price : $service->base_price;

            return $service;
        });

        // Popular services preset (top 6 active services)
        $popularServices = $services->take(6);

        // Unique categories for filtering
        $categories = $services->pluck('type')->unique()->values();

        // Fetch all global Customers (cross-branch membership support)
        $customers = Customer::where(function ($q) {
                $q->where('name', 'NOT LIKE', '%Walk-In%')
                    ->where('name', 'NOT LIKE', '%walk-in%')
                    ->where('name', 'NOT LIKE', '%Walk In%')
                    ->where('name', 'NOT LIKE', '%Pelanggan Umum%')
                    ->where('name', 'NOT LIKE', '%pelanggan umum%');
            })
            ->orderBy('name')
            ->get();

        // Fetch active Promotions (Global & Branch Specific)
        $promotions = Promotion::withoutGlobalScopes()
            ->where('is_active', true)
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderBy('name', 'asc')
            ->get();

        // Fetch pending draft orders
        $draftOrders = DraftOrder::where('branch_id', $branchId)
            ->where('cashier_id', Auth::id())
            ->with('customer')
            ->orderByDesc('created_at')
            ->get();

        $branches = Branch::orderBy('name')->get();

        $pointExchangeRate = (float) \App\Models\SystemSetting::get('point_exchange_rate', 1);
        $pointEarnSpendThreshold = (float) \App\Models\SystemSetting::get('point_earn_spend_threshold', 1000);
        $pointMinRedeem = (int) \App\Models\SystemSetting::get('point_min_redeem', 0);

        // Compute shiftSummary if an active shift exists
        $shiftSummary = null;
        if ($activeShift) {
            $shiftOrderPayments = OrderPayment::whereHas('order', function ($q) use ($activeShift) {
                $q->where('cashier_shift_id', $activeShift->id);
            })->get();

            $cashSales = (float) $shiftOrderPayments->where('payment_method', 'CASH')->sum('amount');
            $qrisSales = (float) $shiftOrderPayments->where('payment_method', 'QRIS')->sum('amount');
            $transferSales = (float) $shiftOrderPayments->where('payment_method', 'TRANSFER')->sum('amount');
            $debitSales = (float) $shiftOrderPayments->where('payment_method', 'DEBIT')->sum('amount');
            $dpSales = (float) $shiftOrderPayments->where('payment_method', 'DP')->sum('amount');

            $directCashOrders = Order::where('cashier_shift_id', $activeShift->id)
                ->whereDoesntHave('payments')
                ->whereIn('payment_method', ['cash', 'CASH'])
                ->sum('paid_amount');
            $cashSales += (float) $directCashOrders;

            $pettyCashOut = (float) $activeShift->pettyCashRecords()->sum('amount');
            $openingCash = (float) $activeShift->opening_cash;

            $expectedCashInDrawer = max(0, $openingCash + $cashSales - $pettyCashOut);
            $totalShiftOmset = $cashSales + $qrisSales + $transferSales + $debitSales + $dpSales;
            $ordersCount = Order::where('cashier_shift_id', $activeShift->id)->count();

            $shiftSummary = [
                'opening_cash' => $openingCash,
                'cash_sales' => $cashSales,
                'qris_sales' => $qrisSales,
                'transfer_sales' => $transferSales,
                'debit_sales' => $debitSales,
                'dp_sales' => $dpSales,
                'petty_cash_out' => $pettyCashOut,
                'expected_cash' => $expectedCashInDrawer,
                'total_omset' => $totalShiftOmset,
                'orders_count' => $ordersCount,
            ];
        }

        return view('pos.index', compact(
            'branch',
            'services',
            'popularServices',
            'categories',
            'customers',
            'promotions',
            'branches',
            'activeShift',
            'shiftSummary',
            'draftOrders',
            'pointExchangeRate',
            'pointEarnSpendThreshold',
            'pointMinRedeem'
        ));
    }

    /**
     * Open Cashier Shift.
     */
    public function openShift(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        if (! $branchId) {
            $branchId = Branch::first()?->id;
        }

        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $existingShift = CashierShift::where('branch_id', $branchId)
            ->where('cashier_id', Auth::id())
            ->where('status', 'OPEN')
            ->first();

        if ($existingShift) {
            return redirect()->route('pos.index')->with('error', 'Anda sudah memiliki shift aktif!');
        }

        $shift = CashierShift::create([
            'branch_id' => $branchId,
            'cashier_id' => Auth::id(),
            'opened_at' => now(),
            'opening_cash' => $validated['opening_cash'],
            'status' => 'OPEN',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('pos.index')->with('success', "Shift Kasir #{$shift->id} berhasil dibuka dengan Modal Kas Rp ".number_format($shift->opening_cash, 0, ',', '.'));
    }

    /**
     * Close Cashier Shift & Calculate Difference.
     */
    public function closeShift(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        if (! $branchId) {
            $branchId = Branch::first()?->id;
        }

        $shift = CashierShift::where('branch_id', $branchId)
            ->where('cashier_id', Auth::id())
            ->where('status', 'OPEN')
            ->firstOrFail();

        $validated = $request->validate([
            'closing_cash_actual' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Calculate exact cash transactions under this shift from order_payments
        $shiftOrderPayments = OrderPayment::whereHas('order', function ($q) use ($shift) {
            $q->where('cashier_shift_id', $shift->id);
        })->get();

        $cashSales = (float) $shiftOrderPayments->where('payment_method', 'CASH')->sum('amount');
        $directCashOrders = Order::where('cashier_shift_id', $shift->id)
            ->whereDoesntHave('payments')
            ->whereIn('payment_method', ['cash', 'CASH'])
            ->sum('paid_amount');
        $cashSales += (float) $directCashOrders;

        $pettyCashTotal = (float) $shift->pettyCashRecords()->sum('amount');

        // Total cash expected in drawer = Opening Cash + Cash Sales - Petty Cash Expenses
        $closingCashSystem = (float) $shift->opening_cash + (float) $cashSales - (float) $pettyCashTotal;
        $closingCashActual = (float) $validated['closing_cash_actual'];
        $cashDifference = $closingCashActual - $closingCashSystem;

        $shift->update([
            'closed_at' => now(),
            'closing_cash_system' => $closingCashSystem,
            'closing_cash_actual' => $closingCashActual,
            'cash_difference' => $cashDifference,
            'petty_cash_total' => $pettyCashTotal,
            'status' => 'CLOSED',
            'notes' => $validated['notes'] ?? $shift->notes,
        ]);

        return redirect()->route('pos.index')
            ->with('success', "Shift Kasir #{$shift->id} telah ditutup. Rekapitulasi berhasil dibuat.")
            ->with('closed_shift_id', $shift->id);
    }

    /**
     * Store Petty Cash expense record.
     */
    public function storePettyCash(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        if (! $branchId) {
            $branchId = Branch::first()?->id;
        }

        $shift = CashierShift::where('branch_id', $branchId)
            ->where('cashier_id', Auth::id())
            ->where('status', 'OPEN')
            ->firstOrFail();

        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:500',
        ]);

        $pettyCash = PettyCashRecord::create([
            'cashier_shift_id' => $shift->id,
            'branch_id' => $branchId,
            'user_id' => Auth::id(),
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
        ]);

        $shift->increment('petty_cash_total', $validated['amount']);

        return redirect()->route('pos.index')->with('success', "Pengeluaran Kas Kecil Rp ".number_format($pettyCash->amount, 0, ',', '.')." berhasil dicatat.");
    }

    /**
     * Store Draft Order (Hold Order).
     */
    public function storeDraft(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        if (! $branchId) {
            $branchId = Branch::first()?->id;
        }

        $validated = $request->validate([
            'draft_name' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'cart_data' => 'required|array',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $draft = DraftOrder::create([
            'branch_id' => $branchId,
            'cashier_id' => Auth::id(),
            'customer_id' => $validated['customer_id'] ?? null,
            'draft_name' => $validated['draft_name'],
            'cart_data' => $validated['cart_data'],
            'total_amount' => $validated['total_amount'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil disimpan sementara (Hold).',
            'draft' => $draft->load('customer'),
        ]);
    }

    /**
     * Delete Draft Order.
     */
    public function destroyDraft($id)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        $draft = DraftOrder::where('branch_id', $branchId)->findOrFail($id);
        $draft->delete();

        return response()->json([
            'success' => true,
            'message' => 'Draft transaksi berhasil dihapus.',
        ]);
    }

    /**
     * Add payment settlement for unpaid / partial invoice order.
     */
    public function addPayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,qris,transfer,debit',
            'amount' => 'required|numeric|min:1',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($order, $validated) {
            $payment = OrderPayment::create([
                'order_id' => $order->id,
                'payment_method' => strtoupper($validated['payment_method']),
                'amount' => $validated['amount'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'cashier_id' => Auth::id(),
                'paid_at' => now(),
            ]);

            $newPaidAmount = (float) $order->paid_amount + (float) $validated['amount'];
            $order->paid_amount = min($newPaidAmount, (float) $order->total);

            if ($order->paid_amount >= (float) $order->total) {
                $order->payment_status = 'paid';
                $order->paid_at = now();
                $this->loyaltyService->awardPoints($order);
                try {
                    app(JournalService::class)->postOrderJournal($order);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("POS Journal Auto-Post failed for order #{$order->id}: {$e->getMessage()}");
                }
            } else {
                $order->payment_status = 'partial';
            }

            $order->save();

            return redirect()->back()->with('success', "Pembayaran sebesar Rp ".number_format($validated['amount'], 0, ',', '.')." berhasil ditambahkan untuk Order #{$order->order_number}");
        });
    }

    /**
     * Quick-create a customer from the POS screen.
     */
    public function storeCustomer(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        if (! $branchId) {
            $firstBranch = Branch::first();
            $branchId = $firstBranch?->id;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'member_code' => 'CUST-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
            'loyalty_tier' => 'Bronze',
            'loyalty_points' => 0,
        ]);

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'loyalty_points' => $customer->loyalty_points,
                'loyalty_tier' => $customer->loyalty_tier,
            ],
        ], 201);
    }

    /**
     * Store new Order from POS.
     */
    public function store(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        if (! $branchId) {
            $firstBranch = Branch::first();
            $branchId = $firstBranch?->id;
            if ($branchId) {
                session(['scoped_branch_id' => $branchId]);
            }
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'order_type' => 'nullable|in:outlet,pickup_delivery',
            'customer_name_walkin' => 'nullable|string|max:150',
            'delivery_address' => 'nullable|string|max:1000',
            'delivery_phone' => 'nullable|string|max:30',
            'pickup_scheduled_at' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
            'promo_id' => 'nullable|exists:promotions,id',
            'points_used' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,transfer,qris,debit,invoice,dp,split',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'draft_id' => 'nullable|exists:draft_orders,id',
            'split_payments' => 'nullable|array',
            'split_payments.*.method' => 'required_with:split_payments|string',
            'split_payments.*.amount' => 'required_with:split_payments|numeric|min:0',
            'split_payments.*.reference' => 'nullable|string',
        ]);

        // For pickup/delivery: delivery address is required
        if ($request->input('order_type') === 'pickup_delivery') {
            $validator->sometimes('delivery_address', 'required|string|max:1000', fn () => true);
            $validator->sometimes('delivery_phone', 'required|string|max:30', fn () => true);
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['order_type'] = $data['order_type'] ?? 'outlet';

        $activeShift = CashierShift::where('branch_id', $branchId)
            ->where('cashier_id', Auth::id())
            ->where('status', 'OPEN')
            ->first();

        try {
            return DB::transaction(function () use ($data, $branchId, $activeShift) {
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
                        'discount' => 0,
                        'subtotal' => $itemSubtotal,
                        'notes' => $item['notes'] ?? null,
                    ];
                }

                // Calculate Promo Discount & Enforce Member Eligibility
                $discountAmount = 0;
                $promo = null;
                if (! empty($data['promo_id'])) {
                    $promo = Promotion::withoutGlobalScopes()->find($data['promo_id']);

                    if ($promo) {
                        $check = $promo->isEligibleForCustomer($customer, $subtotal);
                        if (! $check['eligible']) {
                            throw new \Exception($check['reason']);
                        }

                        if ($promo->type === 'percent') {
                            $discountAmount = $subtotal * ($promo->value / 100);
                        } elseif ($promo->type === 'nominal') {
                            $discountAmount = $promo->value;
                        }

                        if ($discountAmount > $subtotal) {
                            $discountAmount = $subtotal;
                        }
                    }
                }

                // Calculate Loyalty Points Discount
                $pointsUsed = 0;
                $pointsDiscount = 0;
                if (! empty($data['points_used']) && $customer) {
                    $exchangeRate = (float) \App\Models\SystemSetting::get('point_exchange_rate', 1);
                    $requestedPoints = (int) $data['points_used'];

                    // Cannot redeem more than customer balance
                    $pointsUsed = min($requestedPoints, (int) $customer->loyalty_points);
                    $pointsDiscount = $pointsUsed * $exchangeRate;

                    if ($pointsDiscount > ($subtotal - $discountAmount)) {
                        $pointsDiscount = max(0, $subtotal - $discountAmount);
                        $pointsUsed = ceil($pointsDiscount / max(0.01, $exchangeRate));
                    }
                }

                // Total Calculation
                $total = max(0, $subtotal - $discountAmount - $pointsDiscount);
                $taxAmount = 0;

                // Enforce Strict Nominal Validation Rules across all payment methods & types
                $paidAmount = (float) ($data['paid_amount'] ?? 0);
                if ($data['payment_method'] === 'invoice') {
                    $paymentStatus = 'unpaid';
                    $paidAmount = 0;
                    $paidAt = null;
                } elseif ($data['payment_method'] === 'dp') {
                    if ($paidAmount <= 0) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'paid_amount' => ['Nominal DP harus lebih dari Rp 0.'],
                        ]);
                    }
                    if ($paidAmount >= $total) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'paid_amount' => ['Nominal DP harus lebih kecil dari total tagihan.'],
                        ]);
                    }
                    $paymentStatus = 'partial';
                    $paidAt = now();
                } elseif ($data['payment_method'] === 'split') {
                    $splitTotal = 0;
                    if (! empty($data['split_payments'])) {
                        foreach ($data['split_payments'] as $sp) {
                            $splitTotal += (float) ($sp['amount'] ?? 0);
                        }
                    }
                    if (abs($total - $splitTotal) > 0.99) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'paid_amount' => ['Total rincian split payment (Rp '.number_format($splitTotal, 0, ',', '.').') tidak sama dengan total tagihan (Rp '.number_format($total, 0, ',', '.').').'],
                        ]);
                    }
                    $paidAmount = $splitTotal;
                    $paymentStatus = 'paid';
                    $paidAt = now();
                } elseif ($data['payment_method'] === 'cash') {
                    if ($paidAmount < $total) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'paid_amount' => ['Uang tunai (Rp '.number_format($paidAmount, 0, ',', '.').') kurang dari total tagihan (Rp '.number_format($total, 0, ',', '.').').'],
                        ]);
                    }
                    $paymentStatus = 'paid';
                    $paidAt = now();
                } else {
                    // Non-cash full payments (QRIS, Transfer, Debit)
                    if ($paidAmount < $total) {
                        $paidAmount = $total;
                    }
                    $paymentStatus = 'paid';
                    $paidAt = now();
                }

                $changeAmount = max(0, $paidAmount - $total);
                $orderNumber = $this->generateOrderNumber($branchId);

                // Create Order Record
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'branch_id' => $branchId,
                    'customer_id' => $customer?->id,
                    'customer_name_walkin' => $customer ? null : ($data['customer_name_walkin'] ?? 'Pelanggan Walk-In'),
                    'order_type' => $data['order_type'] ?? 'outlet',
                    'delivery_address' => $data['delivery_address'] ?? null,
                    'delivery_phone' => $data['delivery_phone'] ?? null,
                    'pickup_scheduled_at' => $data['pickup_scheduled_at'] ?? null,
                    'cashier_id' => Auth::id(),
                    'cashier_shift_id' => $activeShift?->id,
                    'promo_id' => $promo?->id,
                    'production_status' => 'TERIMA',
                    'payment_status' => $paymentStatus,
                    'payment_method' => strtoupper($data['payment_method']),
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'points_used' => $pointsUsed,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'paid_amount' => min($paidAmount, $total),
                    'change_amount' => $changeAmount,
                    'notes' => $data['notes'] ?? null,
                    'paid_at' => $paidAt,
                    'estimated_done_at' => now()->addDays(2),
                ]);

                // Record payment details in order_payments
                if ($data['payment_method'] === 'split' && ! empty($data['split_payments'])) {
                    foreach ($data['split_payments'] as $sp) {
                        OrderPayment::create([
                            'order_id' => $order->id,
                            'payment_method' => strtoupper($sp['method']),
                            'amount' => $sp['amount'],
                            'reference_number' => $sp['reference'] ?? null,
                            'cashier_id' => Auth::id(),
                            'paid_at' => now(),
                        ]);
                    }
                } elseif ($paidAmount > 0 && $data['payment_method'] !== 'invoice') {
                    OrderPayment::create([
                        'order_id' => $order->id,
                        'payment_method' => strtoupper($data['payment_method']),
                        'amount' => min($paidAmount, $total),
                        'cashier_id' => Auth::id(),
                        'paid_at' => now(),
                    ]);
                }

                // Create Order Items
                foreach ($itemsToCreate as $itemData) {
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }

                // Deduct loyalty points if used
                if ($pointsUsed > 0 && $customer) {
                    $this->loyaltyService->redeemPoints($customer, $pointsUsed, $order);
                }

                // Award loyalty points & post double-entry journal if paid
                if ($paymentStatus === 'paid') {
                    $this->loyaltyService->awardPoints($order);
                    try {
                        app(JournalService::class)->postOrderJournal($order);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("POS Journal Auto-Post failed for order #{$order->id}: {$e->getMessage()}");
                    }
                }

                // Award Loyalty Points synchronously for paid order if no promo or point redemption used
                $pointsEarned = 0;
                if ($customer && ! $promo && empty($data['points_used']) && $discountAmount == 0 && $paymentStatus === 'paid') {
                    $loyaltyLog = app(\App\Services\CRM\LoyaltyService::class)->awardPoints($order);
                    if ($loyaltyLog) {
                        $pointsEarned = $loyaltyLog->points;
                    }
                }

                // Update promo usage limit
                if ($promo) {
                    $promo->increment('usage_count');
                }

                // Delete draft order if resuming from draft
                if (! empty($data['draft_id'])) {
                    DraftOrder::where('id', $data['draft_id'])->delete();
                }

                // Log activity to audit_logs
                $this->auditLogService->log('create_order', $order);

                $lastOrderData = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => (float) $order->total,
                    'paid_amount' => (float) $order->paid_amount,
                    'change_amount' => (float) $order->change_amount,
                    'payment_method' => strtoupper($order->payment_method),
                    'customer_name' => $customer ? $customer->name : ($order->customer_name_walkin ?? 'Pelanggan Walk-In'),
                    'customer_phone' => $customer ? ($customer->phone ?? '') : ($order->delivery_phone ?? ''),
                    'points_earned' => $pointsEarned,
                ];

                return redirect()->route('pos.index')
                    ->with('success', "Order #{$orderNumber} berhasil dibuat!")
                    ->with('last_order_id', $order->id)
                    ->with('last_order_number', $order->order_number)
                    ->with('last_order', $lastOrderData);
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['promo_id' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Export Shift Summary PDF Report.
     */
    public function exportShiftSummaryPdf(CashierShift $shift)
    {
        $shift->load(['branch', 'cashier', 'pettyCashRecords', 'orders']);

        $cashSales = Order::where('cashier_shift_id', $shift->id)
            ->whereIn('payment_method', ['cash', 'CASH', 'split', 'dp'])
            ->sum('paid_amount');

        $nonCashSales = Order::where('cashier_shift_id', $shift->id)
            ->whereIn('payment_method', ['qris', 'QRIS', 'transfer', 'TRANSFER', 'debit', 'DEBIT'])
            ->sum('paid_amount');

        $totalSales = $shift->orders->sum('total');

        $pdf = Pdf::loadView('exports.shift_summary_pdf', compact('shift', 'cashSales', 'nonCashSales', 'totalSales'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("Shift_Summary_#{$shift->id}_".now()->format('Ymd').".pdf");
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

            return sprintf('%s-%s-%04d', $branch->code, $yearMonth, $seq);
        });
    }
}
