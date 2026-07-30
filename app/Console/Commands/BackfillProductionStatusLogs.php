<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ProductionStatusLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BackfillProductionStatusLogs extends Command
{
    protected $signature = 'production:backfill-status-logs {--dry-run : Preview missing status logs without writing to database}';

    protected $description = 'Backfill production status logs for existing orders that are missing activity logs.';

    public const STATUS_STAGES = [
        'TERIMA',
        'PILAH',
        'CUCI',
        'KERING',
        'LIPAT',
        'CEK',
        'SIAP',
        'DIAMBIL',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $ordersWithLogs = ProductionStatusLog::pluck('order_id')->unique();
        $orders = Order::withoutGlobalScopes()
            ->whereNotIn('id', $ordersWithLogs)
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('All orders already have production status logs. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$orders->count()} order(s) missing production status logs.");

        if ($dryRun) {
            $this->table(
                ['Order ID', 'Order Number', 'Branch ID', 'Current Status', 'Created At'],
                $orders->map(fn ($o) => [$o->id, $o->order_number, $o->branch_id, $o->production_status, $o->created_at])
            );
            return self::SUCCESS;
        }

        $allUsers = User::all();
        $logsCreated = 0;

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $order) {
            $currentStatus = $order->production_status ?? 'TERIMA';
            $targetIndex = array_search($currentStatus, self::STATUS_STAGES);
            if ($targetIndex === false) {
                $targetIndex = 0;
            }

            // Find users for this branch or fallback to cashier/owner
            $branchUsers = $allUsers->filter(fn ($u) => $u->branch_id == $order->branch_id);
            if ($branchUsers->isEmpty()) {
                $branchUsers = $allUsers;
            }

            $baseTime = Carbon::parse($order->created_at);

            for ($i = 0; $i <= $targetIndex; $i++) {
                $stage = self::STATUS_STAGES[$i];
                $staff = $branchUsers->random();
                $logTime = (clone $baseTime)->addMinutes($i * 15);

                ProductionStatusLog::create([
                    'order_id' => $order->id,
                    'status' => $stage,
                    'updated_by' => $staff->id,
                    'notes' => "Proses produksi status {$stage} untuk nota {$order->order_number}.",
                    'created_at' => $logTime,
                ]);

                $logsCreated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Backfill complete: {$logsCreated} production status log(s) created across {$orders->count()} order(s).");

        return self::SUCCESS;
    }
}
