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
        Schema::create('geo_provinces_2025', function (Blueprint $table) {
            $table->string('code', 20)->primary();
            $table->string('country_code', 10);
            $table->string('name', 150);
            $table->string('name_local', 150)->nullable();
            $table->enum('kind', ['province', 'city', 'municipality'])->default('province');
            $table->timestamp('created_at')->nullable()->useCurrent();
            
            $table->index('country_code');
            $table->foreign('country_code')->references('code')->on('geo_countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_provinces_2025');
    }
};
