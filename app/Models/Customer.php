<?php

namespace App\Models;

use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'branch_id',
    'name',
    'phone',
    'email',
    'address',
    'member_code',
    'loyalty_tier',
    'loyalty_points',
    'total_spent',
    'transaction_count',
    'last_transaction_at',
])]
class Customer extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'loyalty_points' => 'integer',
            'total_spent' => 'decimal:2',
            'transaction_count' => 'integer',
            'last_transaction_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function loyaltyPointLogs(): HasMany
    {
        return $this->hasMany(LoyaltyPointLog::class);
    }

    /**
     * All orders across ALL branches (bypass BranchScoped).
     * Customers are global — their stats must aggregate cross-branch.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->withoutGlobalScope('branch_scope');
    }

    public function latestOrder()
    {
        return $this->hasOne(Order::class)->withoutGlobalScope('branch_scope')->latestOfMany();
    }

    public function getFormattedWaPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (! str_starts_with($phone, '62') && ! empty($phone)) {
            $phone = '62'.$phone;
        }

        return $phone;
    }
}
