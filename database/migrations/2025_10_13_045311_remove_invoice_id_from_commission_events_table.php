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
        Schema::table('commission_events', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropIndex(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_events', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable()->after('commission_total');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->index('invoice_id');
        });
    }
};
