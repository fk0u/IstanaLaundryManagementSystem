<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\CRM\LoyaltyService;
use App\Services\Finance\JournalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Posts the double-entry journal for a paid order and awards loyalty points
 * off the request cycle. Extracted from OrderObserver so journal posting no
 * longer blocks the cashier/POS response.
 *
 * Accepts the order id and re-fetches the model to avoid serializing a stale
 * snapshot across the queue boundary.
 */
class PostOrderJournalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public int $orderId)
    {
    }

    public function handle(JournalService $journalService, LoyaltyService $loyaltyService): void
    {
        $order = Order::find($this->orderId);

        if (! $order) {
            Log::warning("PostOrderJournalJob: Order #{$this->orderId} not found, skipping.");

            return;
        }

        if ($order->payment_status !== 'paid') {
            // Guard: only paid orders post a journal.
            return;
        }

        try {
            $journalService->postOrderJournal($order);
            Log::info("Auto Journal posted (queued) for paid Order #{$order->id}");

            if ($order->customer_id) {
                $loyaltyService->awardPoints($order);
                Log::info("Loyalty points awarded (queued) for paid Order #{$order->id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to post journal (queued) for Order #{$order->id}: ".$e->getMessage());
            throw $e;
        } finally {
            // Bust cached dashboard aggregates for this branch + global view.
            $this->flushDashboardCache($order->branch_id);
        }
    }

    protected function flushDashboardCache(?int $branchId): void
    {
        Cache::forget("dashboard:owner:{$branchId}");
        Cache::forget('dashboard:owner:global');
    }
}
