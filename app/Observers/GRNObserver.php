<?php

namespace App\Observers;

use App\Jobs\PostGrnJournalJob;
use App\Models\GoodsReceivedNote;

class GRNObserver
{
    /**
     * Handle the GoodsReceivedNote "updated" event.
     */
    public function updated(GoodsReceivedNote $grn): void
    {
        // When GRN status changes to confirmed, queue the heavy work
        // (inventory batches, stock updates, journal post, PO completion).
        if ($grn->isDirty('status') && $grn->status === 'confirmed') {
            PostGrnJournalJob::dispatch($grn->id);
        }
    }
}
