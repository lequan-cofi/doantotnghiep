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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('lease_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            
            // Ratings (separate columns for flexibility)
            $table->decimal('overall_rating', 2, 1); // 1.0 to 5.0
            $table->decimal('location_rating', 2, 1)->nullable();
            $table->decimal('quality_rating', 2, 1)->nullable();
            $table->decimal('service_rating', 2, 1)->nullable();
            $table->decimal('price_rating', 2, 1)->nullable();
            
            // Content
            $table->string('title');
            $table->text('content');
            $table->json('highlights')->nullable(); // ['clean', 'location', 'price', etc.]
            $table->json('images')->nullable(); // Array of image paths
            $table->enum('recommend', ['yes', 'maybe', 'no'])->nullable();
            
            // Engagement metrics
            $table->integer('helpful_count')->default(0);
            $table->integer('view_count')->default(0);
            
            // Status
            $table->enum('status', ['published', 'hidden'])->default('published');
            
            // Soft deletes
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['unit_id', 'status']);
            $table->index('overall_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
