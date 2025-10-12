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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->date('dob')->nullable()->comment('Ngày sinh');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->comment('Giới tính');
            $table->string('id_number', 50)->nullable()->comment('Số CMND/CCCD');
            $table->date('id_issued_at')->nullable()->comment('Ngày cấp CMND/CCCD');
            $table->json('id_images')->nullable()->comment('Hình ảnh CMND/CCCD');
            $table->string('address', 255)->nullable()->comment('Địa chỉ thường trú');
            $table->text('note')->nullable()->comment('Ghi chú');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('id_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
