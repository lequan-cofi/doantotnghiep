<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure country & province Hanoi
        DB::table('geo_countries')->updateOrInsert(
            ['code' => 'VN'],
            ['name' => 'Vietnam', 'name_local' => 'Việt Nam']
        );

        DB::table('geo_provinces')->updateOrInsert(
            ['code' => 'VN-HN'],
            ['country_code' => 'VN', 'name' => 'Ha Noi', 'name_local' => 'Hà Nội', 'kind' => 'city']
        );

        // Districts of Hanoi (selection of main ones; extendable)
        $districts = [
            ['code' => 'HN-BD', 'name' => 'Ba Dinh', 'name_local' => 'Ba Đình'],
            ['code' => 'HN-HK', 'name' => 'Hoan Kiem', 'name_local' => 'Hoàn Kiếm'],
            ['code' => 'HN-CG', 'name' => 'Cau Giay', 'name_local' => 'Cầu Giấy'],
            ['code' => 'HN-DH', 'name' => 'Dong Da', 'name_local' => 'Đống Đa'],
            ['code' => 'HN-TX', 'name' => 'Thanh Xuan', 'name_local' => 'Thanh Xuân'],
            ['code' => 'HN-TB', 'name' => 'Tu Liem', 'name_local' => 'Bắc Từ Liêm'],
            ['code' => 'HN-ND', 'name' => 'Nam Tu Liem', 'name_local' => 'Nam Từ Liêm'],
            ['code' => 'HN-HM', 'name' => 'Hai Ba Trung', 'name_local' => 'Hai Bà Trưng'],
            ['code' => 'HN-HAI', 'name' => 'Hai Chau (DN placeholder)', 'name_local' => ''],
        ];
        foreach ($districts as $d) {
            DB::table('geo_districts')->updateOrInsert(
                ['code' => $d['code']],
                ['province_code' => 'VN-HN', 'name' => $d['name'], 'name_local' => $d['name_local'], 'kind' => 'urban_district']
            );
        }

        // Wards for two key districts: Hoàn Kiếm, Cầu Giấy (full ward list for these)
        $wards = [
            // Hoàn Kiếm
            ['code' => 'HK-CHUONGDUONG', 'district_code' => 'HN-HK', 'name' => 'Chuong Duong', 'name_local' => 'Chương Dương'] ,
            ['code' => 'HK-DONGXUAN', 'district_code' => 'HN-HK', 'name' => 'Dong Xuan', 'name_local' => 'Đồng Xuân'] ,
            ['code' => 'HK-HANGBAC', 'district_code' => 'HN-HK', 'name' => 'Hang Bac', 'name_local' => 'Hàng Bạc'] ,
            ['code' => 'HK-HANGBUOM', 'district_code' => 'HN-HK', 'name' => 'Hang Buom', 'name_local' => 'Hàng Buồm'] ,
            ['code' => 'HK-LYTHAITO', 'district_code' => 'HN-HK', 'name' => 'Ly Thai To', 'name_local' => 'Lý Thái Tổ'] ,
            ['code' => 'HK-TRANPHU', 'district_code' => 'HN-HK', 'name' => 'Tran Phu', 'name_local' => 'Trần Phú'] ,
            ['code' => 'HK-TRANGTIEN', 'district_code' => 'HN-HK', 'name' => 'Trang Tien', 'name_local' => 'Tràng Tiền'] ,
            ['code' => 'HK-HANGBAI', 'district_code' => 'HN-HK', 'name' => 'Hang Bai', 'name_local' => 'Hàng Bài'] ,
            ['code' => 'HK-TRUNGLYET', 'district_code' => 'HN-HK', 'name' => 'Trung Liet', 'name_local' => 'Trưng Liệt'] ,
            // Cầu Giấy
            ['code' => 'CG-DICHVONG', 'district_code' => 'HN-CG', 'name' => 'Dich Vong', 'name_local' => 'Dịch Vọng'] ,
            ['code' => 'CG-DICHVONGHAU', 'district_code' => 'HN-CG', 'name' => 'Dich Vong Hau', 'name_local' => 'Dịch Vọng Hậu'] ,
            ['code' => 'CG-MAIDICH', 'district_code' => 'HN-CG', 'name' => 'Mai Dich', 'name_local' => 'Mai Dịch'] ,
            ['code' => 'CG-NGHIAO', 'district_code' => 'HN-CG', 'name' => 'Nghia Do', 'name_local' => 'Nghĩa Đô'] ,
            ['code' => 'CG-NGHITAN', 'district_code' => 'HN-CG', 'name' => 'Nghia Tan', 'name_local' => 'Nghĩa Tân'] ,
            ['code' => 'CG-QUANCHE', 'district_code' => 'HN-CG', 'name' => 'Quan Hoa', 'name_local' => 'Quan Hoa'] ,
            ['code' => 'CG-TRUNGHUA', 'district_code' => 'HN-CG', 'name' => 'Trung Hoa', 'name_local' => 'Trung Hòa'] ,
            ['code' => 'CG-YENHOA', 'district_code' => 'HN-CG', 'name' => 'Yen Hoa', 'name_local' => 'Yên Hòa'] ,
        ];
        foreach ($wards as $w) {
            DB::table('geo_wards')->updateOrInsert(
                ['code' => $w['code']],
                ['district_code' => $w['district_code'], 'name' => $w['name'], 'name_local' => $w['name_local'], 'kind' => 'ward']
            );
        }

        // Streets for selected wards (sample, expandable)
        $streets = [
            ['code' => 'HK-TRANGTIEN-01', 'ward_code' => 'HK-TRANGTIEN', 'name' => 'Trang Tien', 'name_local' => 'Tràng Tiền'],
            ['code' => 'HK-HANGBAI-01', 'ward_code' => 'HK-HANGBAI', 'name' => 'Hang Bai', 'name_local' => 'Hàng Bài'],
            ['code' => 'CG-TRUNGHUA-01', 'ward_code' => 'CG-TRUNGHUA', 'name' => 'Trung Hoa', 'name_local' => 'Trung Hòa'],
            ['code' => 'CG-YENHOA-01', 'ward_code' => 'CG-YENHOA', 'name' => 'Yen Hoa', 'name_local' => 'Yên Hòa'],
        ];
        foreach ($streets as $s) {
            DB::table('geo_streets')->updateOrInsert(
                ['code' => $s['code']],
                ['ward_code' => $s['ward_code'], 'name' => $s['name'], 'name_local' => $s['name_local']]
            );
        }
    }

    public function down(): void
    {
        DB::table('geo_streets')->whereIn('code', ['HK-TRANGTIEN-01','HK-HANGBAI-01','CG-TRUNGHUA-01','CG-YENHOA-01'])->delete();
        DB::table('geo_wards')->whereIn('code', [
            'HK-CHUONGDUONG','HK-DONGXUAN','HK-HANGBAC','HK-HANGBUOM','HK-LYTHAITO','HK-TRANPHU','HK-TRANGTIEN','HK-HANGBAI','HK-TRUNGLYET',
            'CG-DICHVONG','CG-DICHVONGHAU','CG-MAIDICH','CG-NGHIAO','CG-NGHITAN','CG-QUANCHE','CG-TRUNGHUA','CG-YENHOA',
        ])->delete();
        DB::table('geo_districts')->whereIn('code', ['HN-BD','HN-HK','HN-CG','HN-DH','HN-TX','HN-TB','HN-ND','HN-HM','HN-HAI'])->delete();
        // Keep province & country
    }
};


