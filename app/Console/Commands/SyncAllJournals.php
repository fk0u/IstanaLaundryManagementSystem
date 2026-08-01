<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncAllJournals extends Command
{
    protected $signature = 'finance:sync-all
        {--dry-run : Simulate all backfills without posting journals}';

    protected $description = 'Master command to synchronize ALL un-synced financial journals (Orders, Payroll, Depreciation) in a single run.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║    ISTANA LAUNDRY — FINANCIAL SYNC-ALL MASTER           ║');
        $this->info('║    Sinkronisasi Jurnal Keuangan Menyeluruh              ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 MODE: DRY-RUN (simulasi, tidak ada data yang diubah)');
            $this->newLine();
        }

        $overallFailed = false;

        // 1. Backfill Order Journals
        $this->info('━━━ [1/3] Sinkronisasi Jurnal Order POS ━━━');
        $exitCode = $this->call('finance:backfill-order-journals', $dryRun ? ['--dry-run' => true] : []);
        if ($exitCode !== 0) {
            $overallFailed = true;
        }
        $this->newLine();

        // 2. Backfill Payroll Journals
        $this->info('━━━ [2/3] Sinkronisasi Jurnal Payroll HR ━━━');
        $exitCode = $this->call('finance:backfill-payroll-journals', $dryRun ? ['--dry-run' => true] : []);
        if ($exitCode !== 0) {
            $overallFailed = true;
        }
        $this->newLine();

        // 3. Backfill Depreciation Journals
        $this->info('━━━ [3/3] Sinkronisasi Jurnal Penyusutan Aset ━━━');
        $exitCode = $this->call('finance:backfill-depreciation-journals', $dryRun ? ['--dry-run' => true] : []);
        if ($exitCode !== 0) {
            $overallFailed = true;
        }
        $this->newLine();

        // Summary
        $this->info('══════════════════════════════════════════════════════════');
        if ($overallFailed) {
            $this->error('⚠️  Sinkronisasi selesai dengan beberapa error. Periksa log di atas.');
        } else {
            $this->info('✅ Semua modul keuangan berhasil disinkronkan!');
        }
        $this->info('══════════════════════════════════════════════════════════');
        $this->newLine();

        return $overallFailed ? self::FAILURE : self::SUCCESS;
    }
}
