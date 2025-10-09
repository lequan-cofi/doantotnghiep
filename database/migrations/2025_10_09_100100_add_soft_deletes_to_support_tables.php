<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm soft deletes cho các bảng phụ trợ
     */
    public function up(): void
    {
        // 1. Property Types - Loại bất động sản
        if (!Schema::hasColumn('property_types', 'deleted_at')) {
            Schema::table('property_types', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 2. Amenities - Tiện ích
        if (!Schema::hasColumn('amenities', 'deleted_at')) {
            Schema::table('amenities', function (Blueprint $table) {
                $table->timestamps();
                $table->softDeletes();
                $table->unsignedBigInteger('deleted_by')->nullable();
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 3. Services - Dịch vụ
        if (!Schema::hasColumn('services', 'deleted_at')) {
            Schema::table('services', function (Blueprint $table) {
                $table->timestamps();
                $table->softDeletes();
                $table->unsignedBigInteger('deleted_by')->nullable();
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 4. Meters - Đồng hồ
        if (!Schema::hasColumn('meters', 'deleted_at')) {
            Schema::table('meters', function (Blueprint $table) {
                $table->timestamps();
                $table->softDeletes();
                $table->unsignedBigInteger('deleted_by')->nullable();
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 5. Documents - Tài liệu
        if (!Schema::hasColumn('documents', 'deleted_at')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->softDeletes()->after('created_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 6. Salary Contracts - Hợp đồng lương
        if (!Schema::hasColumn('salary_contracts', 'deleted_at')) {
            Schema::table('salary_contracts', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 7. Commission Policies - Chính sách hoa hồng
        if (!Schema::hasColumn('commission_policies', 'deleted_at')) {
            Schema::table('commission_policies', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 8. Commission Events - Sự kiện hoa hồng
        if (!Schema::hasColumn('commission_events', 'deleted_at')) {
            Schema::table('commission_events', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate()->after('created_at');
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 9. Locations - Địa chỉ
        if (!Schema::hasColumn('locations', 'deleted_at')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'property_types',
            'amenities',
            'services',
            'meters',
            'documents',
            'salary_contracts',
            'commission_policies',
            'commission_events',
            'locations'
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    // Drop foreign key for deleted_by if exists
                    if (Schema::hasColumn($table, 'deleted_by')) {
                        $blueprint->dropForeign([$table . '_deleted_by_foreign']);
                        $blueprint->dropColumn('deleted_by');
                    }
                    $blueprint->dropSoftDeletes();
                });
            }
        }
    }
};

