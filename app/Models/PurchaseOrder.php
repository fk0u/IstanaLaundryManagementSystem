<?php

namespace App\Models;

use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['pr_id', 'branch_id', 'po_number', 'supplier_id', 'status', 'subtotal', 'tax_amount', 'total', 'order_date', 'expected_date'])]
class PurchaseOrder extends Model
{
    use BranchScoped;

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'order_date' => 'date',
            'expected_date' => 'date',
        ];
    }

    public function pr(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'pr_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id');
    }

    public function goodsReceivedNotes(): HasMany
    {
        return $this->hasMany(GoodsReceivedNote::class, 'po_id');
    }
}
