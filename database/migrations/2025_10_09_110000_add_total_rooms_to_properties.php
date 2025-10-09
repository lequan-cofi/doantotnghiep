<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm trường total_rooms vào bảng properties để tính tỷ lệ lấp đầy
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'total_rooms')) {
                $table->integer('total_rooms')->default(0)->after('total_floors')
                    ->comment('Tổng số phòng trong tòa nhà');
                
                $table->index('total_rooms');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'total_rooms')) {
                $table->dropIndex(['total_rooms']);
                $table->dropColumn('total_rooms');
            }
        });
    }
};
