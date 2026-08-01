<?php

namespace App\Jobs;

use App\Models\DepreciationSchedule;
use App\Services\Finance\JournalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Posts the double-entry journal for a depreciation schedule entry off the
 * request cycle. Dispatched when depreciation schedules that are marked as
 * posted (due period) need their financial journal entries created.
 */
class PostDepreciationJournalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public int $scheduleId)
    {
    }

    public function handle(JournalService $journalService): void
    {
        $schedule = DepreciationSchedule::with('fixedAsset')->find($this->scheduleId);

        if (! $schedule) {
            Log::warning("PostDepreciationJournalJob: Schedule #{$this->scheduleId} not found, skipping.");

            return;
        }

        if (! $schedule->is_posted) {
            // Schedule not yet due; skip
            return;
        }

        if ($schedule->journal_id) {
            // Already has a journal entry; idempotent skip
            return;
        }

        try {
            $journal = $journalService->postDepreciationJournal($schedule);
            Log::info("Depreciation journal posted (queued) for schedule #{$schedule->id}, asset: {$schedule->fixedAsset?->name} (Ref: {$journal->reference})");
        } catch (\Exception $e) {
            Log::error("Failed to post depreciation journal (queued) for schedule #{$schedule->id}: ".$e->getMessage());
            throw $e;
        }
    }
}
