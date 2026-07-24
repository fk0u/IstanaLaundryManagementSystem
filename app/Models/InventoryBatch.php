<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['item_id', 'grn_id', 'batch_number', 'quantity', 'remaining_qty', 'unit_cost', 'received_date'])]
class InventoryBatch extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'remaining_qty' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'received_date' => 'date',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }
}
