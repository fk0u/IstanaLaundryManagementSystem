<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\CRM\LoyaltyService;
use App\Services\Finance\JournalService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    protected $journalService;

    protected $loyaltyService;

    public function __construct(JournalService $journalService, LoyaltyService $loyaltyService)
    {
        $this->journalService = $journalService;
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Handle the Order "created" event.
     *
     * Covers the primary POS flow where a cashier creates an order that is
     * already fully paid at creation time (e.g. cash payment). This never
     * fires an "updated" event, so the journal must be posted here too.
     */
    public function created(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            try {
                $this->journalService->postOrderJournal($order);
                Log::info("Auto Journal posted for newly created paid Order #{$order->id}");
            } catch (\Exception $e) {
                Log::error("Failed to auto-post journal for newly created Order #{$order->id}: ".$e->getMessage());
            }
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // When payment status changes to paid, trigger journal and points
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            try {
                // 1. Post double-entry journal entry
                $this->journalService->postOrderJournal($order);
                Log::info("Auto Journal posted for paid Order #{$order->id}");

                // 2. Award loyalty points if customer is linked
                if ($order->customer_id) {
                    $this->loyaltyService->awardPoints($order);
                    Log::info("Loyalty points awarded for paid Order #{$order->id}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to auto-process order observers for Order #{$order->id}: ".$e->getMessage());
            }
        }
    }
}
