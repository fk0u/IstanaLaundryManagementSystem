<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'old_salary', 'new_salary', 'effective_date', 'notes', 'changed_by'])]
class SalaryHistory extends Model
{
    protected $table = 'salary_histories';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'old_salary' => 'decimal:2',
            'new_salary' => 'decimal:2',
            'effective_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
