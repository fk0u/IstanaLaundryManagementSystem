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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->enum('status', ['pending', 'branch_approved', 'finance_approved', 'owner_approved', 'completed', 'rejected'])->default('pending');
            $table->timestamp('cashier_approved_at')->nullable();
            $table->timestamp('branch_approved_at')->nullable();
            $table->timestamp('finance_approved_at')->nullable();
            $table->timestamp('owner_approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
