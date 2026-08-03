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
        Schema::table('promotions', function (Blueprint $table) {
            if (! Schema::hasColumn('promotions', 'target_customer_type')) {
                $table->enum('target_customer_type', ['all', 'new_member_only', 'existing_member_only'])->default('all')->after('applicable_tier');
            }
            if (! Schema::hasColumn('promotions', 'max_member_age_days')) {
                $table->integer('max_member_age_days')->nullable()->default(60)->after('target_customer_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'target_customer_type')) {
                $table->dropColumn('target_customer_type');
            }
            if (Schema::hasColumn('promotions', 'max_member_age_days')) {
                $table->dropColumn('max_member_age_days');
            }
        });
    }
};
