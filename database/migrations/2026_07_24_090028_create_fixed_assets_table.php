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
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->string('asset_code', 20)->unique();
            $table->string('name', 100);
            $table->string('category', 50); // Mesin, Kendaraan, Furniture, dll
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->smallInteger('useful_life_months');
            $table->enum('depreciation_method', ['straight_line', 'double_declining']);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('book_value', 15, 2);
            $table->boolean('is_active')->default(true)->index();
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_value', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
