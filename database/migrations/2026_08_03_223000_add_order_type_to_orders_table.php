<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds order_type (outlet/pickup_delivery), customer_name_walkin, and delivery_address to orders table.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_type', ['outlet', 'pickup_delivery'])->default('outlet')->after('cashier_shift_id');
            $table->string('customer_name_walkin', 150)->nullable()->after('order_type')->comment('Nama pelanggan untuk transaksi walk-in tanpa akun member');
            $table->text('delivery_address')->nullable()->after('customer_name_walkin')->comment('Alamat penjemputan/pengantaran untuk order pickup & delivery');
            $table->string('delivery_phone', 30)->nullable()->after('delivery_address')->comment('Nomor HP untuk koordinasi pickup/delivery');
            $table->dateTime('pickup_scheduled_at')->nullable()->after('delivery_phone')->comment('Jadwal penjemputan untuk order pickup/delivery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_type',
                'customer_name_walkin',
                'delivery_address',
                'delivery_phone',
                'pickup_scheduled_at',
            ]);
        });
    }
};
