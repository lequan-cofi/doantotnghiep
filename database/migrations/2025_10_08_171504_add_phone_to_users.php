<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_phone_to_users.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable();
            // nếu muốn passwordless, cho phép password null
            // $table->string('password')->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_verified_at']);
        });
    }
};
