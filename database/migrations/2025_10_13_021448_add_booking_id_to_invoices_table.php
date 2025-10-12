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
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_deposit_id')->nullable()->after('lease_id');
            $table->foreign('booking_deposit_id')->references('id')->on('booking_deposits')->onDelete('cascade');
            $table->index('booking_deposit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['booking_deposit_id']);
            $table->dropIndex(['booking_deposit_id']);
            $table->dropColumn('booking_deposit_id');
        });
    }
};
