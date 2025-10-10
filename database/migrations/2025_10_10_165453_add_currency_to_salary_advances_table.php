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
        Schema::table('salary_advances', function (Blueprint $table) {
            // Add currency column if it doesn't exist
            if (!Schema::hasColumn('salary_advances', 'currency')) {
                $table->string('currency', 3)->default('VND')->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_advances', function (Blueprint $table) {
            if (Schema::hasColumn('salary_advances', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};