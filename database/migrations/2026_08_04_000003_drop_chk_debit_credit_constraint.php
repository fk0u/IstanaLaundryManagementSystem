<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('database.default') === 'mysql') {
            try {
                DB::statement('ALTER TABLE journal_lines DROP CHECK chk_debit_credit');
            } catch (\Throwable $e) {
                // Constraint might already be dropped or not present
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            try {
                DB::statement('ALTER TABLE journal_lines ADD CONSTRAINT chk_debit_credit CHECK (debit = 0 OR credit = 0)');
            } catch (\Throwable $e) {
                // Constraint might already exist
            }
        }
    }
};
