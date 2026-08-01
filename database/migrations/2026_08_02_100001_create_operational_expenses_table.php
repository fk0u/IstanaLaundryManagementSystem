<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('expense_date');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->comment('Expense COA (5-2101 Listrik, 5-2102 Air, etc.)');
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->string('receipt_number')->nullable();
            $table->enum('payment_method', ['cash', 'transfer'])->default('cash');
            $table->enum('status', ['draft', 'posted'])->default('posted');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'expense_date']);
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_expenses');
    }
};
