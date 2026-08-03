<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds delivery/pickup fields and walk-in customer name to orders table.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_name_walkin')) {
                $table->string('customer_name_walkin', 150)->nullable()->after('order_type')
                    ->comment('Nama pelanggan walk-in tanpa akun member');
            }
            if (!Schema::hasColumn('orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('customer_name_walkin')
                    ->comment('Alamat penjemputan/pengantaran untuk pickup & delivery');
            }
            if (!Schema::hasColumn('orders', 'delivery_phone')) {
                $table->string('delivery_phone', 30)->nullable()->after('delivery_address')
                    ->comment('Nomor HP koordinasi pickup/delivery');
            }
            if (!Schema::hasColumn('orders', 'pickup_scheduled_at')) {
                $table->dateTime('pickup_scheduled_at')->nullable()->after('delivery_phone')
                    ->comment('Jadwal penjemputan untuk order pickup/delivery');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name_walkin',
                'delivery_address',
                'delivery_phone',
                'pickup_scheduled_at',
            ]);
        });
    }
};
