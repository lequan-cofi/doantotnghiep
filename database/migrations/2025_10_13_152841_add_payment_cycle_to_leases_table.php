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
        Schema::table('leases', function (Blueprint $table) {
            $table->string('lease_payment_cycle', 20)->nullable()->after('billing_day')->comment('Chu kỳ thanh toán của hợp đồng: monthly, quarterly, yearly');
            $table->integer('lease_payment_day')->nullable()->after('lease_payment_cycle')->comment('Ngày thanh toán trong chu kỳ (1-31)');
            $table->text('lease_payment_notes')->nullable()->after('lease_payment_day')->comment('Ghi chú về chu kỳ thanh toán');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn(['lease_payment_cycle', 'lease_payment_day', 'lease_payment_notes']);
        });
    }
};
