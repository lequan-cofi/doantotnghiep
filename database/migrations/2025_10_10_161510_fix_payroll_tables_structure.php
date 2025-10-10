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
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Drop existing tables if they exist (in correct order)
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_payslips');
        Schema::dropIfExists('payroll_cycles');
        Schema::dropIfExists('salary_contracts');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Recreate payroll_cycles table
        Schema::create('payroll_cycles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('period_month', 7); // Format: YYYY-MM
            $table->enum('status', ['open', 'locked', 'paid'])->default('open');
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->index(['organization_id', 'period_month']);
        });

        // Recreate payroll_payslips table
        Schema::create('payroll_payslips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_cycle_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->decimal('deduction_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('payroll_cycle_id')->references('id')->on('payroll_cycles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['payroll_cycle_id', 'user_id']);
        });

        // Recreate payroll_items table
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_cycle_id');
            $table->unsignedBigInteger('user_id');
            $table->string('item_type'); // 'salary', 'commission', 'allowance', 'deduction', etc.
            $table->integer('sign'); // 1 for positive (salary, commission), -1 for negative (deduction)
            $table->decimal('amount', 15, 2);
            $table->string('ref_type')->nullable(); // 'commission_event', 'salary_contract', etc.
            $table->unsignedBigInteger('ref_id')->nullable(); // Reference to the source record
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('payroll_cycle_id')->references('id')->on('payroll_cycles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['payroll_cycle_id', 'user_id']);
        });

        // Recreate salary_contracts table
        Schema::create('salary_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('base_salary', 15, 2);
            $table->string('currency', 3)->default('VND');
            $table->string('pay_cycle')->default('monthly'); // 'monthly', 'weekly', 'daily'
            $table->integer('pay_day')->default(1); // Day of month/week for payment
            $table->json('allowances_json')->nullable(); // JSON array of allowance amounts
            $table->json('kpi_target_json')->nullable(); // JSON object of KPI targets
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['organization_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_contracts');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_payslips');
        Schema::dropIfExists('payroll_cycles');
    }
};
