<?php

namespace Database\Seeders;

use App\Http\Controllers\AssetController;
use App\Models\AccountingPeriod;
use App\Models\Attendance;
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
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Refund;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Finance\JournalService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ERPDataSeeder extends Seeder
{
    /**
     * Run the hyper-realistic, fully synchronized multi-branch ERP database seeds.
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

        // ---------------------------------------------------------
        // 1. Seed System Settings
        // ---------------------------------------------------------
        $settings = [
            ['key' => 'company_name', 'value' => 'Istana Laundry Management System'],
            ['key' => 'company_phone', 'value' => '0811-5555-9999'],
            ['key' => 'company_address', 'value' => 'Jl. Wijaya Kusuma Blok V-C Gg. Rina, Samarinda'],
            ['key' => 'tax_percentage', 'value' => '11'],
            ['key' => 'loyalty_point_rate', 'value' => '1000'],
            ['key' => 'point_exchange_rate', 'value' => '100'],
            ['key' => 'point_earn_spend_threshold', 'value' => '1000'],
            ['key' => 'point_min_redeem', 'value' => '10'],
            ['key' => 'print_header', 'value' => 'ISTANA LAUNDRY - Samarinda'],
            ['key' => 'print_footer', 'value' => 'Terima kasih telah mempercayakan pakaian Anda kepada kami!'],
        ];
        foreach ($settings as $st) {
            SystemSetting::firstOrCreate(['key' => $st['key']], ['value' => $st['value']]);
        }

        // ---------------------------------------------------------
        // 2. Seed Accounting Periods for ALL 4 Branches (Past 6 Months)
        // ---------------------------------------------------------
        foreach ($branches as $branch) {
            for ($m = 0; $m < 6; $m++) {
                $periodDate = now()->subMonths($m);
                AccountingPeriod::firstOrCreate(
                    ['branch_id' => $branch->id, 'month' => $periodDate->month, 'year' => $periodDate->year],
                    ['status' => 'open']
                );
            }
        }

        // ---------------------------------------------------------
        // 3. Seed Real Indonesian Customers across ALL Branches
        // ---------------------------------------------------------
        $indonesianCustomers = [
            // Branch WJK Customers
            ['name' => 'Dr. Irwan Syahputra, M.Kes', 'phone' => '081255511001', 'email' => 'irwan.syahputra@gmail.com', 'tier' => 'Platinum', 'points' => 1450, 'address' => 'Jl. Juanda No. 45, Samarinda Ulu', 'branch_code' => 'WJK'],
            ['name' => 'Hj. Kartini Hartono', 'phone' => '081355511002', 'email' => 'kartini.hartono@yahoo.com', 'tier' => 'Gold', 'points' => 680, 'address' => 'Jl. Wijaya Kusuma No. 12, Air Hitam', 'branch_code' => 'WJK'],
            ['name' => 'Bpk. Muhammad Al-Ghazali', 'phone' => '082155511003', 'email' => 'alghazali.m@gmail.com', 'tier' => 'Gold', 'points' => 520, 'address' => 'Perum Cendana Blok C-8, Samarinda', 'branch_code' => 'WJK'],
            ['name' => 'Anisa Rahmawati', 'phone' => '085255511004', 'email' => 'anisa.rahma@gmail.com', 'tier' => 'Silver', 'points' => 210, 'address' => 'Jl. Antasari Gg. 3 No. 8, Samarinda', 'branch_code' => 'WJK'],
            ['name' => 'Dian Sastrowardoyo', 'phone' => '081155511005', 'email' => 'dian.sastro@outlook.com', 'tier' => 'Platinum', 'points' => 1980, 'address' => 'Jl. Siradj Salman No. 88, Samarinda', 'branch_code' => 'WJK'],
            ['name' => 'Hotel Grand Victoria (Corporate)', 'phone' => '081155511006', 'email' => 'laundry@grandvictoriahotel.co.id', 'tier' => 'Platinum', 'points' => 3400, 'address' => 'Jl. Letjen S. Parman No. 11, Samarinda', 'branch_code' => 'WJK'],
            ['name' => 'Kos Putri Annisa', 'phone' => '082255511007', 'email' => 'kos.annisa@gmail.com', 'tier' => 'Gold', 'points' => 790, 'address' => 'Jl. Kadrie Oening Gg. 2, Samarinda', 'branch_code' => 'WJK'],

            // Branch SUT Customers
            ['name' => 'Taufik Hidayat', 'phone' => '081255522001', 'email' => 'taufik.hidayat@gmail.com', 'tier' => 'Gold', 'points' => 850, 'address' => 'Jl. Dr. Sutomo Gg. 4 No. 15, Sidodadi', 'branch_code' => 'SUT'],
            ['name' => 'Bayu Skak', 'phone' => '081355522002', 'email' => 'bayuskak.vlog@gmail.com', 'tier' => 'Silver', 'points' => 310, 'address' => 'Jl. Pahlawan No. 27, Samarinda', 'branch_code' => 'SUT'],
            ['name' => 'Siska Kohl', 'phone' => '082155522003', 'email' => 'siska.kohl@tiktok.com', 'tier' => 'Platinum', 'points' => 2200, 'address' => 'Perum Villa Tamara Blok B-1, Samarinda', 'branch_code' => 'SUT'],
            ['name' => 'CV Borneo Utama Logistics', 'phone' => '085255522004', 'email' => 'admin@borneoutamalogistics.com', 'tier' => 'Platinum', 'points' => 2800, 'address' => 'Jl. Perniagaan No. 6, Samarinda', 'branch_code' => 'SUT'],
            ['name' => 'Lestari Utami', 'phone' => '082255522005', 'email' => 'lestari.utami@gmail.com', 'tier' => 'Bronze', 'points' => 95, 'address' => 'Jl. Abul Hasan No. 34, Samarinda', 'branch_code' => 'SUT'],

            // Branch HID Customers
            ['name' => 'Putri Marino', 'phone' => '081255533001', 'email' => 'putri.marino@gmail.com', 'tier' => 'Gold', 'points' => 640, 'address' => 'Jl. Pangeran Hidayatullah No. 50, Karang Mumus', 'branch_code' => 'HID'],
            ['name' => 'Bagas Maulana', 'phone' => '081355533002', 'email' => 'bagas.m@gmail.com', 'tier' => 'Silver', 'points' => 280, 'address' => 'Jl. Muso Salim No. 19, Samarinda', 'branch_code' => 'HID'],
            ['name' => 'Chintya Bella', 'phone' => '082155533003', 'email' => 'chintya.bella@gmail.com', 'tier' => 'Platinum', 'points' => 1650, 'address' => 'Jl. Agus Salim No. 8, Samarinda', 'branch_code' => 'HID'],
            ['name' => 'Doni Salmanan', 'phone' => '085255533004', 'email' => 'doni.salmanan@gmail.com', 'tier' => 'Bronze', 'points' => 120, 'address' => 'Jl. Gatot Subroto No. 44, Samarinda', 'branch_code' => 'HID'],
            ['name' => 'Fitri Carlina', 'phone' => '082255533005', 'email' => 'fitri.carlina@gmail.com', 'tier' => 'Gold', 'points' => 590, 'address' => 'Jl. Gajah Mada No. 12, Samarinda', 'branch_code' => 'HID'],

            // Branch LMG Customers
            ['name' => 'Gilang Dirga', 'phone' => '081255544001', 'email' => 'gilang.dirga@gmail.com', 'tier' => 'Gold', 'points' => 720, 'address' => 'Jl. Lambung Mangkurat Gg. 1 No. 3, Sungai Pinang', 'branch_code' => 'LMG'],
            ['name' => 'Hesty Purwadinata', 'phone' => '081355544002', 'email' => 'hesty.purwa@gmail.com', 'tier' => 'Silver', 'points' => 340, 'address' => 'Jl. Sentosa No. 88, Samarinda', 'branch_code' => 'LMG'],
            ['name' => 'Jusuf Hamka', 'phone' => '082155544003', 'email' => 'jusuf.hamka@citramarga.com', 'tier' => 'Platinum', 'points' => 3100, 'address' => 'Jl. Pelabuhan No. 1, Samarinda', 'branch_code' => 'LMG'],
            ['name' => 'Kevin Sanjaya', 'phone' => '085255544004', 'email' => 'kevin.sanjaya@gmail.com', 'tier' => 'Gold', 'points' => 890, 'address' => 'Jl. Remaja No. 23, Samarinda', 'branch_code' => 'LMG'],
            ['name' => 'Maudy Ayunda', 'phone' => '082255544005', 'email' => 'maudy.ayunda@stanford.edu', 'tier' => 'Platinum', 'points' => 2400, 'address' => 'Jl. Merdeka No. 7, Samarinda', 'branch_code' => 'LMG'],
        ];

        foreach ($indonesianCustomers as $idx => $cData) {
            $branch = Branch::where('code', $cData['branch_code'])->first() ?? $branches->first();
            Customer::firstOrCreate(
                ['phone' => $cData['phone']],
                [
                    'branch_id' => $branch->id,
                    'name' => $cData['name'],
                    'email' => $cData['email'],
                    'address' => $cData['address'],
                    'member_code' => 'CUST-'.$branch->code.'-'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                    'loyalty_tier' => $cData['tier'],
                    'loyalty_points' => $cData['points'],
                ]
            );
        }

        // ---------------------------------------------------------
        // 4. Seed Inventory Items, Suppliers & GRNs for EACH branch
        // ---------------------------------------------------------
        $inventoryTemplates = [
            ['name' => 'Detergen Cair Ultra Clean', 'sku_prefix' => 'DET', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 15, 'current_stock' => 60, 'unit_cost' => 16000],
            ['name' => 'Softener Premium Lavender', 'sku_prefix' => 'SOF', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 15, 'current_stock' => 55, 'unit_cost' => 19000],
            ['name' => 'Plastik HD Packing 35x50', 'sku_prefix' => 'PLS-35', 'category' => 'Kemasan', 'unit' => 'Pack', 'min_stock' => 10, 'current_stock' => 30, 'unit_cost' => 26000],
            ['name' => 'Plastik HD Packing 40x60', 'sku_prefix' => 'PLS-40', 'category' => 'Kemasan', 'unit' => 'Pack', 'min_stock' => 10, 'current_stock' => 25, 'unit_cost' => 32000],
            ['name' => 'Hanger Pakaian Plastik Anti Patah', 'sku_prefix' => 'HNG', 'category' => 'Perlengkapan', 'unit' => 'Lusin', 'min_stock' => 10, 'current_stock' => 8, 'unit_cost' => 14000],
            ['name' => 'Parfum Laundry Master Ocean', 'sku_prefix' => 'PRF', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 8, 'current_stock' => 18, 'unit_cost' => 38000],
            ['name' => 'Pembersih Noda Minyak & Kerah', 'sku_prefix' => 'BLC', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 5, 'current_stock' => 12, 'unit_cost' => 24000],
            ['name' => 'Tag Label Barcode Laundry', 'sku_prefix' => 'TAG', 'category' => 'Perlengkapan', 'unit' => 'Box', 'min_stock' => 5, 'current_stock' => 15, 'unit_cost' => 45000],
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

            $adminUser = User::where('branch_id', $branch->id)->first() ?? User::first();
            $supplier = $suppliers->random() ?? Supplier::first();

            // Seed 2 GRNs per branch
            for ($g = 1; $g <= 2; $g++) {
                $grnDate = now()->subDays(20 * $g);
                $poNumber = "PO-{$branch->code}-{$grnDate->format('Ymd')}-00{$g}";
                $grnNumber = "GRN-{$branch->code}-{$grnDate->format('Ymd')}-00{$g}";

                $itemsForGrn = array_slice($createdItems, ($g - 1) * 3, 3);
                $totalPoCost = 0;
                foreach ($itemsForGrn as $ci) {
                    $totalPoCost += 25 * $ci['unit_cost'];
                }

                $po = PurchaseOrder::firstOrCreate(
                    ['po_number' => $poNumber],
                    [
                        'branch_id' => $branch->id,
                        'supplier_id' => $supplier->id,
                        'status' => 'completed',
                        'subtotal' => $totalPoCost,
                        'tax_amount' => 0,
                        'total' => $totalPoCost,
                        'order_date' => $grnDate->copy()->subDays(3),
                        'expected_date' => $grnDate,
                    ]
                );

                $poItemsMap = [];
                foreach ($itemsForGrn as $ci) {
                    $poItem = PurchaseOrderItem::firstOrCreate(
                        ['po_id' => $po->id, 'item_id' => $ci['item']->id],
                        [
                            'quantity' => 25,
                            'unit_cost' => $ci['unit_cost'],
                            'subtotal' => 25 * $ci['unit_cost'],
                            'received_qty' => 25,
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
                        'notes' => "Penerimaan stok bahan kimia & kemasan dari {$supplier->name}",
                    ]
                );

                foreach ($itemsForGrn as $ci) {
                    $itemModel = $ci['item'];
                    $qty = 25;
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

                try {
                    $journalService->postGRNJournal($grn);
                } catch (\Exception $e) {
                }

                // Seed Supplier Payment (AP Settlement)
                if ($supplier) {
                    $totalGrnCost = GRNItem::where('grn_id', $grn->id)->sum(DB::raw('quantity * unit_cost'));
                    $payment = SupplierPayment::firstOrCreate(
                        ['grn_id' => $grn->id],
                        [
                            'branch_id' => $branch->id,
                            'supplier_id' => $supplier->id,
                            'payment_date' => $grnDate->copy()->addDays(2),
                            'amount' => $totalGrnCost,
                            'payment_method' => 'transfer',
                            'reference_number' => 'TRF-PAY-'.$branch->code.'-'.rand(10000, 99999),
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

        // ---------------------------------------------------------
        // 5. Seed Attendance & Payroll HR Module
        // ---------------------------------------------------------
        $allEmployees = Employee::withoutGlobalScopes()->get();
        foreach ($allEmployees as $emp) {
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i);
                if ($date->isSunday()) {
                    continue;
                }
                Attendance::firstOrCreate(
                    ['employee_id' => $emp->id, 'date' => $date->toDateString()],
                    [
                        'status' => $i % 8 === 0 ? 'terlambat' : 'hadir',
                        'check_in' => $i % 8 === 0 ? '08:30:00' : '07:55:00',
                        'check_out' => '17:00:00',
                        'notes' => $i % 8 === 0 ? 'Terlambat 30 menit (Macet Juanda)' : 'Presensi Kerja Tepat Waktu',
                    ]
                );
            }
        }

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
                    $transport = 350000.00;
                    $bonus = 200000.00;
                    $tardiness = 50000.00;
                    $bpjs = 120000.00;

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

                try {
                    $journalService->postPayrollJournal($payroll);
                } catch (\Exception $e) {
                }
            }
        }

        // ---------------------------------------------------------
        // 6. Seed Fixed Assets & Monthly Depreciation Schedules
        // ---------------------------------------------------------
        $assetTemplates = [
            [
                'code_suffix' => 'WASH-01',
                'name' => 'Mesin Cuci Industrial Maytag Front Load 15kg',
                'category' => 'peralatan',
                'account_id' => $coaMesinCuci?->id ?? 1,
                'cost' => 16500000.00,
                'salvage' => 1500000.00,
                'life' => 48,
            ],
            [
                'code_suffix' => 'DRY-01',
                'name' => 'Mesin Pengering SpeedQueen Heavy Duty 15kg',
                'category' => 'peralatan',
                'account_id' => $coaMesinPengering?->id ?? 1,
                'cost' => 22000000.00,
                'salvage' => 2000000.00,
                'life' => 60,
            ],
            [
                'code_suffix' => 'STEAM-01',
                'name' => 'Boiler Steamer Gas Stainless Steel 30 Liter',
                'category' => 'peralatan',
                'account_id' => $coaPeralatanSetrika?->id ?? 1,
                'cost' => 9500000.00,
                'salvage' => 500000.00,
                'life' => 36,
            ],
            [
                'code_suffix' => 'POS-01',
                'name' => 'Komputer POS Touchscreen & Thermal Printer',
                'category' => 'peralatan',
                'account_id' => $coaKomputer?->id ?? 1,
                'cost' => 8200000.00,
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

        // ---------------------------------------------------------
        // 7. Seed Operational Expenses (OpEx) for ALL Branches
        // ---------------------------------------------------------
        $opexTemplates = [
            ['code' => '5-2101', 'name' => 'Tagihan Listrik PLN Workshop', 'amount' => 1450000, 'method' => 'transfer'],
            ['code' => '5-2102', 'name' => 'Tagihan Air PDAM', 'amount' => 520000, 'method' => 'cash'],
            ['code' => '5-2103', 'name' => 'Pembelian Tabung Gas LPG 12kg (4 Tabung)', 'amount' => 840000, 'method' => 'cash'],
            ['code' => '5-2105', 'name' => 'Langganan Internet Biznet Dedicated', 'amount' => 650000, 'method' => 'transfer'],
            ['code' => '5-4102', 'name' => 'Maintenance Routine & Service Mesin Cuci', 'amount' => 400000, 'method' => 'cash'],
            ['code' => '5-2104', 'name' => 'Retribusi Kebersihan & Keamanan Lingkungan', 'amount' => 250000, 'method' => 'cash'],
        ];

        foreach ($branches as $branch) {
            $adminUser = User::where('branch_id', $branch->id)->first() ?? User::first();
            for ($d = 28; $d >= 2; $d -= 4) {
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

        // ---------------------------------------------------------
        // 8. Seed Active Promotions
        // ---------------------------------------------------------
        $promotions = [
            ['code' => 'HEMAT10', 'name' => 'Diskon Hemat 10%', 'type' => 'percent', 'value' => 10, 'min' => 20000],
            ['code' => 'DISKON50K', 'name' => 'Potongan Rp 50.000 (Super Hemat)', 'type' => 'nominal', 'value' => 50000, 'min' => 100000],
            ['code' => 'WELCOME', 'name' => 'Promo Sambutan Pelanggan Baru Rp 10.000', 'type' => 'nominal', 'value' => 10000, 'min' => 25000],
            ['code' => 'MERDEKA20', 'name' => 'Diskon Merdeka 20%', 'type' => 'percent', 'value' => 20, 'min' => 30000],
        ];
        foreach ($promotions as $p) {
            Promotion::firstOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'type' => $p['type'],
                    'value' => $p['value'],
                    'min_transaction' => $p['min'],
                    'start_date' => now()->subMonths(3)->toDateString(),
                    'end_date' => now()->addYear()->toDateString(),
                    'is_active' => true,
                ]
            );
        }

        // ---------------------------------------------------------
        // 9. Seed POS Orders for Dashboards & Financial Journals
        // ---------------------------------------------------------
        foreach ($branches as $branch) {
            $branchCustomers = Customer::where('branch_id', $branch->id)->get();
            $cashierUser = User::where('branch_id', $branch->id)->where('email', 'like', 'cashier%')->first()
                ?? User::where('branch_id', $branch->id)->first()
                ?? User::first();

            if ($branchCustomers->isEmpty()) {
                continue;
            }

            // Seed 25 days daily revenue trend (4-7 orders per day per branch)
            for ($i = 25; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $numOrders = rand(4, 7);

                for ($o = 1; $o <= $numOrders; $o++) {
                    $customer = $branchCustomers->random();
                    $service = $services->random();

                    $branchPrice = $service->branchPrices()->where('branch_id', $branch->id)->first();
                    $unitPrice = $branchPrice ? $branchPrice->price : $service->base_price;
                    $qty = rand(3, 10);
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

                    if ($paymentStatus === 'paid') {
                        try {
                            $journalService->postOrderJournal($order);
                        } catch (\Exception $e) {
                        }
                    }
                }
            }

            // Seed 1 Refund record per branch
            $sampleOrder = Order::where('branch_id', $branch->id)->where('payment_status', 'paid')->first();
            if ($sampleOrder) {
                Refund::firstOrCreate(
                    ['order_id' => $sampleOrder->id],
                    [
                        'branch_id' => $branch->id,
                        'requested_by' => $cashierUser->id,
                        'amount' => $sampleOrder->total,
                        'reason' => 'Pakaian tertukar saat proses lipat',
                        'status' => 'completed',
                        'cashier_approved_at' => now()->subHours(6),
                        'branch_approved_at' => now()->subHours(5),
                        'finance_approved_at' => now()->subHours(3),
                        'owner_approved_at' => now()->subHours(1),
                        'processed_at' => now(),
                    ]
                );
            }
        }
    }
}
