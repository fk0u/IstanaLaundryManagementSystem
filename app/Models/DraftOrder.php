<?php

namespace App\Models;

use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftOrder extends Model
{
    use BranchScoped, HasFactory;

    protected $fillable = [
        'branch_id',
        'cashier_id',
        'customer_id',
        'draft_name',
        'cart_data',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'cart_data' => 'array',
            'total_amount' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
