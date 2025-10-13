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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('prop_payment_cycle', 20)->nullable()->after('status')->comment('Chu kỳ thanh toán của bất động sản: monthly, quarterly, yearly');
            $table->integer('prop_payment_day')->nullable()->after('prop_payment_cycle')->comment('Ngày thanh toán trong chu kỳ (1-31)');
            $table->text('prop_payment_notes')->nullable()->after('prop_payment_day')->comment('Ghi chú về chu kỳ thanh toán');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['prop_payment_cycle', 'prop_payment_day', 'prop_payment_notes']);
        });
    }
};
