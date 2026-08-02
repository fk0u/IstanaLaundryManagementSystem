<?php

namespace Database\Seeders;

use App\Http\Controllers\AssetController;
use App\Models\AccountingPeriod;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FixedAsset;
use App\Models\GoodsReceivedNote;
use App\Models\GRNItem;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\OperationalExpense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Promotion;
use App\Models\Refund;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Finance\JournalService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ERPDataSeeder extends Seeder
{
    /**
     * Run the comprehensive multi-branch database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();
        $services = Service::all();
        $suppliers = Supplier::all();
        $journalService = app(JournalService::class);

        $coaMesinCuci = ChartOfAccount::where('code', '1-2101')->first();
        $coaMesinPengering = ChartOfAccount::where('code', '1-2102')->first();
        $coaPeralatanSetrika = ChartOfAccount::where('code', '1-2103')->first();
        $coaKomputer = ChartOfAccount::where('code', '1-2106')->first();
        $coaKendaraan = ChartOfAccount::where('code', '1-2104')->first();

        // 1. Seed System Settings
        $settings = [
            ['key' => 'company_name', 'value' => 'Istana Laundry Management System'],
            ['key' => 'company_phone', 'value' => '0811-5555-9999'],
            ['key' => 'company_address', 'value' => 'Jl. Wijaya Kusuma Blok V-C, Samarinda'],
            ['key' => 'tax_percentage', 'value' => '11'],
            ['key' => 'loyalty_point_rate', 'value' => '1000'], // 1 point per 1000 IDR
            ['key' => 'print_header', 'value' => 'ISTANA LAUNDRY - Samarinda'],
            ['key' => 'print_footer', 'value' => 'Terima kasih telah mempercayakan pakaian Anda kepada kami!'],
        ];
        foreach ($settings as $st) {
            SystemSetting::firstOrCreate(['key' => $st['key']], ['value' => $st['value']]);
        }

        // 2. Seed Accounting Periods for ALL 4 Branches (Past 3 Months)
        foreach ($branches as $branch) {
            for ($m = 0; $m < 3; $m++) {
                $periodDate = now()->subMonths($m);
                AccountingPeriod::firstOrCreate(
                    ['branch_id' => $branch->id, 'month' => $periodDate->month, 'year' => $periodDate->year],
                    ['status' => 'open']
                );
            }
        }

        // 3. Seed Inventory Items & GRNs (BHP Restock) for EACH branch
        $inventoryTemplates = [
            ['name' => 'Detergen Cair Liquid Premium', 'sku_prefix' => 'DET', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 10, 'current_stock' => 50, 'unit_cost' => 15000],
            ['name' => 'Softener Aroma Sakura Lavender', 'sku_prefix' => 'SOF', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 10, 'current_stock' => 45, 'unit_cost' => 18000],
            ['name' => 'Plastik Packing 35x50', 'sku_prefix' => 'PLS-35', 'category' => 'Kemasan', 'unit' => 'Pack', 'min_stock' => 5, 'current_stock' => 20, 'unit_cost' => 25000],
            ['name' => 'Plastik Packing 40x60', 'sku_prefix' => 'PLS-40', 'category' => 'Kemasan', 'unit' => 'Pack', 'min_stock' => 5, 'current_stock' => 15, 'unit_cost' => 30000],
            ['name' => 'Hanger Pakaian Plastik', 'sku_prefix' => 'HNG', 'category' => 'Perlengkapan', 'unit' => 'Lusin', 'min_stock' => 8, 'current_stock' => 6, 'unit_cost' => 12000], // Low stock alert
            ['name' => 'Parfum Laundry Lemon Fresh', 'sku_prefix' => 'PRF', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 5, 'current_stock' => 12, 'unit_cost' => 35000],
            ['name' => 'Pembersih Noda Membandel (Bleach)', 'sku_prefix' => 'BLC', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 5, 'current_stock' => 8, 'unit_cost' => 22000],
            ['name' => 'Tag Pin / Label Laundry', 'sku_prefix' => 'TAG', 'category' => 'Perlengkapan', 'unit' => 'Box', 'min_stock' => 3, 'current_stock' => 10, 'unit_cost' => 40000],
        ];

        foreach ($branches as $branch) {
            $createdItems = [];
            foreach ($inventoryTemplates as $template) {
                $item = InventoryItem::firstOrCreate(
                    ['branch_id' => $branch->id, 'sku' => $template['sku_prefix'].'-'.$branch->code],
                    [
                        'name' => $template['name'],
                        'category' => $template['category'],
                        'unit' => $template['unit'],
                        'min_stock' => $template['min_stock'],
                        'current_stock' => $template['current_stock'],
                    ]
                );
                $createdItems[] = ['item' => $item, 'unit_cost' => $template['unit_cost']];
            }

            // Create 2 GRN receipts from suppliers for each branch
            $adminUser = User::where('branch_id', $branch->id)->first() ?? User::first();
            $supplier = $suppliers->random() ?? Supplier::first();

            for ($g = 1; $g <= 2; $g++) {
                $grnDate = now()->subDays(15 * $g);
                $poNumber = "PO-{$branch->code}-{$grnDate->format('Ymd')}-00{$g}";
                $grnNumber = "GRN-{$branch->code}-{$grnDate->format('Ymd')}-00{$g}";

                $itemsForGrn = array_slice($createdItems, ($g - 1) * 2, 2);
                $totalPoCost = 0;
                foreach ($itemsForGrn as $ci) {
                    $totalPoCost += 20 * $ci['unit_cost'];
                }

                $po = \App\Models\PurchaseOrder::firstOrCreate(
                    ['po_number' => $poNumber],
                    [
                        'branch_id' => $branch->id,
                        'supplier_id' => $supplier->id,
                        'status' => 'completed',
                        'subtotal' => $totalPoCost,
                        'tax_amount' => 0,
                        'total' => $totalPoCost,
                        'order_date' => $grnDate->copy()->subDays(2),
                        'expected_date' => $grnDate,
                    ]
                );

                $poItemsMap = [];
                foreach ($itemsForGrn as $ci) {
                    $poItem = \App\Models\PurchaseOrderItem::firstOrCreate(
                        ['po_id' => $po->id, 'item_id' => $ci['item']->id],
                        [
                            'quantity' => 20,
                            'unit_cost' => $ci['unit_cost'],
                            'subtotal' => 20 * $ci['unit_cost'],
                            'received_qty' => 20,
                        ]
                    );
                    $poItemsMap[$ci['item']->id] = $poItem->id;
                }

                $grn = GoodsReceivedNote::firstOrCreate(
                    ['grn_number' => $grnNumber],
                    [
                        'po_id' => $po->id,
                        'received_by' => $adminUser->id,
                        'status' => 'confirmed',
                        'received_date' => $grnDate,
                        'notes' => "Penerimaan stok bahan habis pakai dari {$supplier->name}",
                    ]
                );

                // Add 2 items to GRN
                foreach ($itemsForGrn as $ci) {
                    $itemModel = $ci['item'];
                    $qty = rand(10, 30);
                    $unitCost = $ci['unit_cost'];
                    $poItemId = $poItemsMap[$itemModel->id];

                    GRNItem::firstOrCreate(
                        ['grn_id' => $grn->id, 'item_id' => $itemModel->id],
                        [
                            'po_item_id' => $poItemId,
                            'quantity' => $qty,
                            'unit_cost' => $unitCost,
                            'batch_number' => 'BATCH-'.$branch->code.'-'.$grnDate->format('Ym').'-'.rand(100, 999),
                        ]
                    );

                    InventoryBatch::firstOrCreate(
                        ['grn_id' => $grn->id, 'item_id' => $itemModel->id],
                        [
                            'batch_number' => 'BATCH-'.$branch->code.'-'.$grnDate->format('Ym').'-'.rand(100, 999),
                            'quantity' => $qty,
                            'remaining_qty' => $qty,
                            'unit_cost' => $unitCost,
                            'received_date' => $grnDate,
                        ]
                    );
                }

                // Post GRN financial journal
                try {
                    $journalService->postGRNJournal($grn);
                } catch (\Exception $e) {
                    // Ignored if already posted or period issue
                }

                // Seed Supplier Payment for GRN
                if ($g === 1 && $supplier) {
                    $totalGrnCost = GRNItem::where('grn_id', $grn->id)->sum(\DB::raw('quantity * unit_cost'));
                    $payment = SupplierPayment::firstOrCreate(
                        ['grn_id' => $grn->id],
                        [
                            'branch_id' => $branch->id,
                            'supplier_id' => $supplier->id,
                            'payment_date' => $grnDate->addDays(3),
                            'amount' => $totalGrnCost,
                            'payment_method' => 'transfer',
                            'reference_number' => 'PAY-SUP-'.$branch->code.'-'.rand(1000, 9999),
                            'notes' => "Pelunasan faktur GRN #{$grn->grn_number}",
                            'status' => 'posted',
                            'created_by' => $adminUser->id,
                        ]
                    );

                    try {
                        $journalService->postSupplierPaymentJournal($payment);
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        // 4. Seed Customers for EACH branch (Multi-Tenancy)
        foreach ($branches as $branch) {
            $customerTemplates = [
                ['name' => "Pelanggan Utama {$branch->code}", 'phone' => '0812'.str_pad($branch->id, 2, '0', STR_PAD_LEFT).'111222', 'email' => "cust1.{$branch->code}@gmail.com", 'tier' => 'Gold', 'points' => 350],
                ['name' => "Pelanggan Reguler {$branch->code}", 'phone' => '0813'.str_pad($branch->id, 2, '0', STR_PAD_LEFT).'333444', 'email' => "cust2.{$branch->code}@gmail.com", 'tier' => 'Bronze', 'points' => 45],
                ['name' => "Pelanggan VIP {$branch->code}", 'phone' => '0821'.str_pad($branch->id, 2, '0', STR_PAD_LEFT).'555666', 'email' => "cust3.{$branch->code}@gmail.com", 'tier' => 'Platinum', 'points' => 1200],
                ['name' => "Pelanggan Express {$branch->code}", 'phone' => '0852'.str_pad($branch->id, 2, '0', STR_PAD_LEFT).'777888', 'email' => "cust4.{$branch->code}@gmail.com", 'tier' => 'Silver', 'points' => 180],
                ['name' => "Hotel Grand {$branch->code} (Corporate)", 'phone' => '0811'.str_pad($branch->id, 2, '0', STR_PAD_LEFT).'999000', 'email' => "corporate.{$branch->code}@hotelgrand.com", 'tier' => 'Platinum', 'points' => 2500],
                ['name' => "Kos Puteri Melati {$branch->code}", 'phone' => '0822'.str_pad($branch->id, 2, '0', STR_PAD_LEFT).'444555', 'email' => "melati.{$branch->code}@gmail.com", 'tier' => 'Gold', 'points' => 540],
            ];

            $cIdx = 1;
            foreach ($customerTemplates as $template) {
                Customer::firstOrCreate(
                    ['phone' => $template['phone']],
                    [
                        'branch_id' => $branch->id,
                        'name' => $template['name'],
                        'email' => $template['email'],
                        'address' => "Area Layanan {$branch->name}, Samarinda",
                        'member_code' => 'CUST-'.$branch->code.'-'.str_pad($cIdx++, 4, '0', STR_PAD_LEFT),
                        'loyalty_tier' => $template['tier'],
                        'loyalty_points' => $template['points'],
                    ]
                );
            }
        }

        // 5. Seed Attendance & Payroll HR Module
        $allEmployees = Employee::withoutGlobalScopes()->get();
        foreach ($allEmployees as $emp) {
            // Seed 30 days of attendance logs
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i);
                if ($date->isSunday()) {
                    continue;
                }
                \App\Models\Attendance::firstOrCreate(
                    ['employee_id' => $emp->id, 'date' => $date->toDateString()],
                    [
                        'status' => $i % 9 === 0 ? 'terlambat' : 'hadir',
                        'check_in' => $i % 9 === 0 ? '08:35:00' : '07:55:00',
                        'check_out' => '17:05:00',
                        'notes' => $i % 9 === 0 ? 'Terlambat 35 menit' : 'Presensi Sesi Kerja Reguler',
                    ]
                );
            }
        }

        // Generate Payroll Records for previous month across ALL branches
        $prevMonth = now()->subMonth();
        foreach ($branches as $branch) {
            $branchEmployees = Employee::withoutGlobalScopes()->where('branch_id', $branch->id)->get();
            $adminUser = User::where('branch_id', $branch->id)->first() ?? User::first();

            if ($branchEmployees->isNotEmpty()) {
                $payroll = Payroll::firstOrCreate(
                    ['branch_id' => $branch->id, 'month' => $prevMonth->month, 'year' => $prevMonth->year],
                    [
                        'status' => 'finalized',
                        'processed_at' => $prevMonth->endOfMonth(),
                        'created_by' => $adminUser->id,
                    ]
                );

                foreach ($branchEmployees as $emp) {
                    $baseSalary = (float) $emp->base_salary;
                    $transport = 300000.00;
                    $bonus = 150000.00;
                    $tardiness = 50000.00;
                    $bpjs = 100000.00;

                    $totalEarnings = $baseSalary + $transport + $bonus;
                    $totalDeductions = $tardiness + $bpjs;
                    $netSalary = $totalEarnings - $totalDeductions;

                    PayrollItem::firstOrCreate(
                        ['payroll_id' => $payroll->id, 'employee_id' => $emp->id],
                        [
                            'base_salary' => $baseSalary,
                            'transport_allowance' => $transport,
                            'bonus_kg' => $bonus,
                            'tardiness_deduction' => $tardiness,
                            'bpjs_deduction' => $bpjs,
                            'attendance_days' => 25,
                            'work_days' => 26,
                            'total_earnings' => $totalEarnings,
                            'total_deductions' => $totalDeductions,
                            'net_salary' => $netSalary,
                        ]
                    );
                }

                // Post Payroll financial journal
                try {
                    $journalService->postPayrollJournal($payroll);
                } catch (\Exception $e) {
                }
            }
        }

        // 6. Seed Fixed Assets & Depreciation Schedules for EACH Branch
        $assetTemplates = [
            [
                'code_suffix' => 'WASH-01',
                'name' => 'Mesin Cuci Industrial LG Front Load 15kg',
                'category' => 'peralatan',
                'account_id' => $coaMesinCuci?->id ?? 1,
                'cost' => 15000000.00,
                'salvage' => 1500000.00,
                'life' => 48,
            ],
            [
                'code_suffix' => 'DRY-01',
                'name' => 'Mesin Pengering SpeedQueen Heavy Duty 15kg',
                'category' => 'peralatan',
                'account_id' => $coaMesinPengering?->id ?? 1,
                'cost' => 20000000.00,
                'salvage' => 2000000.00,
                'life' => 60,
            ],
            [
                'code_suffix' => 'STEAM-01',
                'name' => 'Boiler Steamer Boiler Gas 25 Liter',
                'category' => 'peralatan',
                'account_id' => $coaPeralatanSetrika?->id ?? 1,
                'cost' => 8500000.00,
                'salvage' => 500000.00,
                'life' => 36,
            ],
            [
                'code_suffix' => 'POS-01',
                'name' => 'Komputer Kasir All-in-One & Thermal Printer',
                'category' => 'peralatan',
                'account_id' => $coaKomputer?->id ?? 1,
                'cost' => 7500000.00,
                'salvage' => 500000.00,
                'life' => 36,
            ],
        ];

        foreach ($branches as $branch) {
            foreach ($assetTemplates as $at) {
                $fa = FixedAsset::firstOrCreate(
                    ['asset_code' => 'AST-'.$branch->code.'-'.$at['code_suffix']],
                    [
                        'branch_id' => $branch->id,
                        'account_id' => $at['account_id'],
                        'name' => $at['name'],
                        'category' => $at['category'],
                        'acquisition_date' => now()->subYear()->toDateString(),
                        'acquisition_cost' => $at['cost'],
                        'salvage_value' => $at['salvage'],
                        'useful_life_months' => $at['life'],
                        'depreciation_method' => 'straight_line',
                        'accumulated_depreciation' => 0,
                        'book_value' => $at['cost'],
                        'is_active' => true,
                    ]
                );

                AssetController::generateSchedulesForAsset($fa);

                // Post depreciation for schedules up to current date
                foreach ($fa->depreciationSchedules()->where('is_posted', false)->get() as $sched) {
                    if ($sched->period_date && $sched->period_date->isPast()) {
                        try {
                            $journalService->postDepreciationJournal($sched);
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
        }

        // 7. Seed Operational Expenses (OpEx) for ALL branches across last 30 days
        $opexTemplates = [
            ['code' => '5-2101', 'name' => 'Tagihan Listrik PLN Workshop', 'amount' => 1250000, 'method' => 'transfer'],
            ['code' => '5-2102', 'name' => 'Tagihan Air PDAM', 'amount' => 450000, 'method' => 'cash'],
            ['code' => '5-2103', 'name' => 'Pembelian Gas LPG 12kg (3 Tabung)', 'amount' => 630000, 'method' => 'cash'],
            ['code' => '5-2105', 'name' => 'Langganan Internet Fiber Optic Biznet', 'amount' => 550000, 'method' => 'transfer'],
            ['code' => '5-4102', 'name' => 'Service & Ganti Oli Mesin Cuci', 'amount' => 350000, 'method' => 'cash'],
            ['code' => '5-2104', 'name' => 'Sewa Gudang Tambahan Bulan Ini', 'amount' => 2000000, 'method' => 'transfer'],
        ];

        foreach ($branches as $branch) {
            $adminUser = User::where('branch_id', $branch->id)->first() ?? User::first();
            for ($d = 25; $d >= 2; $d -= 5) {
                $expDate = now()->subDays($d);
                $tpl = $opexTemplates[rand(0, count($opexTemplates) - 1)];
                $coa = ChartOfAccount::where('code', $tpl['code'])->first() ?? ChartOfAccount::where('type', 'expense')->first();

                $expense = OperationalExpense::firstOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'receipt_number' => 'EXP-'.$branch->code.'-'.$expDate->format('Ymd').'-'.rand(100, 999),
                    ],
                    [
                        'expense_date' => $expDate,
                        'account_id' => $coa->id,
                        'amount' => $tpl['amount'],
                        'description' => $tpl['name'].' '.$branch->name,
                        'payment_method' => $tpl['method'],
                        'status' => 'posted',
                        'created_by' => $adminUser->id,
                    ]
                );

                try {
                    $journalService->postOperationalExpenseJournal($expense);
                } catch (\Exception $e) {
                }
            }
        }

        // 8. Seed Active Promotions
        $promotions = [
            ['code' => 'HEMAT10', 'name' => 'Diskon Hemat 10%', 'type' => 'percent', 'value' => 10, 'min' => 20000],
            ['code' => 'DISKON50K', 'name' => 'Potongan Rp 50.000 (Spesial)', 'type' => 'nominal', 'value' => 50000, 'min' => 100000],
            ['code' => 'WELCOME', 'name' => 'Promo Sambutan Rp 10.000', 'type' => 'nominal', 'value' => 10000, 'min' => 25000],
            ['code' => 'MERDEKA20', 'name' => 'Diskon Promo Merdeka 20%', 'type' => 'percent', 'value' => 20, 'min' => 30000],
        ];
        foreach ($promotions as $p) {
            Promotion::firstOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'type' => $p['type'],
                    'value' => $p['value'],
                    'min_transaction' => $p['min'],
                    'start_date' => now()->subMonths(2)->toDateString(),
                    'end_date' => now()->addYear()->toDateString(),
                    'is_active' => true,
                ]
            );
        }

        // 9. Seed POS Orders for Dashboards & Financial Journals across ALL branches
        foreach ($branches as $branch) {
            $branchCustomers = Customer::where('branch_id', $branch->id)->get();
            $cashierUser = User::where('branch_id', $branch->id)->where('email', 'like', 'cashier%')->first()
                ?? User::where('branch_id', $branch->id)->first()
                ?? User::first();

            if ($branchCustomers->isEmpty()) {
                continue;
            }

            // Seed 20 days daily revenue trend (3-6 orders per day per branch)
            for ($i = 20; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $numOrders = rand(3, 6);

                for ($o = 1; $o <= $numOrders; $o++) {
                    $customer = $branchCustomers->random();
                    $service = $services->random();

                    $branchPrice = $service->branchPrices()->where('branch_id', $branch->id)->first();
                    $unitPrice = $branchPrice ? $branchPrice->price : $service->base_price;
                    $qty = rand(2, 8);
                    $subtotal = $unitPrice * $qty;
                    $total = $subtotal;

                    $seqStr = str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT);
                    $orderNumber = "ORD-{$branch->code}-{$date->format('Ymd')}-{$seqStr}";

                    $isToday = $i === 0;
                    $productionStatus = $isToday ? ['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK', 'SIAP'][rand(0, 6)] : 'DIAMBIL';
                    $paymentStatus = ($isToday && rand(0, 1) === 0) ? 'pending' : 'paid';
                    $paymentMethod = ['cash', 'transfer', 'invoice'][rand(0, 2)];

                    $order = Order::firstOrCreate(
                        ['order_number' => $orderNumber],
                        [
                            'branch_id' => $branch->id,
                            'customer_id' => $customer->id,
                            'cashier_id' => $cashierUser->id,
                            'subtotal' => $subtotal,
                            'discount_amount' => 0,
                            'points_used' => 0,
                            'tax_amount' => 0,
                            'total' => $total,
                            'payment_status' => $paymentStatus,
                            'payment_method' => $paymentMethod,
                            'paid_amount' => $paymentStatus === 'paid' ? $total : 0,
                            'change_amount' => 0,
                            'production_status' => $productionStatus,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]
                    );

                    OrderItem::firstOrCreate(
                        ['order_id' => $order->id, 'service_id' => $service->id],
                        [
                            'quantity' => $qty,
                            'unit' => $service->unit,
                            'unit_price' => $unitPrice,
                            'discount' => 0,
                            'subtotal' => $subtotal,
                        ]
                    );

                    // Post order financial journal if paid
                    if ($paymentStatus === 'paid') {
                        try {
                            $journalService->postOrderJournal($order);
                        } catch (\Exception $e) {
                        }
                    }
                }
            }

            // Seed 1 Refund record per branch for audit trail testing
            $sampleOrder = Order::where('branch_id', $branch->id)->where('payment_status', 'paid')->first();
            if ($sampleOrder) {
                Refund::firstOrCreate(
                    ['order_id' => $sampleOrder->id],
                    [
                        'branch_id' => $branch->id,
                        'requested_by' => $cashierUser->id,
                        'amount' => $sampleOrder->total,
                        'reason' => 'Pakaian luntur saat pencucian',
                        'status' => 'completed',
                        'cashier_approved_at' => now()->subHours(5),
                        'branch_approved_at' => now()->subHours(4),
                        'finance_approved_at' => now()->subHours(3),
                        'owner_approved_at' => now()->subHours(2),
                        'processed_at' => now(),
                    ]
                );
            }
        }
    }
}

