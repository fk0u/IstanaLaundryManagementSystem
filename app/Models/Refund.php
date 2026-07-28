<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'branch_id',
    'requested_by',
    'amount',
    'reason',
    'status',
    'cashier_approved_at',
    'branch_approved_at',
    'finance_approved_at',
    'owner_approved_at',
    'processed_at',
])]
class Refund extends Model
{
    use Auditable, BranchScoped;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'cashier_approved_at' => 'datetime',
            'branch_approved_at' => 'datetime',
            'finance_approved_at' => 'datetime',
            'owner_approved_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
