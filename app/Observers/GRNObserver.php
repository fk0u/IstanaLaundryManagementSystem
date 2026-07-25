<?php

namespace App\Observers;

use App\Models\GoodsReceivedNote;
use App\Models\InventoryBatch;
use App\Services\Finance\JournalService;
use Illuminate\Support\Facades\Log;

class GRNObserver
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Handle the GoodsReceivedNote "updated" event.
     *
     * @param GoodsReceivedNote $grn
     * @return void
     */
    public function updated(GoodsReceivedNote $grn): void
    {
        // When GRN status changes to confirmed
        if ($grn->isDirty('status') && $grn->status === 'confirmed') {
            try {
                // 1. Process items, create batches and update stock
                foreach ($grn->items as $item) {
                    InventoryBatch::create([
                        'item_id' => $item->item_id,
                        'grn_id' => $grn->id,
                        'batch_number' => $item->batch_number ?? 'BATCH-' . $grn->grn_number . '-' . $item->item_id,
                        'quantity' => $item->quantity,
                        'remaining_qty' => $item->quantity,
                        'unit_cost' => $item->unit_cost,
                        'received_date' => $grn->received_date ?? now()->toDateString(),
                    ]);

                    // Update current stock of the inventory item
                    $inventoryItem = $item->item;
                    if ($inventoryItem) {
                        $inventoryItem->current_stock = (float)$inventoryItem->current_stock + (float)$item->quantity;
                        $inventoryItem->save();
                    }

                    // Update PO Item received qty if po_item_id is set
                    $poItem = $item->poItem;
                    if ($poItem) {
                        $poItem->received_qty = (float)$poItem->received_qty + (float)$item->quantity;
                        $poItem->save();
                    }
                }

                // 2. Post auto-journal
                $this->journalService->postGRNJournal($grn);
                Log::info("GRN confirmed and inventory updated with auto-journal: GRN #{$grn->id}");

                // 3. Check if PO is completely received
                $po = $grn->purchaseOrder;
                if ($po) {
                    $fullyReceived = true;
                    foreach ($po->items as $poItem) {
                        if ((float)$poItem->received_qty < (float)$poItem->quantity) {
                            $fullyReceived = false;
                            break;
                        }
                    }
                    $po->status = $fullyReceived ? 'completed' : 'partial';
                    $po->save();
                }
            } catch (\Exception $e) {
                Log::error("Failed to process GRNObserver for GRN #{$grn->id}: " . $e->getMessage());
            }
        }
    }
}
