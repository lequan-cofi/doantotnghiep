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
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('org_payment_cycle', 20)->nullable()->after('status')->comment('Chu kỳ thanh toán của tổ chức: monthly, quarterly, yearly');
            $table->integer('org_payment_day')->nullable()->after('org_payment_cycle')->comment('Ngày thanh toán trong chu kỳ (1-31)');
            $table->text('org_payment_notes')->nullable()->after('org_payment_day')->comment('Ghi chú về chu kỳ thanh toán');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['org_payment_cycle', 'org_payment_day', 'org_payment_notes']);
        });
    }
};
