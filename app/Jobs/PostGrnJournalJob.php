<?php

namespace App\Jobs;

use App\Models\GoodsReceivedNote;
use App\Models\InventoryBatch;
use App\Services\Finance\JournalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Confirms a GRN off the request cycle: creates inventory batches, updates
 * stock + PO received quantities, posts the auto-journal, and finalizes the
 * parent PO status. Extracted from GRNObserver so the heavy work no longer
 * blocks the confirm response.
 *
 * Accepts the GRN id and re-fetches the model to avoid a stale snapshot.
 */
class PostGrnJournalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public int $grnId)
    {
    }

    public function handle(JournalService $journalService): void
    {
        $grn = GoodsReceivedNote::find($this->grnId);

        if (! $grn) {
            Log::warning("PostGrnJournalJob: GRN #{$this->grnId} not found, skipping.");

            return;
        }

        if ($grn->status !== 'confirmed') {
            return;
        }

        try {
            // 1. Process items, create batches and update stock
            foreach ($grn->items as $item) {
                InventoryBatch::create([
                    'item_id' => $item->item_id,
                    'grn_id' => $grn->id,
                    'batch_number' => $item->batch_number ?? 'BATCH-'.$grn->grn_number.'-'.$item->item_id,
                    'quantity' => $item->quantity,
                    'remaining_qty' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'received_date' => $grn->received_date ?? now()->toDateString(),
                ]);

                // Update current stock of the inventory item
                $inventoryItem = $item->item;
                if ($inventoryItem) {
                    $inventoryItem->current_stock = (float) $inventoryItem->current_stock + (float) $item->quantity;
                    $inventoryItem->save();
                }

                // Update PO Item received qty if po_item_id is set
                $poItem = $item->poItem;
                if ($poItem) {
                    $poItem->received_qty = (float) $poItem->received_qty + (float) $item->quantity;
                    $poItem->save();
                }
            }

            // 2. Post auto-journal
            $journalService->postGRNJournal($grn);
            Log::info("GRN confirmed and inventory updated with auto-journal (queued): GRN #{$grn->id}");

            // 3. Check if PO is completely received
            $po = $grn->purchaseOrder;
            if ($po) {
                $fullyReceived = true;
                foreach ($po->items as $poItem) {
                    if ((float) $poItem->received_qty < (float) $poItem->quantity) {
                        $fullyReceived = false;
                        break;
                    }
                }
                $po->status = $fullyReceived ? 'completed' : 'partial';
                $po->save();
            }
        } catch (\Exception $e) {
            Log::error("Failed to process GRN (queued) for GRN #{$grn->id}: ".$e->getMessage());
            throw $e;
        }
    }
}
