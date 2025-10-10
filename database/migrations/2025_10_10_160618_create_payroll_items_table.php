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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
