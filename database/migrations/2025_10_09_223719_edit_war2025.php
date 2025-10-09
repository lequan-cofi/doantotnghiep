<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('geo_wards_2025', function (Blueprint $table) {
            // 1. Drop foreign key cũ nếu có
            $table->dropForeign(['district_code']);
        });

        Schema::table('geo_wards_2025', function (Blueprint $table) {
            // 2. Rename column
            $table->renameColumn('district_code', 'province_code');
        });

        Schema::table('geo_wards_2025', function (Blueprint $table) {
            // 3. Cột mới nếu chưa đúng kiểu, đảm bảo nullable hoặc không tuỳ bạn
            $table->string('province_code', 20)->nullable()->change();
        });

        // 4. Cập nhật dữ liệu: đổi mã cũ → VN-HN
        DB::table('geo_wards_2025')
          ->whereNotNull('province_code')  // nếu bạn muốn chỉ những có cũ
          ->update(['province_code' => 'VN-HN']);

        Schema::table('geo_wards_2025', function (Blueprint $table) {
            // 5. Thêm lại khóa ngoại mới: province_code → bảng provinces (2025 hoặc hiện tại)
            $table->foreign('province_code')
                  ->references('code')
                  ->on('geo_provinces_2025')  // hoặc 'geo_provinces'
                  ->onDelete('cascade');      // hoặc SET NULL tuỳ ý
        });
    }

    public function down(): void
    {
        Schema::table('geo_wards_2025', function (Blueprint $table) {
            // gỡ khóa ngoại mới
            $table->dropForeign(['province_code']);
        });

        // nếu cần, đổi lại tên cột
        Schema::table('geo_wards_2025', function (Blueprint $table) {
            $table->renameColumn('province_code', 'district_code');
        });

        Schema::table('geo_wards_2025', function (Blueprint $table) {
            $table->string('district_code', 20)->nullable()->change();
        });

        Schema::table('geo_wards_2025', function (Blueprint $table) {
            // nếu muốn khôi phục FK cũ (nếu có bảng geo_districts)
            $table->foreign('district_code')
                  ->references('code')
                  ->on('geo_districts')
                  ->onDelete('cascade');
        });
    }
};
