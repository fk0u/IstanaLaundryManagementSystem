<?php

namespace App\Models;

use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    use BranchScoped, HasFactory;

    protected $fillable = [
        'branch_id',
        'supplier_id',
        'grn_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function goodsReceivedNote()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
