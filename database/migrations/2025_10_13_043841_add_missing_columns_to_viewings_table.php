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
        Schema::table('viewings', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('organization_id');
            $table->string('lead_name')->nullable()->after('unit_id');
            $table->string('lead_phone')->nullable()->after('lead_name');
            $table->string('lead_email')->nullable()->after('lead_phone');
            $table->text('note')->nullable()->after('result_note');
            
            // Foreign keys
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            
            // Indexes
            $table->index('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viewings', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id']);
            $table->dropColumn(['unit_id', 'lead_name', 'lead_phone', 'lead_email', 'note']);
        });
    }
};
