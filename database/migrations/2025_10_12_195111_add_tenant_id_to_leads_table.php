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
        Schema::table('leads', function (Blueprint $table) {
            // Add tenant_id column to link with users table
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            
            // Add foreign key constraint
            $table->foreign('tenant_id')->references('id')->on('users')->onDelete('set null');
            
            // Add index for better performance
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop foreign key and index
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            
            // Drop tenant_id column
            $table->dropColumn('tenant_id');
        });
    }
};
