<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryBatch;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class FIFOService
{
    /**
     * Deduct stock of an inventory item using FIFO method and calculate COGS.
     *
     * @param int $itemId
     * @param float $quantityToDeduct
     * @return array
     * @throws InsufficientStockException
     */
    public function deduct(int $itemId, float $quantityToDeduct): array
    {
        return DB::transaction(function () use ($itemId, $quantityToDeduct) {
            $item = InventoryItem::findOrFail($itemId);

            if ((float) $item->current_stock < $quantityToDeduct) {
                throw new InsufficientStockException("Stok tidak mencukupi untuk item: {$item->name}. Stok saat ini: {$item->current_stock}, diminta: {$quantityToDeduct}");
            }

            $batches = InventoryBatch::where('item_id', $itemId)
                ->where('remaining_qty', '>', 0)
                ->orderBy('received_date', 'asc') // FIFO: oldest batch first
                ->lockForUpdate() // Avoid concurrency issues
                ->get();

            $remainingToDeduct = $quantityToDeduct;
            $totalCogs = 0;
            $deductionDetails = [];

            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

                $remainingQty = (float) $batch->remaining_qty;
                if ($remainingQty >= $remainingToDeduct) {
                    $deductFromBatch = $remainingToDeduct;
                } else {
                    $deductFromBatch = $remainingQty;
                }

                $batch->remaining_qty = $remainingQty - $deductFromBatch;
                $batch->save();

                $batchCogs = $deductFromBatch * (float) $batch->unit_cost;
                $totalCogs += $batchCogs;

                $deductionDetails[] = [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity' => $deductFromBatch,
                    'unit_cost' => (float) $batch->unit_cost,
                    'cogs' => $batchCogs
                ];

                $remainingToDeduct -= $deductFromBatch;
            }

            // In case of discrepancy
            if ($remainingToDeduct > 0) {
                throw new InsufficientStockException("Kesalahan kalkulasi FIFO: stok tidak mencukupi pada batch.");
            }

            // Deduct the inventory item's current stock
            $item->current_stock -= $quantityToDeduct;
            $item->save();

            return [
                'total_quantity' => $quantityToDeduct,
                'total_cogs' => $totalCogs,
                'details' => $deductionDetails
            ];
        });
    }
}
