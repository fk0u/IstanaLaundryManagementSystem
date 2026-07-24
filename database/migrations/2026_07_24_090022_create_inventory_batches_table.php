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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('grn_id')->constrained('goods_received_notes')->onDelete('cascade');
            $table->string('batch_number', 30);
            $table->decimal('quantity', 10, 3);
            $table->decimal('remaining_qty', 10, 3);
            $table->decimal('unit_cost', 12, 2);
            $table->date('received_date');
            $table->timestamps();

            $table->index(['item_id', 'received_date'], 'idx_batches_item_received');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
