<?php

namespace App\Events;

use App\Models\InventoryItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert
{
    use Dispatchable, SerializesModels;

    public $item;

    public function __construct(InventoryItem $item)
    {
        $this->item = $item;
    }
}
