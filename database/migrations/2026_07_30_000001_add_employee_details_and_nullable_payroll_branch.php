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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('position');
            $table->string('birth_place', 100)->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->text('address')->nullable()->after('birth_date');
            $table->string('bank_name', 50)->nullable()->after('address');
            $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            $table->string('bank_account_holder', 100)->nullable()->after('bank_account_number');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'birth_place',
                'birth_date',
                'address',
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
            ]);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable(false)->change();
        });
    }
};
