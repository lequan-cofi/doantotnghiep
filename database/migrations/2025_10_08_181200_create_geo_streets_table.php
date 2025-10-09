<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('geo_streets')) {
            Schema::create('geo_streets', function (Blueprint $table) {
                $table->string('code', 30)->primary();
                $table->string('ward_code', 20);
                $table->string('name', 150);
                $table->string('name_local', 150)->nullable();
                $table->timestamps();

                $table->foreign('ward_code')
                    ->references('code')->on('geo_wards')
                    ->onDelete('cascade');
                $table->index(['ward_code'], 'idx_gstreet_ward');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_streets');
    }
};


