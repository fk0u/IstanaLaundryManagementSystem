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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('name');
            $table->string('phone', 20)->unique();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('member_code', 20)->nullable()->unique();
            $table->enum('loyalty_tier', ['Bronze', 'Silver', 'Gold', 'Platinum'])->default('Bronze');
            $table->integer('loyalty_points')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();

            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
