<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include 'agent'
        DB::statement("ALTER TABLE review_replies MODIFY COLUMN user_type ENUM('tenant', 'manager', 'agent', 'owner') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE review_replies MODIFY COLUMN user_type ENUM('tenant', 'manager', 'owner') NOT NULL");
    }
};