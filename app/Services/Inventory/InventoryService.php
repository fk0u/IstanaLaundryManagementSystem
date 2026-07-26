<?php

namespace App\Services\Inventory;

use App\Events\LowStockAlert;
use App\Exceptions\InsufficientStockException;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    protected $fifoService;

    public function __construct(FIFOService $fifoService)
    {
        $this->fifoService = $fifoService;
    }

    /**
     * Update stock of an inventory item.
     * If delta is negative, deducts stock using FIFO.
     *
     * @throws InsufficientStockException
     */
    public function updateStock(int $itemId, float $delta, string $source, int $sourceId): ?array
    {
        $item = InventoryItem::findOrFail($itemId);
        $result = null;

        if ($delta < 0) {
            // Deduct using FIFO
            $result = $this->fifoService->deduct($itemId, abs($delta));
            Log::info("FIFO Stock Deduction: Item ID {$itemId}, Quantity ".abs($delta).", Source {$source} (ID: {$sourceId})");
        } elseif ($delta > 0) {
            // Simply add to stock
            $item->current_stock += $delta;
            $item->save();
            Log::info("Stock Added: Item ID {$itemId}, Quantity {$delta}, Source {$source} (ID: {$sourceId})");
        }

        // Fresh load and check min_stock
        $item->refresh();
        if ((float) $item->current_stock < (float) $item->min_stock) {
            event(new LowStockAlert($item));
        }

        return $result;
    }

    /**
     * Check low stock items for a branch and fire alerts.
     *
     * @return Collection
     */
    public function checkLowStock(int $branchId)
    {
        $lowStockItems = InventoryItem::where('branch_id', $branchId)
            ->whereRaw('current_stock < min_stock')
            ->get();

        foreach ($lowStockItems as $item) {
            event(new LowStockAlert($item));
        }

        return $lowStockItems;
    }
}
