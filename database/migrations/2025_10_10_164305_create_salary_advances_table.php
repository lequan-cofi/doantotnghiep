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
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('VND');
            $table->date('advance_date');
            $table->date('expected_repayment_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'repaid', 'partially_repaid'])->default('pending');
            $table->decimal('repaid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2);
            $table->enum('repayment_method', ['payroll_deduction', 'direct_payment', 'installment'])->default('payroll_deduction');
            $table->integer('installment_months')->nullable(); // For installment method
            $table->decimal('monthly_deduction', 15, 2)->nullable(); // For payroll deduction method
            $table->text('note')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['organization_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['advance_date']);
            $table->index(['expected_repayment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
    }
};