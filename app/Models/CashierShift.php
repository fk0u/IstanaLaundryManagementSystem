<?php

namespace App\Models;

use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashierShift extends Model
{
    use BranchScoped, HasFactory;

    protected $fillable = [
        'branch_id',
        'cashier_id',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash_system',
        'closing_cash_actual',
        'cash_difference',
        'petty_cash_total',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_cash' => 'decimal:2',
            'closing_cash_system' => 'decimal:2',
            'closing_cash_actual' => 'decimal:2',
            'cash_difference' => 'decimal:2',
            'petty_cash_total' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function pettyCashRecords(): HasMany
    {
        return $this->hasMany(PettyCashRecord::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
