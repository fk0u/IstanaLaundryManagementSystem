<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BranchScoped
{
    /**
     * Boot the BranchScoped trait for a model.
     */
    protected static function bootBranchScoped(): void
    {
        static::addGlobalScope('branch_scope', function (Builder $builder) {
            $branchId = session('scoped_branch_id');

            // Fail-safe: if no branch scope is set in session
            if ($branchId === null) {
                $user = auth()->user();

                // Global role users default to un-restricted view (All Branches) when session scope is empty
                if ($user && $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance', 'CS_Marketing'])) {
                    return;
                }

                $branchId = $user?->branch_id;
            }

            if ($branchId !== null) {
                $builder->where(static::getBranchColumn(), $branchId);
            }
        });

        // Auto-set branch_id on creation
        static::creating(function ($model) {
            if (! $model->{static::getBranchColumn()}) {
                $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;

                if ($branchId) {
                    $model->{static::getBranchColumn()} = $branchId;
                }
            }
        });
    }

    /**
     * Get the column name for branch scoping
     */
    public static function getBranchColumn(): string
    {
        return 'branch_id';
    }

    /**
     * Query without branch scope (for super users manually querying)
     */
    public static function withoutBranchScope(): Builder
    {
        return static::withoutGlobalScope('branch_scope');
    }
}
