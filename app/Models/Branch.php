<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

#[Fillable(['code', 'name', 'address', 'phone', 'email', 'google_maps_url', 'lat', 'lng', 'is_active'])]
class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        // Invalidate the cached branch list whenever a branch is created,
        // updated, or deleted so the dashboard chart/selector stays fresh.
        static::saved(fn () => Cache::forget('branches:list'));
        static::deleted(fn () => Cache::forget('branches:list'));
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
        ];
    }

    public function workshops(): HasMany
    {
        return $this->hasMany(Workshop::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function serviceBranchPrices(): HasMany
    {
        return $this->hasMany(ServiceBranchPrice::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function accountingPeriods(): HasMany
    {
        return $this->hasMany(AccountingPeriod::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function fixedAssets(): HasMany
    {
        return $this->hasMany(FixedAsset::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
