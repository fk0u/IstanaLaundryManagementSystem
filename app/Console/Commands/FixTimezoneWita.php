<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixTimezoneWita extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-timezone-wita';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shift existing database transaction timestamps by +1 hour from WIB to WITA (UTC+8)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai penyesuaian timestamp database dari WIB ke WITA (+1 Jam)...');

        $driver = DB::getDriverName();

        $tables = [
            'orders' => ['created_at', 'updated_at', 'paid_at', 'estimated_done_at', 'pickup_scheduled_at'],
            'order_items' => ['created_at', 'updated_at'],
            'order_payments' => ['created_at', 'updated_at', 'paid_at'],
            'production_status_logs' => ['created_at', 'updated_at'],
            'cashier_shifts' => ['created_at', 'updated_at', 'opened_at', 'closed_at'],
            'purchase_orders' => ['created_at', 'updated_at', 'order_date', 'expected_date'],
            'purchase_order_items' => ['created_at', 'updated_at'],
            'audit_logs' => ['created_at', 'updated_at'],
        ];

        foreach ($tables as $table => $columns) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            $validCols = array_filter($columns, fn ($col) => DB::getSchemaBuilder()->hasColumn($table, $col));
            if (empty($validCols)) {
                continue;
            }

            $setExprs = [];
            foreach ($validCols as $col) {
                if ($driver === 'sqlite') {
                    $setExprs[] = "{$col} = datetime({$col}, '+1 hour')";
                } else {
                    $setExprs[] = "{$col} = DATE_ADD({$col}, INTERVAL 1 HOUR)";
                }
            }

            $sql = "UPDATE {$table} SET ".implode(', ', $setExprs)." WHERE ".implode(' IS NOT NULL OR ', $validCols).' IS NOT NULL';
            $affected = DB::update($sql);

            $this->info("Berhasil meng-update {$affected} baris pada tabel '{$table}'.");
        }

        $this->info('Penyesuaian zona waktu WITA (+1 Jam) selesai!');
        return 0;
    }
}
