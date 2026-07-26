<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            // Earnings Components
            $table->decimal('bonus_kg', 12, 2)->default(0)->after('allowance');
            $table->decimal('bonus_pcs', 12, 2)->default(0)->after('bonus_kg');
            $table->decimal('transport_allowance', 12, 2)->default(0)->after('bonus_pcs');
            $table->decimal('overtime_pay', 12, 2)->default(0)->after('transport_allowance');
            $table->decimal('attendance_bonus', 12, 2)->default(0)->after('overtime_pay');

            // Deductions Components
            $table->decimal('tardiness_deduction', 12, 2)->default(0)->after('deduction');
            $table->decimal('loan_deduction', 12, 2)->default(0)->after('tardiness_deduction');
            $table->decimal('damage_deduction', 12, 2)->default(0)->after('loan_deduction');
            $table->decimal('bpjs_deduction', 12, 2)->default(0)->after('damage_deduction');

            // Additional calculation fields
            $table->decimal('total_earnings', 12, 2)->default(0)->after('attendance_bonus');
            $table->decimal('total_deductions', 12, 2)->default(0)->after('bpjs_deduction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn([
                'bonus_kg',
                'bonus_pcs',
                'transport_allowance',
                'overtime_pay',
                'attendance_bonus',
                'tardiness_deduction',
                'loan_deduction',
                'damage_deduction',
                'bpjs_deduction',
                'total_earnings',
                'total_deductions',
            ]);
        });
    }
};
