<?php

namespace App\Models;

use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'branch_id',
    'name',
    'code',
    'type',
    'value',
    'min_transaction',
    'service_id',
    'applicable_tier',
    'usage_limit',
    'usage_count',
    'per_customer_limit',
    'start_date',
    'end_date',
    'is_active',
])]
class Promotion extends Model
{
    use BranchScoped, HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_transaction' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'per_customer_limit' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Override bootBranchScoped to allow global promotions (branch_id is null)
     */
    protected static function bootBranchScoped(): void
    {
        static::addGlobalScope('branch_scope', function (Builder $builder) {
            $branchId = session('scoped_branch_id');

            if ($branchId !== null) {
                $builder->where(function (Builder $query) use ($branchId) {
                    $query->where(static::getBranchColumn(), $branchId)
                        ->orWhereNull(static::getBranchColumn());
                });
            }
        });

        static::creating(function ($model) {
            // Nullable branch_id is allowed for global promotions,
            // so we only auto-set if not explicitly set and not intended to be global.
            if (! $model->{static::getBranchColumn()}) {
                $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;
                if ($branchId) {
                    $model->{static::getBranchColumn()} = $branchId;
                }
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
