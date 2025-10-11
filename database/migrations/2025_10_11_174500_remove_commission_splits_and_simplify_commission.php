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
        // Drop commission event splits table
        Schema::dropIfExists('commission_event_splits');
        
        // Drop commission policy splits table
        Schema::dropIfExists('commission_policy_splits');
        
        // Add user_id column to commission_events table for direct assignment
        Schema::table('commission_events', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->after('agent_id');
            $table->index(['user_id', 'status']);
        });
        
        // Update existing commission events to set user_id = agent_id
        DB::statement('UPDATE commission_events SET user_id = agent_id WHERE user_id IS NULL');
        
        // Make user_id not nullable after data migration
        Schema::table('commission_events', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove user_id column from commission_events
        Schema::table('commission_events', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropColumn('user_id');
        });
        
        // Recreate commission_policy_splits table
        Schema::create('commission_policy_splits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('policy_id');
            $table->string('role_key', 50);
            $table->decimal('percent_share', 5, 2);
            $table->timestamps();
            
            $table->unique(['policy_id', 'role_key']);
            $table->foreign('policy_id')->references('id')->on('commission_policies')->onDelete('cascade');
        });
        
        // Recreate commission_event_splits table
        Schema::create('commission_event_splits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role_key', 50);
            $table->decimal('percent_share', 5, 2);
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'reversed', 'cancelled'])->default('pending');
            $table->timestamps();
            
            $table->foreign('event_id')->references('id')->on('commission_events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['event_id', 'status']);
        });
    }
};