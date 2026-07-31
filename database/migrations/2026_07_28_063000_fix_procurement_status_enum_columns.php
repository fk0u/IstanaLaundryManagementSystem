<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Memperbaiki kolom `status` (ENUM) pada tabel procurement agar sesuai dengan
 * nilai status yang digunakan oleh controller & view.
 *
 * Penyebab bug 1265 "Data truncated for column 'status'":
 *   - Migration awal `purchase_requests` menetapkan ENUM ['draft','pending','approved','rejected']
 *     (mengandung nilai 'pending'), tetapi controller `PurchaseRequestController`
 *     mengirimkan nilai 'pending_approval' yang TIDAK ada di ENUM tersebut.
 *   - Konsekuensinya MySQL memotong/truncate nilai status → exception.
 *
 * Selain itu, ENUM sebelumnya juga tidak punya default yang sesuai dengan flow
 * (PR baru seharusnya berstatus 'pending_approval').
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) purchase_requests.status
        if (Schema::hasTable('purchase_requests') && Schema::hasColumn('purchase_requests', 'status')) {
            try {
                DB::table('purchase_requests')
                    ->where('status', 'pending')
                    ->update(['status' => 'pending_approval']);

                if (DB::getDriverName() !== 'sqlite') {
                    DB::statement(
                        "ALTER TABLE `purchase_requests` "
                        ."MODIFY `status` ENUM('draft','pending_approval','approved','rejected') "
                        ."NOT NULL DEFAULT 'pending_approval'"
                    );
                }
            } catch (\Throwable $e) {
                // Ignore if table does not exist
            }
        }

        // 2) purchase_orders.status
        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'status')) {
            try {
                if (DB::getDriverName() !== 'sqlite') {
                    DB::statement(
                        "ALTER TABLE `purchase_orders` "
                        ."MODIFY `status` ENUM('draft','sent','confirmed','partial','completed','cancelled') "
                        ."NOT NULL DEFAULT 'draft'"
                    );
                }
            } catch (\Throwable $e) {
                // Ignore if table does not exist
            }
        }

        // 3) goods_received_notes.status
        if (Schema::hasTable('goods_received_notes') && Schema::hasColumn('goods_received_notes', 'status')) {
            try {
                if (DB::getDriverName() !== 'sqlite') {
                    DB::statement(
                        "ALTER TABLE `goods_received_notes` "
                        ."MODIFY `status` ENUM('draft','confirmed') NOT NULL DEFAULT 'draft'"
                    );
                }
            } catch (\Throwable $e) {
                // Ignore if table does not exist
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_requests') && Schema::hasColumn('purchase_requests', 'status')) {
            // Kembalikan 'pending_approval' -> 'pending' sebelum revert ENUM.
            DB::table('purchase_requests')
                ->where('status', 'pending_approval')
                ->update(['status' => 'pending']);

            DB::statement(
                "ALTER TABLE `purchase_requests` "
                ."MODIFY `status` ENUM('draft','pending','approved','rejected') "
                ."NOT NULL DEFAULT 'draft'"
            );
        }

        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'status')) {
            DB::statement(
                "ALTER TABLE `purchase_orders` "
                ."MODIFY `status` ENUM('draft','sent','confirmed','partial','completed','cancelled') "
                ."NOT NULL DEFAULT 'draft'"
            );
        }

        if (Schema::hasTable('goods_received_notes') && Schema::hasColumn('goods_received_notes', 'status')) {
            DB::statement(
                "ALTER TABLE `goods_received_notes` "
                ."MODIFY `status` ENUM('draft','confirmed') NOT NULL DEFAULT 'draft'"
            );
        }
    }
};
