<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Country
        DB::table('geo_countries')->updateOrInsert(
            ['code' => 'VN'],
            ['name' => 'Vietnam', 'name_local' => 'Việt Nam']
        );

        // Provinces (sample core cities). Extend as needed
        $provinces = [
            ['code' => 'VN-HN', 'country_code' => 'VN', 'name' => 'Ha Noi', 'name_local' => 'Hà Nội', 'kind' => 'city'],
            ['code' => 'VN-HCM', 'country_code' => 'VN', 'name' => 'Ho Chi Minh City', 'name_local' => 'TP. Hồ Chí Minh', 'kind' => 'city'],
            ['code' => 'VN-DN', 'country_code' => 'VN', 'name' => 'Da Nang', 'name_local' => 'Đà Nẵng', 'kind' => 'city'],
        ];
        foreach ($provinces as $p) {
            DB::table('geo_provinces')->updateOrInsert(
                ['code' => $p['code']],
                ['country_code' => $p['country_code'], 'name' => $p['name'], 'name_local' => $p['name_local'], 'kind' => $p['kind']]
            );
        }

        // Districts (sample)
        $districts = [
            ['code' => 'HN-HK', 'province_code' => 'VN-HN', 'name' => 'Hoan Kiem', 'name_local' => 'Hoàn Kiếm', 'kind' => 'urban_district'],
            ['code' => 'HN-CG', 'province_code' => 'VN-HN', 'name' => 'Cau Giay', 'name_local' => 'Cầu Giấy', 'kind' => 'urban_district'],
            ['code' => 'HCM-Q1', 'province_code' => 'VN-HCM', 'name' => 'District 1', 'name_local' => 'Quận 1', 'kind' => 'urban_district'],
            ['code' => 'DN-HAI', 'province_code' => 'VN-DN', 'name' => 'Hai Chau', 'name_local' => 'Hải Châu', 'kind' => 'urban_district'],
        ];
        foreach ($districts as $d) {
            DB::table('geo_districts')->updateOrInsert(
                ['code' => $d['code']],
                ['province_code' => $d['province_code'], 'name' => $d['name'], 'name_local' => $d['name_local'], 'kind' => $d['kind']]
            );
        }

        // Wards (sample)
        $wards = [
            ['code' => 'HK-TRANGTIEN', 'district_code' => 'HN-HK', 'name' => 'Trang Tien', 'name_local' => 'Tràng Tiền', 'kind' => 'ward'],
            ['code' => 'CG-DICHVONG', 'district_code' => 'HN-CG', 'name' => 'Dich Vong', 'name_local' => 'Dịch Vọng', 'kind' => 'ward'],
            ['code' => 'Q1-BENTHANH', 'district_code' => 'HCM-Q1', 'name' => 'Ben Thanh', 'name_local' => 'Bến Thành', 'kind' => 'ward'],
            ['code' => 'HAI-THACHTHANG', 'district_code' => 'DN-HAI', 'name' => 'Thach Thang', 'name_local' => 'Thạch Thang', 'kind' => 'ward'],
        ];
        foreach ($wards as $w) {
            DB::table('geo_wards')->updateOrInsert(
                ['code' => $w['code']],
                ['district_code' => $w['district_code'], 'name' => $w['name'], 'name_local' => $w['name_local'], 'kind' => $w['kind']]
            );
        }
    }

    public function down(): void
    {
        // Only remove inserted samples to be safe
        DB::table('geo_wards')->whereIn('code', ['HK-TRANGTIEN','CG-DICHVONG','Q1-BENTHANH','HAI-THACHTHANG'])->delete();
        DB::table('geo_districts')->whereIn('code', ['HN-HK','HN-CG','HCM-Q1','DN-HAI'])->delete();
        DB::table('geo_provinces')->whereIn('code', ['VN-HN','VN-HCM','VN-DN'])->delete();
        // Keep VN country
    }
};


