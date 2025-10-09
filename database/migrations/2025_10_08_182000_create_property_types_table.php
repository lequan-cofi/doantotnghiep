<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('property_types')) {
            Schema::create('property_types', function (Blueprint $table) {
                $table->id();
                $table->string('key_code', 50)->unique();
                $table->string('name', 150);
                $table->string('name_local', 150)->nullable();
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('property_types');
    }
};


