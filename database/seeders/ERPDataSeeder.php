<?php

namespace Database\Seeders;

use App\Models\AccountingPeriod;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FixedAsset;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ERPDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();
        $services = Service::all();
        $coaMesinCuci = ChartOfAccount::where('code', '1-2101')->first();
        $coaMesinPengering = ChartOfAccount::where('code', '1-2102')->first();
        $coaPeralatanSetrika = ChartOfAccount::where('code', '1-2103')->first();

        // 1. Seed Accounting Periods (Open)
        foreach ($branches as $branch) {
            AccountingPeriod::firstOrCreate(
                ['branch_id' => $branch->id, 'month' => now()->month, 'year' => now()->year],
                ['status' => 'open']
            );
            AccountingPeriod::firstOrCreate(
                ['branch_id' => $branch->id, 'month' => now()->subMonth()->month, 'year' => now()->subMonth()->year],
                ['status' => 'open']
            );
        }

        // 2. Seed Inventory Items (BHP) for each branch
        $inventoryTemplates = [
            ['name' => 'Detergen Cair Liquid Premium', 'sku_prefix' => 'DET', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 10, 'current_stock' => 50],
            ['name' => 'Softener Aroma Sakura Lavender', 'sku_prefix' => 'SOF', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 10, 'current_stock' => 45],
            ['name' => 'Plastik Packing 35x50', 'sku_prefix' => 'PLS-35', 'category' => 'Kemasan', 'unit' => 'Pack', 'min_stock' => 5, 'current_stock' => 20],
            ['name' => 'Plastik Packing 40x60', 'sku_prefix' => 'PLS-40', 'category' => 'Kemasan', 'unit' => 'Pack', 'min_stock' => 5, 'current_stock' => 15],
            ['name' => 'Hanger Pakaian Plastik', 'sku_prefix' => 'HNG', 'category' => 'Perlengkapan', 'unit' => 'Lusin', 'min_stock' => 8, 'current_stock' => 6], // Under min stock!
            ['name' => 'Parfum Laundry Lemon Fresh', 'sku_prefix' => 'PRF', 'category' => 'Bahan Kimia', 'unit' => 'Liter', 'min_stock' => 5, 'current_stock' => 12],
        ];

        foreach ($branches as $branch) {
            foreach ($inventoryTemplates as $template) {
                InventoryItem::firstOrCreate(
                    ['branch_id' => $branch->id, 'sku' => $template['sku_prefix'].'-'.$branch->code],
                    [
                        'name' => $template['name'],
                        'category' => $template['category'],
                        'unit' => $template['unit'],
                        'min_stock' => $template['min_stock'],
                        'current_stock' => $template['current_stock'],
                    ]
                );
            }
        }

        // 3. Seed Customers for EACH branch to ensure multi-tenancy coverage
        $custIdx = 1;
        foreach ($branches as $branch) {
            $customerTemplates = [
                ['name' => "Pelanggan Utama {$branch->code}", 'phone' => '0812'.str_pad($branch->id, 2, '0', STR_PAD_LEFT).'111222', 'email' => "cust1.{$branch->code}@gmail.com", 'address' => "Jl. Utama No. 12, Area {$branch->name}"],
                ['name' => "Pelanggan Reguler {$branch->code}", 'phone' => '0813'.str_pad($branch->id, 2, '0', STR_PAD_LEFT).'333444', 'email' => "cust2.{$branch->code}@gmail.com", 'address' => "Jl. Pasundan Gg. 3B, Area {$branch->name}"],
                ['name' => "Pelanggan VIP {$branch->code}", 'phone' => '0821'.str_pad($branch->id, 2, '0', STR_PAD_LEFT).'555666', 'email' => "cust3.{$branch->code}@gmail.com", 'address' => "Jl. Pemuda No. 45, Area {$branch->name}"],
                ['name' => "Pelanggan Express {$branch->code}", 'phone' => '0852'.str_pad($branch->id, 2, '0', STR_PAD_LEFT).'777888', 'email' => "cust4.{$branch->code}@gmail.com", 'address' => "Jl. Ahmad Yani No. 88, Area {$branch->name}"],
            ];

            foreach ($customerTemplates as $template) {
                Customer::firstOrCreate(
                    ['phone' => $template['phone']],
                    [
                        'branch_id' => $branch->id,
                        'name' => $template['name'],
                        'email' => $template['email'],
                        'address' => $template['address'],
                        'member_code' => 'CUST-'.$branch->code.'-'.str_pad($custIdx++, 4, '0', STR_PAD_LEFT),
                        'loyalty_tier' => 'Bronze',
                        'loyalty_points' => rand(10, 150),
                    ]
                );
            }
        }

        // 4. Seed Employees for HR Module
        foreach ($branches as $branch) {
            $staffUser = User::where('branch_id', $branch->id)->where('email', 'like', 'staff%')->first();
            $cashierUser = User::where('branch_id', $branch->id)->where('email', 'like', 'cashier%')->first();

            if ($staffUser) {
                Employee::firstOrCreate(
                    ['nik' => 'NIK-'.$branch->code.'-001'],
                    [
                        'branch_id' => $branch->id,
                        'user_id' => $staffUser->id,
                        'name' => $staffUser->name,
                        'position' => 'Operator Workshop',
                        'phone' => '08125555'.rand(1000, 9999),
                        'birth_place' => 'Samarinda',
                        'birth_date' => '1998-05-15',
                        'address' => 'Jl. Pahlawan No. '.rand(1, 50).', Samarinda',
                        'bank_name' => 'Bank BCA',
                        'bank_account_number' => '8830'.rand(100000, 999999),
                        'bank_account_holder' => $staffUser->name,
                        'base_salary' => 2800000.00,
                        'is_active' => true,
                        'joined_at' => now()->subMonths(10)->toDateString(),
                    ]
                );
            }

            if ($cashierUser) {
                Employee::firstOrCreate(
                    ['nik' => 'NIK-'.$branch->code.'-002'],
                    [
                        'branch_id' => $branch->id,
                        'user_id' => $cashierUser->id,
                        'name' => $cashierUser->name,
                        'position' => 'Kasir Utama',
                        'phone' => '08134444'.rand(1000, 9999),
                        'birth_place' => 'Balikpapan',
                        'birth_date' => '2000-08-20',
                        'address' => 'Jl. Mulawarman No. '.rand(1, 50).', Samarinda',
                        'bank_name' => 'Bank Mandiri',
                        'bank_account_number' => '14800'.rand(100000, 999999),
                        'bank_account_holder' => $cashierUser->name,
                        'base_salary' => 3000000.00,
                        'is_active' => true,
                        'joined_at' => now()->subMonths(6)->toDateString(),
                    ]
                );
            }
        }

        // 5. Seed Attendance (Work Sessions & Presensi) for recent days
        $allEmployees = Employee::withoutGlobalScopes()->get();
        foreach ($allEmployees as $emp) {
            for ($i = 0; $i < 15; $i++) {
                $date = now()->subDays($i);
                if ($date->isSunday()) {
                    continue;
                }
                \App\Models\Attendance::firstOrCreate(
                    ['employee_id' => $emp->id, 'date' => $date->toDateString()],
                    [
                        'status' => $i % 7 === 0 ? 'terlambat' : 'hadir',
                        'check_in' => $i % 7 === 0 ? '08:45:00' : '07:55:00',
                        'check_out' => '17:05:00',
                        'notes' => $i % 7 === 0 ? 'Terlambat 45 menit karena macet' : 'Presensi Sesi Kerja Reguler',
                    ]
                );
            }
        }

        // 5. Seed Fixed Assets
        foreach ($branches as $branch) {
            $fa1 = FixedAsset::firstOrCreate(
                ['asset_code' => 'AST-'.$branch->code.'-WASH-01'],
                [
                    'branch_id' => $branch->id,
                    'account_id' => $coaMesinCuci?->id ?? 1,
                    'name' => 'Mesin Cuci LG Front Load 15kg',
                    'category' => 'Mesin',
                    'acquisition_date' => now()->subYear()->toDateString(),
                    'acquisition_cost' => 12000000.00,
                    'salvage_value' => 1000000.00,
                    'useful_life_months' => 48,
                    'depreciation_method' => 'straight_line',
                    'accumulated_depreciation' => 2750000.00,
                    'book_value' => 9250000.00,
                    'is_active' => true,
                ]
            );
            \App\Http\Controllers\AssetController::generateSchedulesForAsset($fa1);

            $fa2 = FixedAsset::firstOrCreate(
                ['asset_code' => 'AST-'.$branch->code.'-DRY-01'],
                [
                    'branch_id' => $branch->id,
                    'account_id' => $coaMesinPengering?->id ?? 1,
                    'name' => 'Mesin Pengering SpeedQueen Gas 15kg',
                    'category' => 'Mesin',
                    'acquisition_date' => now()->subYear()->toDateString(),
                    'acquisition_cost' => 18000000.00,
                    'salvage_value' => 2000000.00,
                    'useful_life_months' => 60,
                    'depreciation_method' => 'straight_line',
                    'accumulated_depreciation' => 3200000.00,
                    'book_value' => 14800000.00,
                    'is_active' => true,
                ]
            );
            \App\Http\Controllers\AssetController::generateSchedulesForAsset($fa2);
        }

        // 5. Seed Active Promotions & Coupons
        \App\Models\Promotion::firstOrCreate(
            ['code' => 'HEMAT10'],
            [
                'name' => 'Diskon Hemat 10%',
                'code' => 'HEMAT10',
                'type' => 'percent',
                'value' => 10,
                'min_transaction' => 20000,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'is_active' => true,
            ]
        );

        \App\Models\Promotion::firstOrCreate(
            ['code' => 'DISKON50K'],
            [
                'name' => 'Potongan Rp 50.000 (Spesial)',
                'code' => 'DISKON50K',
                'type' => 'nominal',
                'value' => 50000,
                'min_transaction' => 100000,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'is_active' => true,
            ]
        );

        \App\Models\Promotion::firstOrCreate(
            ['code' => 'WELCOME'],
            [
                'name' => 'Promo Sambutan Rp 10.000',
                'code' => 'WELCOME',
                'type' => 'nominal',
                'value' => 10000,
                'min_transaction' => 25000,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'is_active' => true,
            ]
        );

        \App\Models\Promotion::firstOrCreate(
            ['code' => 'MERDEKA20'],
            [
                'name' => 'Diskon Promo Merdeka 20%',
                'code' => 'MERDEKA20',
                'type' => 'percent',
                'value' => 20,
                'min_transaction' => 30000,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'is_active' => true,
            ]
        );

        // 6. Seed Past & Today Orders for Dashboards across ALL branches
        foreach ($branches as $branch) {
            $branchCustomers = Customer::where('branch_id', $branch->id)->get();
            $cashierUser = User::where('branch_id', $branch->id)->where('email', 'like', 'cashier%')->first()
                ?? User::where('branch_id', $branch->id)->first()
                ?? User::first();

            if ($branchCustomers->isEmpty()) {
                continue;
            }

            // Seed 14 days daily revenue trend for each branch
            for ($i = 14; $i >= 0; $i--) {
                $date = now()->subDays($i);

                // 2-4 orders per day per branch
                $numOrders = rand(2, 4);
                for ($o = 1; $o <= $numOrders; $o++) {
                    $customer = $branchCustomers->random();
                    $service = $services->random();

                    // Determine price
                    $branchPrice = $service->branchPrices()->where('branch_id', $branch->id)->first();
                    $unitPrice = $branchPrice ? $branchPrice->price : $service->base_price;
                    $qty = rand(2, 6);
                    $subtotal = $unitPrice * $qty;
                    $total = $subtotal; // Simple total without discount

                    $seqStr = str_pad(rand(10, 9999), 4, '0', STR_PAD_LEFT);
                    $orderNumber = "ORD-{$branch->code}-{$date->format('Ymd')}-{$seqStr}";

                    // Status and payment
                    $isToday = $i === 0;
                    $productionStatus = $isToday ? ['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK', 'SIAP'][rand(0, 6)] : 'DIAMBIL';
                    $paymentStatus = $isToday && rand(0, 1) === 0 ? 'pending' : 'paid';

                    $order = Order::firstOrCreate(
                        ['order_number' => $orderNumber],
                        [
                            'branch_id' => $branch->id,
                            'customer_id' => $customer->id,
                            'cashier_id' => $cashierUser->id,
                            'order_number' => $orderNumber,
                            'subtotal' => $subtotal,
                            'discount_amount' => 0,
                            'points_used' => 0,
                            'tax_amount' => 0,
                            'total' => $total,
                            'payment_status' => $paymentStatus,
                            'payment_method' => 'cash',
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
                }
            }
        }
    }
}
