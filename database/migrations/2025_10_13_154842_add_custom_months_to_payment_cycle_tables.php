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
        // Add custom_months column to organizations table
        Schema::table('organizations', function (Blueprint $table) {
            $table->integer('org_custom_months')->nullable()->after('org_payment_notes')->comment('Số tháng tùy chỉnh cho chu kỳ thanh toán (1-60)');
        });

        // Add custom_months column to properties table
        Schema::table('properties', function (Blueprint $table) {
            $table->integer('prop_custom_months')->nullable()->after('prop_payment_notes')->comment('Số tháng tùy chỉnh cho chu kỳ thanh toán (1-60)');
        });

        // Add custom_months column to leases table
        Schema::table('leases', function (Blueprint $table) {
            $table->integer('lease_custom_months')->nullable()->after('lease_payment_notes')->comment('Số tháng tùy chỉnh cho chu kỳ thanh toán (1-60)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('org_custom_months');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('prop_custom_months');
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('lease_custom_months');
        });
    }
};