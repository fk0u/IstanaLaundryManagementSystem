<?php

namespace App\Console\Commands;

use App\Models\DepreciationSchedule;
use App\Models\Journal;
use App\Services\Finance\JournalService;
use Illuminate\Console\Command;

class BackfillDepreciationJournals extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'finance:backfill-depreciation-journals
        {--dry-run : List affected schedules without posting journals}
        {--month= : Filter by period month (format: YYYY-MM)}';

    /**
     * The console command description.
     */
    protected $description = 'Post general ledger journal entries for depreciation schedules that are marked as posted but do not have a financial journal record yet.';

    public function handle(JournalService $journalService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $monthFilter = $this->option('month');

        // Depreciation schedule IDs that already have a journal entry
        $journaledScheduleIds = Journal::withoutGlobalScopes()
            ->where('source_type', DepreciationSchedule::class)
            ->pluck('source_id');

        $query = DepreciationSchedule::with('fixedAsset')
            ->where('is_posted', true)
            ->whereNull('journal_id')
            ->whereNotIn('id', $journaledScheduleIds);

        if ($monthFilter) {
            $query->where('period_date', 'like', $monthFilter.'%');
        }

        $schedules = $query->orderBy('period_date')->get();

        if ($schedules->isEmpty()) {
            $this->info('All posted depreciation schedules are already synced with financial journals. Nothing to do.');

            return self::SUCCESS;
        }

        $this->info("Found {$schedules->count()} posted depreciation schedule(s) without a financial journal entry.");

        if ($dryRun) {
            $this->table(
                ['Schedule ID', 'Asset', 'Period Date', 'Amount', 'Book Value'],
                $schedules->map(fn ($s) => [
                    $s->id,
                    $s->fixedAsset?->name ?? 'N/A',
                    $s->period_date?->format('Y-m') ?? 'N/A',
                    number_format($s->depreciation_amount, 0, ',', '.'),
                    number_format($s->book_value, 0, ',', '.'),
                ])
            );

            return self::SUCCESS;
        }

        $posted = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($schedules->count());
        $bar->start();

        foreach ($schedules as $schedule) {
            try {
                $journalService->postDepreciationJournal($schedule);
                $posted++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("Schedule #{$schedule->id} ({$schedule->fixedAsset?->name}): {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Backfill complete: {$posted} depreciation journal(s) posted, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
