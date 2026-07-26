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
}
