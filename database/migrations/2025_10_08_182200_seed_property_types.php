<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $types = [
            ['key_code' => 'phongtro', 'name' => 'Phòng trọ', 'name_local' => 'Phòng trọ'],
            ['key_code' => 'chungcumini', 'name' => 'Chung cư mini', 'name_local' => 'Chung cư mini'],
            ['key_code' => 'nhanguyencan', 'name' => 'Nhà nguyên căn', 'name_local' => 'Nhà nguyên căn'],
            ['key_code' => 'matbang', 'name' => 'Mặt bằng', 'name_local' => 'Mặt bằng'],
            ['key_code' => 'chungcu', 'name' => 'Chung cư', 'name_local' => 'Chung cư'],
        ];
        foreach ($types as $t) {
            DB::table('property_types')->updateOrInsert(
                ['key_code' => $t['key_code']],
                ['name' => $t['name'], 'name_local' => $t['name_local']]
            );
        }
    }

    public function down(): void
    {
        DB::table('property_types')->whereIn('key_code', [
            'phongtro','chungcumini','nhanguyencan','matbang','chungcu'
        ])->delete();
    }
};


