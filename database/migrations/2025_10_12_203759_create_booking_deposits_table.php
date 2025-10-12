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
        Schema::create('booking_deposits', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('tenant_user_id')->nullable()->comment('Người thuê (nếu đã có tài khoản)');
            $table->unsignedBigInteger('lead_id')->nullable()->comment('Lead khách hàng tiềm năng');
            $table->unsignedBigInteger('agent_id')->comment('Agent xử lý');
            $table->unsignedBigInteger('invoice_id')->nullable()->comment('Hóa đơn liên kết');
            
            // Deposit details
            $table->decimal('amount', 15, 2)->comment('Số tiền đặt cọc');
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'expired', 'cancelled'])
                  ->default('pending')->comment('Trạng thái thanh toán');
            $table->enum('deposit_type', ['booking', 'security', 'advance'])
                  ->default('booking')->comment('Loại đặt cọc');
            
            // Dates
            $table->datetime('hold_until')->comment('Giữ chỗ đến ngày');
            $table->datetime('paid_at')->nullable()->comment('Ngày thanh toán');
            $table->datetime('expired_at')->nullable()->comment('Ngày hết hạn');
            
            // Additional info
            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->json('payment_details')->nullable()->comment('Chi tiết thanh toán');
            $table->string('reference_number', 50)->nullable()->comment('Số tham chiếu');
            
            // Soft deletes
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('tenant_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['organization_id', 'payment_status']);
            $table->index(['unit_id', 'payment_status']);
            $table->index(['tenant_user_id', 'payment_status']);
            $table->index(['lead_id', 'payment_status']);
            $table->index(['agent_id', 'created_at']);
            $table->index('hold_until');
            $table->index('expired_at');
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_deposits');
    }
};
