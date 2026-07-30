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
    'user_id',
    'nik',
    'name',
    'position',
    'phone',
    'birth_place',
    'birth_date',
    'address',
    'bank_name',
    'bank_account_number',
    'bank_account_holder',
    'base_salary',
    'is_active',
    'joined_at',
])]
class Employee extends Model
{
    use BranchScoped, HasFactory;

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'is_active' => 'boolean',
            'joined_at' => 'date',
            'birth_date' => 'date',
        ];
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? (int) $this->birth_date->age : null;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salaryHistories(): HasMany
    {
        return $this->hasMany(SalaryHistory::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }
}
