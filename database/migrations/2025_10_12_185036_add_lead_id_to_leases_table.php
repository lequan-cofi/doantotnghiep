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
        Schema::table('leases', function (Blueprint $table) {
            // Add lead_id column to link with leads table
            $table->unsignedBigInteger('lead_id')->nullable()->after('tenant_id');
            
            // Add foreign key constraint
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
            
            // Add index for better performance
            $table->index('lead_id');
            
            // Modify tenant_id to be nullable when lead_id is present
            // This allows creating leases from leads without requiring a user account
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            // Drop foreign key and index
            $table->dropForeign(['lead_id']);
            $table->dropIndex(['lead_id']);
            
            // Drop lead_id column
            $table->dropColumn('lead_id');
            
            // Restore tenant_id to NOT NULL
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
        });
    }
};
