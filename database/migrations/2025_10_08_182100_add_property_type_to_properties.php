<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'property_type_id')) {
                $table->unsignedBigInteger('property_type_id')->nullable()->after('owner_id');
                $table->foreign('property_type_id')
                    ->references('id')->on('property_types')
                    ->nullOnDelete();
                $table->index('property_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'property_type_id')) {
                $table->dropForeign(['property_type_id']);
                $table->dropIndex(['property_type_id']);
                $table->dropColumn('property_type_id');
            }
        });
    }
};


