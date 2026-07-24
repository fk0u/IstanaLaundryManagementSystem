<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['asset_id', 'period_date', 'depreciation_amount', 'accumulated', 'book_value', 'is_posted', 'journal_id'])]
class DepreciationSchedule extends Model
{
    protected $table = 'depreciation_schedules';

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'depreciation_amount' => 'decimal:2',
            'accumulated' => 'decimal:2',
            'book_value' => 'decimal:2',
            'is_posted' => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'asset_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
