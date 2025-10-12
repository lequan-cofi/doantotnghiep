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
        Schema::create('viewings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('listing_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('lead_name');
            $table->string('lead_phone');
            $table->string('lead_email')->nullable();
            $table->datetime('schedule_at');
            $table->enum('status', ['requested', 'confirmed', 'done', 'no_show', 'cancelled'])->default('requested');
            $table->text('result_note')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();

            // Foreign keys
            $table->foreign('lead_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('listing_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['agent_id', 'schedule_at']);
            $table->index(['listing_id', 'schedule_at']);
            $table->index(['status', 'schedule_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viewings');
    }
};
