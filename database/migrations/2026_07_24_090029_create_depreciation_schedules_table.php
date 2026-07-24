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
        Schema::create('depreciation_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('fixed_assets')->onDelete('cascade');
            $table->date('period_date'); // First day of the month
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('accumulated', 15, 2);
            $table->decimal('book_value', 15, 2);
            $table->boolean('is_posted')->default(false);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->timestamps();

            $table->unique(['asset_id', 'period_date'], 'uk_depreciation_asset_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depreciation_schedules');
    }
};
