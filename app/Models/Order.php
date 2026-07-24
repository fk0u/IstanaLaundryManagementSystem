<?php

namespace App\Models;

use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'order_number', 
    'branch_id', 
    'workshop_id', 
    'customer_id', 
    'cashier_id', 
    'promo_id', 
    'production_status', 
    'payment_method', 
    'payment_status', 
    'subtotal', 
    'discount_amount', 
    'points_used', 
    'tax_amount', 
    'total', 
    'paid_amount', 
    'change_amount', 
    'notes', 
    'qr_code_path', 
    'estimated_done_at', 
    'paid_at'
])]
class Order extends Model
{
    use HasFactory, SoftDeletes, BranchScoped;

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'points_used' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'estimated_done_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promo_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productionStatusLogs(): HasMany
    {
        return $this->hasMany(ProductionStatusLog::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }

    public function loyaltyPointLogs(): HasMany
    {
        return $this->hasMany(LoyaltyPointLog::class);
    }
}
