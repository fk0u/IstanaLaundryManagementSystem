<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'branch_id',
    'accounting_period_id',
    'reference',
    'source_type',
    'source_id',
    'type',
    'description',
    'date',
    'status',
    'reversed_by',
    'created_by',
    'posted_at',
])]
class Journal extends Model
{
    use Auditable, BranchScoped;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function reversedByJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'reversed_by');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
