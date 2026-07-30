<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'payroll_id', 'employee_id', 'base_salary', 'allowance', 'deduction',
    'attendance_days', 'work_days', 'net_salary',
    'bonus_kg', 'bonus_pcs', 'transport_allowance', 'overtime_pay', 'attendance_bonus', 'special_bonus',
    'tardiness_deduction', 'loan_deduction', 'damage_deduction',
    'bpjs_deduction', 'bpjs_kesehatan_deduction', 'bpjs_ketenagakerjaan_deduction',
    'total_earnings', 'total_deductions',
])]
class PayrollItem extends Model
{
    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'allowance' => 'decimal:2',
            'deduction' => 'decimal:2',
            'attendance_days' => 'integer',
            'work_days' => 'integer',
            'net_salary' => 'decimal:2',
            'bonus_kg' => 'decimal:2',
            'bonus_pcs' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'attendance_bonus' => 'decimal:2',
            'special_bonus' => 'decimal:2',
            'tardiness_deduction' => 'decimal:2',
            'loan_deduction' => 'decimal:2',
            'damage_deduction' => 'decimal:2',
            'bpjs_deduction' => 'decimal:2',
            'bpjs_kesehatan_deduction' => 'decimal:2',
            'bpjs_ketenagakerjaan_deduction' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'total_deductions' => 'decimal:2',
        ];
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate total earnings (base + allowances + bonuses)
     */
    public function calculateTotalEarnings(): float
    {
        return (float) $this->base_salary
            + (float) $this->allowance
            + (float) ($this->bonus_kg ?? 0)
            + (float) ($this->bonus_pcs ?? 0)
            + (float) ($this->transport_allowance ?? 0)
            + (float) ($this->overtime_pay ?? 0)
            + (float) ($this->attendance_bonus ?? 0)
            + (float) ($this->special_bonus ?? 0);
    }

    /**
     * Calculate total deductions
     */
    public function calculateTotalDeductions(): float
    {
        return (float) ($this->deduction ?? 0)
            + (float) ($this->tardiness_deduction ?? 0)
            + (float) ($this->loan_deduction ?? 0)
            + (float) ($this->damage_deduction ?? 0)
            + (float) ($this->bpjs_deduction ?? 0)
            + (float) ($this->bpjs_kesehatan_deduction ?? 0)
            + (float) ($this->bpjs_ketenagakerjaan_deduction ?? 0);
    }

    /**
     * Calculate final net salary
     */
    public function calculateNetSalary(): float
    {
        return $this->calculateTotalEarnings() - $this->calculateTotalDeductions();
    }

    /**
     * Auto-calculate and save all totals
     */
    public function saveCalculations(): void
    {
        $this->total_earnings = $this->calculateTotalEarnings();
        $this->total_deductions = $this->calculateTotalDeductions();
        $this->net_salary = $this->calculateNetSalary();
        $this->save();
    }
}
