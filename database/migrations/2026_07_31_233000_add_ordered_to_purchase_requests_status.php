<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('purchase_requests') && Schema::hasColumn('purchase_requests', 'status')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement(
                    "ALTER TABLE `purchase_requests` "
                    ."MODIFY `status` ENUM('draft','pending_approval','approved','ordered','rejected') "
                    ."NOT NULL DEFAULT 'pending_approval'"
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_requests') && Schema::hasColumn('purchase_requests', 'status')) {
            DB::table('purchase_requests')
                ->where('status', 'ordered')
                ->update(['status' => 'approved']);

            if (DB::getDriverName() !== 'sqlite') {
                DB::statement(
                    "ALTER TABLE `purchase_requests` "
                    ."MODIFY `status` ENUM('draft','pending_approval','approved','rejected') "
                    ."NOT NULL DEFAULT 'pending_approval'"
                );
            }
        }
    }
};
