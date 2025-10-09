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
        Schema::create('geo_wards_2025', function (Blueprint $table) {
            $table->string('code', 20)->primary();
            $table->string('province_code', 20);
            $table->string('name', 150);
            $table->string('name_local', 150)->nullable();
            $table->enum('kind', ['ward', 'commune', 'townlet'])->default('ward');
            $table->timestamp('created_at')->nullable()->useCurrent();
            
            $table->index('province_code');
            $table->foreign('province_code')->references('code')->on('geo_provinces_2025')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_wards_2025');
    }
};
