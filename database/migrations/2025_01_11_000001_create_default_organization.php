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
        // Create default organization if it doesn't exist
        $defaultOrgExists = DB::table('organizations')->where('name', 'Default Organization')->exists();
        
        if (!$defaultOrgExists) {
            DB::table('organizations')->insert([
                'name' => 'Default Organization',
                'email' => 'default@organization.com',
                'phone' => null,
                'address' => 'Default Address',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove default organization
        DB::table('organizations')->where('name', 'Default Organization')->delete();
    }
};
