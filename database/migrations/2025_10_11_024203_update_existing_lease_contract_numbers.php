<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cập nhật mã hợp đồng cho các hợp đồng hiện tại chưa có mã hoặc có mã không đúng format
        $leases = DB::table('leases')
            ->where(function($query) {
                $query->whereNull('contract_no')
                      ->orWhere('contract_no', '')
                      ->orWhere('contract_no', 'not like', 'HD%');
            })
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'asc')
            ->get();

        $contractNumber = 1;
        
        foreach ($leases as $lease) {
            $newContractNo = 'HD' . str_pad($contractNumber, 6, '0', STR_PAD_LEFT);
            
            // Kiểm tra xem mã hợp đồng đã tồn tại chưa
            while (DB::table('leases')->where('contract_no', $newContractNo)->exists()) {
                $contractNumber++;
                $newContractNo = 'HD' . str_pad($contractNumber, 6, '0', STR_PAD_LEFT);
            }
            
            DB::table('leases')
                ->where('id', $lease->id)
                ->update(['contract_no' => $newContractNo]);
            
            $contractNumber++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không thể reverse migration này vì không biết mã hợp đồng gốc
        // Chỉ có thể xóa mã hợp đồng đã được cập nhật
        DB::table('leases')
            ->where('contract_no', 'like', 'HD%')
            ->update(['contract_no' => null]);
    }
};
