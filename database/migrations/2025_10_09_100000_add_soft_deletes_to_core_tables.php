<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm soft deletes (deleted_at) cho các bảng quan trọng
     */
    public function up(): void
    {
        // 1. Users - Người dùng
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 2. Organizations - Tổ chức
        if (!Schema::hasColumn('organizations', 'deleted_at')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 3. Properties - Bất động sản
        if (!Schema::hasColumn('properties', 'deleted_at')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 4. Units - Phòng/căn
        if (!Schema::hasColumn('units', 'deleted_at')) {
            Schema::table('units', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 5. Listings - Tin đăng
        if (!Schema::hasColumn('listings', 'deleted_at')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 6. Leases - Hợp đồng thuê
        if (!Schema::hasColumn('leases', 'deleted_at')) {
            Schema::table('leases', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 7. Invoices - Hóa đơn
        if (!Schema::hasColumn('invoices', 'deleted_at')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 8. Payments - Thanh toán
        if (!Schema::hasColumn('payments', 'deleted_at')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->softDeletes()->after('created_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 9. Tickets - Ticket bảo trì
        if (!Schema::hasColumn('tickets', 'deleted_at')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 10. Leads - Khách hàng tiềm năng
        if (!Schema::hasColumn('leads', 'deleted_at')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 11. Viewings - Lịch xem phòng
        if (!Schema::hasColumn('viewings', 'deleted_at')) {
            Schema::table('viewings', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                
                $table->foreign('deleted_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }

        // 12. Booking Deposits - Đặt cọc
        if (!Schema::hasColumn('booking_deposits', 'deleted_at')) {
            Schema::table('booking_deposits', function (Blueprint $table) {
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
            'users',
            'organizations',
            'properties',
            'units',
            'listings',
            'leases',
            'invoices',
            'payments',
            'tickets',
            'leads',
            'viewings',
            'booking_deposits'
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

