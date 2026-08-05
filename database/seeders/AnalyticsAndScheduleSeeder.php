<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FixedAsset;
use App\Models\LoyaltyPointLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductionStatusLog;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\User;
use App\Services\Finance\JournalService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnalyticsAndScheduleSeeder extends Seeder
{
    /**
     * Run realistic 24-hour transaction distribution, staff schedules, cashier shift recaps,
     * asset maintenance schedules, and production pipeline status seeders.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $branches = Branch::all();
            $services = Service::all();
            $promotions = Promotion::all();
            $journalService = app(JournalService::class);

        // ---------------------------------------------------------
        // 1. Hourly Weight Distribution (Jam Tersibuk 00:00 — 23:00)
        // ---------------------------------------------------------
        $hourWeights = [
            0  => 2,  // 00:00 - Online / Night Drop
            1  => 1,  // 01:00
            2  => 1,  // 02:00
            3  => 1,  // 03:00
            4  => 2,  // 04:00
            5  => 4,  // 05:00 - Subuh Drop
            6  => 8,  // 06:00 - Morning prep
            7  => 15, // 07:00 - Morning Peak
            8  => 22, // 08:00 - High Peak Morning
            9  => 18, // 09:00 - High Peak Morning
            10 => 14, // 10:00 - Mid-day steady
            11 => 12, // 11:00 - Pre-lunch
            12 => 10, // 12:00 - Lunch break
            13 => 12, // 13:00 - Afternoon steady
            14 => 15, // 14:00 - Express pickup
            15 => 17, // 15:00 - Afternoon peak start
            16 => 24, // 16:00 - High Peak Afternoon (After Work Drop-off)
            17 => 28, // 17:00 - Rush Hour Peak
            18 => 23, // 18:00 - Evening Peak
            19 => 18, // 19:00 - Evening pickup
            20 => 12, // 20:00 - Late evening
            21 => 8,  // 21:00 - Closing prep
            22 => 5,  // 22:00 - Night kiosk
            23 => 3,  // 23:00 - Night drop
        ];

        foreach ($branches as $branch) {
            $branchCustomers = Customer::where('branch_id', $branch->id)->get();
            if ($branchCustomers->isEmpty()) {
                $branchCustomers = Customer::all();
            }

            $cashierUser = User::where('branch_id', $branch->id)->where('email', 'like', 'cashier%')->first()
                ?? User::where('branch_id', $branch->id)->first()
                ?? User::first();

            $operators = User::where('branch_id', $branch->id)->get();

            // Seed 45 days of rich, 24-hour distributed transactions
            for ($dayOffset = 45; $dayOffset >= 0; $dayOffset--) {
                $targetDate = now()->subDays($dayOffset);

                for ($h = 0; $h <= 23; $h++) {
                    $weight = $hourWeights[$h];
                    // Calculate order frequency based on hourly weight
                    $ordersCount = rand(0, 100) <= ($weight * 4) ? rand(1, max(1, (int) ceil($weight / 7))) : 0;

                    for ($o = 0; $o < $ordersCount; $o++) {
                        $minute = rand(0, 59);
                        $second = rand(0, 59);
                        $createdAt = $targetDate->copy()->setHour($h)->setMinute($minute)->setSecond($second);

                        $customer = $branchCustomers->random();
                        $service = $services->random();
                        $branchPrice = $service->branchPrices()->where('branch_id', $branch->id)->first();
                        $unitPrice = $branchPrice ? $branchPrice->price : $service->base_price;
                        $qty = rand(2, 10);
                        $subtotal = $unitPrice * $qty;

                        // 20% chance of promo discount
                        $discountAmount = 0;
                        if (rand(1, 100) <= 20 && $promotions->isNotEmpty()) {
                            $promo = $promotions->random();
                            if ($promo->type === 'percent') {
                                $discountAmount = round(($subtotal * $promo->value) / 100);
                            } else {
                                $discountAmount = min($subtotal, $promo->value);
                            }
                        }

                        $total = max(0, $subtotal - $discountAmount);
                        $seqStr = sprintf('%04d', ($i * 10) + rand(1, 9));
                        $orderNumber = "ORD-{$branch->code}-{$createdAt->format('Ymd')}-{$seqStr}";
                        $orderType = rand(1, 10) <= 8 ? 'outlet' : 'pickup_delivery';

                        if ($dayOffset === 0) {
                            $prodStatus = ['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK', 'SIAP'][rand(0, 6)];
                            $payStatus = rand(0, 1) === 0 ? 'pending' : 'paid';
                        } else {
                            $prodStatus = 'DIAMBIL';
                            $payStatus = 'paid';
                        }

                        $order = Order::firstOrCreate(
                            ['order_number' => $orderNumber],
                            [
                                'branch_id' => $branch->id,
                                'customer_id' => $customer->id,
                                'cashier_id' => $cashierUser->id,
                                'order_type' => $orderType,
                                'subtotal' => $subtotal,
                                'discount_amount' => $discountAmount,
                                'points_used' => 0,
                                'tax_amount' => 0,
                                'total' => $total,
                                'payment_status' => $payStatus,
                                'payment_method' => ['cash', 'transfer', 'invoice'][rand(0, 2)],
                                'paid_amount' => $payStatus === 'paid' ? $total : 0,
                                'change_amount' => 0,
                                'production_status' => $prodStatus,
                                'created_at' => $createdAt,
                                'updated_at' => $createdAt,
                            ]
                        );

                        OrderItem::firstOrCreate(
                            ['order_id' => $order->id, 'service_id' => $service->id],
                            [
                                'quantity' => $qty,
                                'unit' => $service->unit,
                                'unit_price' => $unitPrice,
                                'discount' => $discountAmount,
                                'subtotal' => $total,
                            ]
                        );

                        // Seed Production Logs for stage-by-stage analytics tracking
                        $stages = ['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK', 'SIAP', 'DIAMBIL'];
                        $logTime = $createdAt->copy();
                        foreach ($stages as $st) {
                            ProductionStatusLog::firstOrCreate(
                                ['order_id' => $order->id, 'status' => $st],
                                [
                                    'updated_by' => $operators->isNotEmpty() ? $operators->random()->id : $cashierUser->id,
                                    'notes' => "Proses $st berhasil diselesaikan di Workshop {$branch->name}",
                                    'created_at' => $logTime,
                                ]
                            );
                            $logTime = $logTime->copy()->addMinutes(rand(20, 90));
                            if ($st === $prodStatus) {
                                break;
                            }
                        }

                        // Seed Loyalty Point Log
                        if ($payStatus === 'paid') {
                            $earnedPoints = (int) floor($total / 1000);
                            if ($earnedPoints > 0) {
                                LoyaltyPointLog::firstOrCreate(
                                    ['order_id' => $order->id, 'type' => 'earn'],
                                    [
                                        'customer_id' => $customer->id,
                                        'points' => $earnedPoints,
                                        'balance_after' => ($customer->points ?? 0) + $earnedPoints,
                                        'description' => "Poin dari transaksi #{$order->order_number}",
                                        'created_at' => $createdAt,
                                    ]
                                );
                            }

                            try {
                                $journalService->postOrderJournal($order);
                            } catch (\Throwable $e) {
                            }
                        }
                    }
                }
            }

            // ---------------------------------------------------------
            // 2. Seed Cashier Shifts & Daily Settlement (Rekap Shift)
            // ---------------------------------------------------------
            for ($dayOffset = 30; $dayOffset >= 0; $dayOffset--) {
                $shiftDate = now()->subDays($dayOffset);
                $openingCash = 500000.00;

                $cashSales = Order::where('branch_id', $branch->id)
                    ->where('payment_method', 'cash')
                    ->where('payment_status', 'paid')
                    ->whereDate('created_at', $shiftDate->toDateString())
                    ->sum('total');

                $expectedCash = $openingCash + $cashSales;
                $variance = rand(-1, 2) * 5000;
                $actualCash = max(0, $expectedCash + $variance);

                CashierShift::firstOrCreate(
                    ['branch_id' => $branch->id, 'opened_at' => $shiftDate->copy()->setHour(7)->setMinute(0)],
                    [
                        'cashier_id' => $cashierUser->id,
                        'opened_at' => $shiftDate->copy()->setHour(7)->setMinute(0),
                        'closed_at' => $shiftDate->copy()->setHour(21)->setMinute(0),
                        'opening_cash' => $openingCash,
                        'closing_cash_system' => $expectedCash,
                        'closing_cash_actual' => $actualCash,
                        'cash_difference' => $variance,
                        'petty_cash_total' => 0,
                        'status' => 'CLOSED',
                        'notes' => $variance === 0 ? 'Serah terima shift kasir pas seimbang' : ($variance > 0 ? 'Surplus selisih kas kecil' : 'Defisit selisih kembalian'),
                        'created_at' => $shiftDate->copy()->setHour(21)->setMinute(0),
                    ]
                );
            }

            // ---------------------------------------------------------
            // 3. Seed Fixed Asset Maintenance Schedules
            // ---------------------------------------------------------
            $assets = FixedAsset::where('branch_id', $branch->id)->get();
            foreach ($assets as $asset) {
                $asset->update([
                    'last_maintenance_date' => now()->subDays(rand(10, 45))->toDateString(),
                    'next_maintenance_date' => now()->addDays(rand(5, 30))->toDateString(),
                    'maintenance_notes' => 'Jadwal pemeliharaan rutin: Pembersihan filter lint, pelumasan bearing drum, dan inspek selang gas.',
                    'condition' => rand(1, 10) <= 8 ? 'good' : 'fair',
                    'serial_number' => 'SN-'.$branch->code.'-'.rand(100000, 999999),
                    'supplier' => 'PT Laundry Indonesia Teknik',
                ]);
            }

            // ---------------------------------------------------------
            // 4. Seed Employee Shift Rosters & Work Schedules
            // ---------------------------------------------------------
            $employees = Employee::withoutGlobalScopes()->where('branch_id', $branch->id)->get();
            foreach ($employees as $emp) {
                for ($d = 30; $d >= 0; $d--) {
                    $attDate = now()->subDays($d);
                    if ($attDate->isSunday()) {
                        continue;
                    }

                    // Multi-shift roster simulation: Morning (07:00), Afternoon (14:00), Night (22:00)
                    $shiftType = rand(1, 3);
                    $checkIn = match ($shiftType) {
                        1 => '07:00:00',
                        2 => '14:00:00',
                        3 => '22:00:00',
                    };
                    $checkOut = match ($shiftType) {
                        1 => '15:00:00',
                        2 => '22:00:00',
                        3 => '06:00:00',
                    };

                    $status = rand(1, 20) === 1 ? 'terlambat' : 'hadir';

                    try {
                        Attendance::firstOrCreate(
                            ['employee_id' => $emp->id, 'date' => $attDate->format('Y-m-d')],
                            [
                                'status' => $status,
                                'check_in' => $checkIn,
                                'check_out' => $checkOut,
                                'notes' => $status === 'terlambat' ? 'Terlambat 15 menit karena cuaca hujan deras' : 'Presensi shift kerja tepat waktu',
                            ]
                        );
                    } catch (\Throwable $e) {
                    }
                }
            }
        }
        });
    }
}
