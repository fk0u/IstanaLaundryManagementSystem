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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('accounting_period_id')->constrained('accounting_periods')->onDelete('cascade');
            $table->string('reference', 50);
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->enum('type', ['auto', 'manual', 'adjustment', 'reversal']);
            $table->text('description');
            $table->date('date');
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->foreignId('reversed_by')->nullable()->constrained('journals')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'date'], 'idx_journals_branch_date');
            $table->index(['source_type', 'source_id'], 'idx_journals_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
