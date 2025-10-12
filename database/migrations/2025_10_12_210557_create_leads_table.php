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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            
            // Organization relationship
            $table->unsignedBigInteger('organization_id');
            
            // Lead information
            $table->string('source')->nullable()->comment('Nguồn lead');
            $table->string('name')->comment('Tên khách hàng');
            $table->string('phone')->nullable()->comment('Số điện thoại');
            $table->string('email')->nullable()->comment('Email');
            $table->string('desired_city')->nullable()->comment('Thành phố mong muốn');
            $table->decimal('budget_min', 15, 2)->nullable()->comment('Ngân sách tối thiểu');
            $table->decimal('budget_max', 15, 2)->nullable()->comment('Ngân sách tối đa');
            $table->text('note')->nullable()->comment('Ghi chú');
            $table->enum('status', ['active', 'inactive', 'converted', 'lost'])->default('active')->comment('Trạng thái');
            
            // Link to tenant user if converted
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('ID người thuê nếu đã chuyển đổi');
            
            // Soft deletes
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['organization_id', 'status']);
            $table->index(['tenant_id']);
            $table->index(['phone']);
            $table->index(['email']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
