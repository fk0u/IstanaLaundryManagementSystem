<?php

namespace App\Models;

use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'branch_id',
    'account_id',
    'asset_code',
    'name',
    'category',
    'acquisition_date',
    'acquisition_cost',
    'salvage_value',
    'useful_life_months',
    'depreciation_method',
    'accumulated_depreciation',
    'book_value',
    'is_active',
    'disposal_date',
    'disposal_value',
    'last_maintenance_date',
    'next_maintenance_date',
    'maintenance_notes',
    'notes',
    'serial_number',
    'supplier',
    'condition',
])]
class FixedAsset extends Model
{
    use BranchScoped;

    protected $table = 'fixed_assets';

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'salvage_value' => 'decimal:2',
            'useful_life_months' => 'integer',
            'accumulated_depreciation' => 'decimal:2',
            'book_value' => 'decimal:2',
            'is_active' => 'boolean',
            'disposal_date' => 'date',
            'disposal_value' => 'decimal:2',
            'last_maintenance_date' => 'date',
            'next_maintenance_date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function depreciationSchedules(): HasMany
    {
        return $this->hasMany(DepreciationSchedule::class, 'asset_id');
    }

    /**
     * Get the last posted depreciation schedule.
     */
    public function lastDepreciation()
    {
        return $this->depreciationSchedules()
            ->where('is_posted', true)
            ->orderByDesc('period_date')
            ->first();
    }

    /**
     * Get age in months since acquisition.
     */
    public function getAgeInMonthsAttribute(): int
    {
        if (! $this->acquisition_date) {
            return 0;
        }

        return (int) $this->acquisition_date->diffInMonths(now());
    }

    /**
     * Get depreciation progress percentage.
     */
    public function getDepreciationProgressAttribute(): float
    {
        if ($this->acquisition_cost <= 0) {
            return 0;
        }

        return round(($this->accumulated_depreciation / ($this->acquisition_cost - $this->salvage_value)) * 100, 1);
    }
}
