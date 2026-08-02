<?php

namespace App\Console\Commands;

use App\Models\Journal;
use App\Models\Payroll;
use App\Services\Finance\JournalService;
use Illuminate\Console\Command;

class BackfillPayrollJournals extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'finance:backfill-payroll-journals {--dry-run : List affected payrolls without posting journals}';

    /**
     * The console command description.
     */
    protected $description = 'Post general ledger journal entries for existing payrolls that do not have a financial record yet.';

    public function handle(JournalService $journalService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Payroll IDs that already have a journal entry
        $journaledPayrollIds = Journal::withoutGlobalScopes()
            ->where('source_type', Payroll::class)
            ->pluck('source_id');

        $payrolls = Payroll::withoutBranchScope()
            ->where('status', 'final')
            ->whereNotIn('id', $journaledPayrollIds)
            ->orderBy('id')
            ->get();

        if ($payrolls->isEmpty()) {
            $this->info('All payroll records are already synced with financial journals. Nothing to do.');

            return self::SUCCESS;
        }

        $this->info("Found {$payrolls->count()} payroll record(s) without a financial journal entry.");

        if ($dryRun) {
            $this->table(
                ['Payroll ID', 'Branch ID', 'Month/Year', 'Status', 'Total Net Salary'],
                $payrolls->map(fn ($p) => [$p->id, $p->branch_id, "{$p->month}/{$p->year}", $p->status, $p->items()->sum('net_salary')])
            );

            return self::SUCCESS;
        }

        $posted = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($payrolls->count());
        $bar->start();

        foreach ($payrolls as $payroll) {
            try {
                $journalService->postPayrollJournal($payroll);
                $posted++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("Payroll #{$payroll->id} ({$payroll->month}/{$payroll->year}): {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Backfill complete: {$posted} payroll journal(s) posted, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
