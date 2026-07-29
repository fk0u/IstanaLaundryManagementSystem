<?php

namespace App\Observers;

use App\Jobs\PostOrderJournalJob;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     *
     * Covers the primary POS flow where a cashier creates an order that is
     * already fully paid at creation time (e.g. cash payment). This never
     * fires an "updated" event, so the journal must be queued here too.
     */
    public function created(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            PostOrderJournalJob::dispatch($order->id);
            $this->flushDashboardCache($order->branch_id);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // When payment status changes to paid, queue journal + points work.
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            PostOrderJournalJob::dispatch($order->id);
        }

        // Bust cached dashboard aggregates for any meaningful order change.
        if ($order->wasChanged(['total', 'payment_status', 'production_status', 'branch_id'])) {
            $this->flushDashboardCache($order->branch_id);
        }
    }

    /**
     * Invalidate the per-tenant dashboard cache so charts recompute after the
     * queue worker finishes the journal post. The cache TTL is short anyway,
     * this just speeds up freshness for the affected branch.
     */
    protected function flushDashboardCache(?int $branchId): void
    {
        Cache::forget("dashboard:owner:{$branchId}");
        Cache::forget('dashboard:owner:global');
    }
}
