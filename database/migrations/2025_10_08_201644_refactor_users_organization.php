<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // --- 1) DROP FK + INDEX + COLUMN organization_id (nếu tồn tại) ---
        if (Schema::hasColumn('users', 'organization_id')) {
            // Tìm tên FK thật trong information_schema (nếu có)
            $fk = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'users'
                  AND COLUMN_NAME = 'organization_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");

            if ($fk && isset($fk->CONSTRAINT_NAME)) {
                // Drop FOREIGN KEY theo đúng tên
                DB::statement("ALTER TABLE `users` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            // Tìm index trên cột organization_id (nếu có)
            $idx = DB::selectOne("
                SELECT INDEX_NAME
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'users'
                  AND COLUMN_NAME = 'organization_id'
                LIMIT 1
            ");
            if ($idx && isset($idx->INDEX_NAME) && $idx->INDEX_NAME !== 'PRIMARY') {
                DB::statement("ALTER TABLE `users` DROP INDEX `{$idx->INDEX_NAME}`");
            }

            // Cuối cùng: drop cột
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }

        // --- 2) Tạo ORG_MAIN nếu chưa có ---
        $org = DB::table('organizations')->where('code', 'ORG_MAIN')->first();
        if (!$org) {
            $orgId = DB::table('organizations')->insertGetId([
                'code'       => 'ORG_MAIN',
                'name'       => 'Tổ chức mặc định',
                'phone'      => '0901000000',
                'email'      => 'info@orgmain.vn',
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $orgId = $org->id;
        }

        // --- 3) Map tất cả users vào organization_users theo user_roles (mỗi role một dòng) ---
        // UNIQUE (organization_id, user_id, role_id) → dùng INSERT IGNORE để tránh trùng
        DB::statement("
            INSERT IGNORE INTO organization_users
                (organization_id, user_id, role_id, status, created_at, updated_at)
            SELECT
                ?, ur.user_id, ur.role_id, 'active', NOW(), NOW()
            FROM user_roles ur
        ", [$orgId]);
    }

    public function down(): void
    {
        // Xoá mapping cho ORG_MAIN (nếu có)
        $org = DB::table('organizations')->where('code', 'ORG_MAIN')->first();
        if ($org) {
            DB::table('organization_users')->where('organization_id', $org->id)->delete();
            // (Không xoá organizations để tránh mất dữ liệu khác)
        }

        // Thêm lại cột organization_id (nullable) để rollback được
        if (!Schema::hasColumn('users', 'organization_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                // tuỳ nhu cầu có thể thêm lại FK:
                // $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            });
        }
    }
};
