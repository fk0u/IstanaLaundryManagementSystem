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
        Schema::table('fixed_assets', function (Blueprint $table) {
            // Maintenance tracking fields
            $table->date('last_maintenance_date')->nullable()->after('disposal_value');
            $table->date('next_maintenance_date')->nullable()->after('last_maintenance_date');
            $table->text('maintenance_notes')->nullable()->after('next_maintenance_date');
            $table->text('notes')->nullable()->after('maintenance_notes');
            $table->string('serial_number', 100)->nullable()->after('notes');
            $table->string('supplier', 100)->nullable()->after('serial_number');
            $table->string('condition', 30)->default('good')->after('supplier'); // good, fair, poor, scrapped
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropColumn([
                'last_maintenance_date',
                'next_maintenance_date',
                'maintenance_notes',
                'notes',
                'serial_number',
                'supplier',
                'condition',
            ]);
        });
    }
};
