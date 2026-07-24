<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'unit', 'base_price', 'est_duration_hours', 'description', 'is_active'])]
class Service extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'est_duration_hours' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function branchPrices(): HasMany
    {
        return $this->hasMany(ServiceBranchPrice::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ServicePriceHistory::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }
}
