<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FixedAsset;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\User;
use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
                    ['branch_id' => $branch->id, 'sku' => $template['sku_prefix'] . '-' . $branch->code],
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

        // 3. Seed Customers for each branch
        $customerTemplates = [
            ['name' => 'Budi Santoso', 'phone' => '081234567890', 'email' => 'budi@gmail.com', 'address' => 'Jl. Juanda No. 12, Samarinda'],
            ['name' => 'Ani Wijaya', 'phone' => '081398765432', 'email' => 'ani@hotmail.com', 'address' => 'Jl. Pasundan Gg. 3B, Samarinda'],
            ['name' => 'Charles Hutagalung', 'phone' => '082155554444', 'email' => 'charles@yahoo.com', 'address' => 'Jl. Pangeran Hidayatullah, Samarinda'],
            ['name' => 'Dewi Lestari', 'phone' => '081122223333', 'email' => 'dewi@gmail.com', 'address' => 'Jl. Lambung Mangkurat, Samarinda'],
            ['name' => 'Eko Prasetyo', 'phone' => '085299998888', 'email' => 'eko@gmail.com', 'address' => 'Jl. Wijaya Kusuma, Samarinda'],
        ];

        $custIdx = 1;
        foreach ($branches as $branch) {
            foreach ($customerTemplates as $template) {
                Customer::firstOrCreate(
                    ['phone' => $template['phone'] . '-' . $branch->code],
                    [
                        'branch_id' => $branch->id,
                        'name' => $template['name'] . ' (' . $branch->code . ')',
                        'phone' => $template['phone'] . '-' . $branch->code,
                        'email' => $template['email'],
                        'address' => $template['address'],
                        'member_code' => 'CUST-' . $branch->code . '-' . str_pad($custIdx++, 4, '0', STR_PAD_LEFT),
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
                    ['nik' => 'NIK-' . $branch->code . '-001'],
                    [
                        'branch_id' => $branch->id,
                        'user_id' => $staffUser->id,
                        'name' => $staffUser->name,
                        'position' => 'Workshop Staff',
                        'base_salary' => 2800000.00,
                        'is_active' => true,
                        'joined_at' => now()->subMonths(10)->toDateString(),
                    ]
                );
            }

            if ($cashierUser) {
                Employee::firstOrCreate(
                    ['nik' => 'NIK-' . $branch->code . '-002'],
                    [
                        'branch_id' => $branch->id,
                        'user_id' => $cashierUser->id,
                        'name' => $cashierUser->name,
                        'position' => 'Cashier',
                        'base_salary' => 3000000.00,
                        'is_active' => true,
                        'joined_at' => now()->subMonths(6)->toDateString(),
                    ]
                );
            }
        }

        // 5. Seed Fixed Assets
        foreach ($branches as $branch) {
            FixedAsset::firstOrCreate(
                ['asset_code' => 'AST-' . $branch->code . '-WASH-01'],
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

            FixedAsset::firstOrCreate(
                ['asset_code' => 'AST-' . $branch->code . '-DRY-01'],
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
        }

        // 6. Seed Past & Today Orders for Dashboards
        foreach ($branches as $branch) {
            $branchCustomers = Customer::where('branch_id', $branch->id)->get();
            $cashierUser = User::where('branch_id', $branch->id)->where('email', 'like', 'cashier%')->first() 
                ?? User::where('branch_id', $branch->id)->first()
                ?? User::first();

            if ($branchCustomers->isEmpty()) continue;

            // Seed 7 days daily revenue trend
            for ($i = 7; $i >= 0; $i--) {
                $date = now()->subDays($i);
                
                // 1-3 orders per day
                $numOrders = rand(1, 3);
                for ($o = 1; $o <= $numOrders; $o++) {
                    $customer = $branchCustomers->random();
                    $service = $services->random();
                    
                    // Determine price
                    $branchPrice = $service->branchPrices()->where('branch_id', $branch->id)->first();
                    $unitPrice = $branchPrice ? $branchPrice->price : $service->base_price;
                    $qty = rand(2, 6);
                    $subtotal = $unitPrice * $qty;
                    $total = $subtotal; // Simple total without discount

                    $seqStr = str_pad(rand(10, 999), 4, '0', STR_PAD_LEFT);
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
