<?php

namespace App\Models;

use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalExpense extends Model
{
    use BranchScoped, HasFactory;

    protected $fillable = [
        'branch_id',
        'expense_date',
        'account_id',
        'amount',
        'description',
        'receipt_number',
        'payment_method',
        'status',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
