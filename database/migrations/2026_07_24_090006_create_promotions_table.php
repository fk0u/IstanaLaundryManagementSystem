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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade'); // NULL = all branches
            $table->string('name');
            $table->string('code', 20)->nullable()->unique();
            $table->enum('type', ['percent', 'nominal', 'buy_x_get_y', 'loyalty_tier']);
            $table->decimal('value', 12, 2);
            $table->decimal('min_transaction', 12, 2)->default(0);
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('cascade'); // NULL = all services
            $table->enum('applicable_tier', ['Bronze', 'Silver', 'Gold', 'Platinum'])->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('per_customer_limit')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
