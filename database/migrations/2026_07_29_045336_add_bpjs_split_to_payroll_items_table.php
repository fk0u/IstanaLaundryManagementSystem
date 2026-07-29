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
            // Split BPJS into two components for proper Indonesian payroll compliance
            // BPJS Kesehatan: employer 4%, employee 1% of basic salary
            // BPJS Ketenagakerjaan: JHT employer 3.7%, employee 2%, JKK 0.24-1.74%, JKM 0.3%
            $table->decimal('bpjs_kesehatan_deduction', 12, 2)->default(0)->after('bpjs_deduction');
            $table->decimal('bpjs_ketenagakerjaan_deduction', 12, 2)->default(0)->after('bpjs_kesehatan_deduction');
            // Additional bonus field for general/special bonus
            $table->decimal('special_bonus', 12, 2)->default(0)->after('attendance_bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn([
                'bpjs_kesehatan_deduction',
                'bpjs_ketenagakerjaan_deduction',
                'special_bonus',
            ]);
        });
    }
};
