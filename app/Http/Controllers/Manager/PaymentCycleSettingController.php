<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentCycleSettingController extends Controller
{
    /**
     * Display payment cycle settings index
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get user's organization
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return redirect()->route('manager.dashboard')
                ->with('error', 'Bạn chưa được gán vào tổ chức nào.');
        }

        // Get properties in organization
        $properties = Property::where('organization_id', $organization->id)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        // Get payment cycle options
        $paymentCycleOptions = [
            'monthly' => 'Hàng tháng',
            'quarterly' => 'Hàng quý',
            'yearly' => 'Hàng năm',
            'custom' => 'Tùy chỉnh (nhập số tháng)'
        ];

        return view('manager.payment-cycle-settings.index', [
            'organization' => $organization,
            'properties' => $properties,
            'paymentCycleOptions' => $paymentCycleOptions
        ]);
    }

    /**
     * Update organization payment cycle settings
     */
    public function updateOrganization(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get user's organization
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return back()->with('error', 'Bạn chưa được gán vào tổ chức nào.');
        }

        $request->validate([
            'org_payment_cycle' => 'nullable|in:monthly,quarterly,yearly,custom',
            'org_payment_day' => 'nullable|integer|min:1|max:31',
            'org_payment_notes' => 'nullable|string|max:1000',
            'org_custom_months' => 'nullable|integer|min:1|max:60',
        ], [
            'org_payment_cycle.in' => 'Chu kỳ thanh toán không hợp lệ.',
            'org_payment_day.integer' => 'Ngày thanh toán phải là số nguyên.',
            'org_payment_day.min' => 'Ngày thanh toán phải từ 1 đến 31.',
            'org_payment_day.max' => 'Ngày thanh toán phải từ 1 đến 31.',
            'org_payment_notes.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
            'org_custom_months.integer' => 'Số tháng tùy chỉnh phải là số nguyên.',
            'org_custom_months.min' => 'Số tháng tùy chỉnh phải từ 1 đến 60.',
            'org_custom_months.max' => 'Số tháng tùy chỉnh phải từ 1 đến 60.',
        ]);

        try {
            $organization->update([
                'org_payment_cycle' => $request->org_payment_cycle,
                'org_payment_day' => $request->org_payment_day,
                'org_payment_notes' => $request->org_payment_notes,
                'org_custom_months' => $request->org_custom_months,
            ]);

            Log::info('Organization payment cycle settings updated', [
                'organization_id' => $organization->id,
                'updated_by' => $user->id,
                'settings' => $request->only(['org_payment_cycle', 'org_payment_day', 'org_payment_notes'])
            ]);

            return back()->with('success', 'Đã cập nhật cài đặt chu kỳ thanh toán tổ chức thành công!');

        } catch (\Exception $e) {
            Log::error('Error updating organization payment cycle settings: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật cài đặt: ' . $e->getMessage());
        }
    }

    /**
     * Update property payment cycle settings
     */
    public function updateProperty(Request $request, $propertyId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get user's organization
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return back()->with('error', 'Bạn chưa được gán vào tổ chức nào.');
        }

        // Get property
        $property = Property::where('organization_id', $organization->id)
            ->where('id', $propertyId)
            ->firstOrFail();

        $request->validate([
            'prop_payment_cycle' => 'nullable|in:monthly,quarterly,yearly,custom',
            'prop_payment_day' => 'nullable|integer|min:1|max:31',
            'prop_payment_notes' => 'nullable|string|max:1000',
            'prop_custom_months' => 'nullable|integer|min:1|max:60',
        ], [
            'prop_payment_cycle.in' => 'Chu kỳ thanh toán không hợp lệ.',
            'prop_payment_day.integer' => 'Ngày thanh toán phải là số nguyên.',
            'prop_payment_day.min' => 'Ngày thanh toán phải từ 1 đến 31.',
            'prop_payment_day.max' => 'Ngày thanh toán phải từ 1 đến 31.',
            'prop_payment_notes.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
            'prop_custom_months.integer' => 'Số tháng tùy chỉnh phải là số nguyên.',
            'prop_custom_months.min' => 'Số tháng tùy chỉnh phải từ 1 đến 60.',
            'prop_custom_months.max' => 'Số tháng tùy chỉnh phải từ 1 đến 60.',
        ]);

        try {
            $property->update([
                'prop_payment_cycle' => $request->prop_payment_cycle,
                'prop_payment_day' => $request->prop_payment_day,
                'prop_payment_notes' => $request->prop_payment_notes,
                'prop_custom_months' => $request->prop_custom_months,
            ]);

            Log::info('Property payment cycle settings updated', [
                'property_id' => $property->id,
                'organization_id' => $organization->id,
                'updated_by' => $user->id,
                'settings' => $request->only(['prop_payment_cycle', 'prop_payment_day', 'prop_payment_notes'])
            ]);

            return back()->with('success', 'Đã cập nhật cài đặt chu kỳ thanh toán bất động sản thành công!');

        } catch (\Exception $e) {
            Log::error('Error updating property payment cycle settings: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật cài đặt: ' . $e->getMessage());
        }
    }

    /**
     * Update lease payment cycle settings
     */
    public function updateLease(Request $request, $leaseId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get user's organization
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return back()->with('error', 'Bạn chưa được gán vào tổ chức nào.');
        }

        // Get lease
        $lease = Lease::where('organization_id', $organization->id)
            ->where('id', $leaseId)
            ->firstOrFail();

        $request->validate([
            'lease_payment_cycle' => 'nullable|in:monthly,quarterly,yearly,custom',
            'lease_payment_day' => 'nullable|integer|min:1|max:31',
            'lease_payment_notes' => 'nullable|string|max:1000',
            'lease_custom_months' => 'nullable|integer|min:1|max:60',
        ], [
            'lease_payment_cycle.in' => 'Chu kỳ thanh toán không hợp lệ.',
            'lease_payment_day.integer' => 'Ngày thanh toán phải là số nguyên.',
            'lease_payment_day.min' => 'Ngày thanh toán phải từ 1 đến 31.',
            'lease_payment_day.max' => 'Ngày thanh toán phải từ 1 đến 31.',
            'lease_payment_notes.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
            'lease_custom_months.integer' => 'Số tháng tùy chỉnh phải là số nguyên.',
            'lease_custom_months.min' => 'Số tháng tùy chỉnh phải từ 1 đến 60.',
            'lease_custom_months.max' => 'Số tháng tùy chỉnh phải từ 1 đến 60.',
        ]);

        try {
            $lease->update([
                'lease_payment_cycle' => $request->lease_payment_cycle,
                'lease_payment_day' => $request->lease_payment_day,
                'lease_payment_notes' => $request->lease_payment_notes,
                'lease_custom_months' => $request->lease_custom_months,
            ]);

            Log::info('Lease payment cycle settings updated', [
                'lease_id' => $lease->id,
                'organization_id' => $organization->id,
                'updated_by' => $user->id,
                'settings' => $request->only(['lease_payment_cycle', 'lease_payment_day', 'lease_payment_notes'])
            ]);

            return back()->with('success', 'Đã cập nhật cài đặt chu kỳ thanh toán hợp đồng thành công!');

        } catch (\Exception $e) {
            Log::error('Error updating lease payment cycle settings: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật cài đặt: ' . $e->getMessage());
        }
    }

    /**
     * Get leases for a property
     */
    public function getPropertyLeases($propertyId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get user's organization
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json(['error' => 'Bạn chưa được gán vào tổ chức nào.'], 403);
        }

        // Get property
        $property = Property::where('organization_id', $organization->id)
            ->where('id', $propertyId)
            ->firstOrFail();

        // Get leases for this property
        $leases = Lease::where('organization_id', $organization->id)
            ->whereHas('unit', function($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            })
            ->with(['unit', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($lease) {
                return [
                    'id' => $lease->id,
                    'contract_no' => $lease->contract_no,
                    'unit_code' => $lease->unit->code ?? 'N/A',
                    'tenant_name' => $lease->tenant->full_name ?? 'N/A',
                    'status' => $lease->status,
                    'lease_payment_cycle' => $lease->lease_payment_cycle,
                    'lease_payment_day' => $lease->lease_payment_day,
                    'lease_payment_notes' => $lease->lease_payment_notes,
                    'lease_custom_months' => $lease->lease_custom_months,
                    'created_at' => $lease->created_at->format('d/m/Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'property' => [
                'id' => $property->id,
                'name' => $property->name,
                'prop_payment_cycle' => $property->prop_payment_cycle,
                'prop_payment_day' => $property->prop_payment_day,
                'prop_payment_notes' => $property->prop_payment_notes,
                'prop_custom_months' => $property->prop_custom_months,
            ],
            'leases' => $leases
        ]);
    }

    /**
     * Apply organization settings to all properties
     */
    public function applyToProperties(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get user's organization
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return back()->with('error', 'Bạn chưa được gán vào tổ chức nào.');
        }

        $request->validate([
            'apply_to_properties' => 'required|boolean',
        ]);

        if (!$request->apply_to_properties) {
            return back()->with('warning', 'Vui lòng xác nhận áp dụng cài đặt cho tất cả bất động sản.');
        }

        try {
            DB::beginTransaction();

            // Update all properties in organization
            $updatedCount = Property::where('organization_id', $organization->id)
                ->update([
                    'prop_payment_cycle' => $organization->org_payment_cycle,
                    'prop_payment_day' => $organization->org_payment_day,
                    'prop_payment_notes' => $organization->org_payment_notes,
                    'prop_custom_months' => $organization->org_custom_months,
                ]);

            Log::info('Organization payment cycle settings applied to properties', [
                'organization_id' => $organization->id,
                'updated_by' => $user->id,
                'updated_properties_count' => $updatedCount,
                'settings' => [
                    'org_payment_cycle' => $organization->org_payment_cycle,
                    'org_payment_day' => $organization->org_payment_day,
                    'org_payment_notes' => $organization->org_payment_notes,
                ]
            ]);

            DB::commit();

            return back()->with('success', "Đã áp dụng cài đặt chu kỳ thanh toán cho {$updatedCount} bất động sản thành công!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying organization settings to properties: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi áp dụng cài đặt: ' . $e->getMessage());
        }
    }

}