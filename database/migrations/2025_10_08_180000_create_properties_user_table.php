<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('properties_user')) {
            Schema::create('properties_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('property_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role_key', 50)->default('agent'); // ví dụ: agent/manager
                $table->timestamp('assigned_at')->useCurrent();
                $table->timestamps();
                // audit fields to support transferring manager/CTV
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->softDeletes();
                $table->unsignedBigInteger('deleted_by')->nullable();

                $table->unique(['property_id', 'user_id'], 'uq_property_user');
                $table->index(['user_id'], 'idx_property_user_user');

                $table->foreign('property_id')
                    ->references('id')->on('properties')
                    ->onDelete('cascade');

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');

                $table->foreign('updated_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();

                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('properties_user');
    }
};


