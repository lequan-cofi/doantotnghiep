<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Unit;
use App\Models\User;
use App\Models\Service;
use App\Models\CommissionPolicy;
use App\Models\CommissionEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaseController extends Controller
{
    /**
     * Display a listing of leases created by the agent.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Query leases created by this agent
        $query = Lease::where('agent_id', $user->id)
            ->with(['unit.property', 'tenant', 'unit']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contract_no', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function($tenantQuery) use ($search) {
                      $tenantQuery->where('full_name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%")
                                 ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('unit.property', function($propertyQuery) use ($search) {
                      $propertyQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by property
        if ($request->filled('property_id')) {
            $query->whereHas('unit', function($q) use ($request) {
                $q->where('property_id', $request->property_id);
            });
        }

        // Get leases with sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort fields
        $allowedSortFields = ['id', 'created_at', 'contract_no', 'start_date', 'end_date', 'rent_amount', 'status'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }
        
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        $leases = $query->orderBy($sortBy, $sortOrder)->get();

        // Get assigned properties for filter
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        $properties = \App\Models\Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return view('agent.leases.index', [
            'leases' => $leases,
            'properties' => $properties,
            'selectedProperty' => $request->property_id,
            'selectedStatus' => $request->status,
            'search' => $request->search
        ]);
    }

    /**
     * Show the form for creating a new lease.
     */
    public function create(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            return redirect()->route('agent.leases.index')
                ->with('error', 'Bạn chưa được gán quản lý bất động sản nào.');
        }

        $properties = \App\Models\Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        // Get tenants
        $tenants = User::whereHas('userRoles', function($q) {
            $q->where('key_code', 'tenant');
        })->get();

        // Get services
        $services = Service::all();

        // Pre-select property if provided
        $selectedProperty = null;
        if ($request->filled('property_id')) {
            $selectedProperty = $properties->find($request->property_id);
        }

        return view('agent.leases.create', [
            'properties' => $properties,
            'tenants' => $tenants,
            'services' => $services,
            'selectedProperty' => $selectedProperty
        ]);
    }

    /**
     * Store a newly created lease in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Clean and validate currency inputs
        $rentAmount = str_replace(['.', ','], '', $request->rent_amount);
        $depositAmount = $request->deposit_amount ? str_replace(['.', ','], '', $request->deposit_amount) : null;
        
        // Validate request
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'tenant_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'rent_amount' => 'required|string|regex:/^[\d.,]+$/',
            'deposit_amount' => 'nullable|string|regex:/^[\d.,]+$/',
            'billing_day' => 'nullable|integer|min:1|max:28',
            'status' => 'required|in:draft,active,terminated,expired',
            'contract_no' => 'nullable|string|max:100|unique:leases,contract_no',
            'signed_at' => 'nullable|date',
            'services' => 'nullable|array',
            'services.*.service_id' => 'required_with:services|exists:services,id',
            'services.*.price' => 'required_with:services|numeric|min:0',
        ], [
            'unit_id.required' => 'Vui lòng chọn phòng.',
            'unit_id.exists' => 'Phòng không tồn tại.',
            'tenant_id.required' => 'Vui lòng chọn khách thuê.',
            'tenant_id.exists' => 'Khách thuê không tồn tại.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'rent_amount.required' => 'Vui lòng nhập giá thuê.',
            'rent_amount.regex' => 'Giá thuê không hợp lệ.',
            'deposit_amount.regex' => 'Tiền cọc không hợp lệ.',
            'billing_day.integer' => 'Ngày thanh toán phải là số nguyên.',
            'billing_day.min' => 'Ngày thanh toán phải từ 1 đến 28.',
            'billing_day.max' => 'Ngày thanh toán phải từ 1 đến 28.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'contract_no.unique' => 'Mã hợp đồng đã tồn tại.',
            'signed_at.date' => 'Ngày ký không hợp lệ.',
            'services.array' => 'Dịch vụ phải là mảng.',
            'services.*.service_id.required_with' => 'Vui lòng chọn dịch vụ.',
            'services.*.service_id.exists' => 'Dịch vụ không tồn tại.',
            'services.*.price.required_with' => 'Vui lòng nhập giá dịch vụ.',
            'services.*.price.numeric' => 'Giá dịch vụ phải là số.',
            'services.*.price.min' => 'Giá dịch vụ phải lớn hơn 0.'
        ]);

        // Check if unit belongs to assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        $unit = Unit::with('property')->findOrFail($request->unit_id);
        
        if (!$assignedPropertyIds->contains($unit->property_id)) {
            return back()->withErrors(['unit_id' => 'Bạn không có quyền tạo hợp đồng cho phòng này.']);
        }

        // Check if unit already has active lease
        $hasActiveLease = Lease::where('unit_id', $request->unit_id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->exists();

        if ($hasActiveLease) {
            return back()->withErrors(['unit_id' => 'Phòng này đã có hợp đồng hoạt động.']);
        }

        try {
            DB::beginTransaction();

            // Get organization from current user
            $organization = $user->organizations()->first();
            
            if (!$organization) {
                return back()->withInput()->with('error', 'Bạn chưa được gán vào tổ chức nào.');
            }

            // Auto-generate contract number if not provided
            $contractNo = $request->contract_no;
            if (empty($contractNo)) {
                $contractNo = $this->generateContractNumber();
            }

            // Create lease
            $lease = Lease::create([
                'organization_id' => $organization?->id,
                'unit_id' => $request->unit_id,
                'tenant_id' => $request->tenant_id,
                'agent_id' => $user->id, // Auto-assign current agent
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'rent_amount' => $rentAmount,
                'deposit_amount' => $depositAmount ?? 0,
                'billing_day' => $request->billing_day ?? 1,
                'status' => $request->status,
                'contract_no' => $contractNo,
                'signed_at' => $request->signed_at,
            ]);

            // Add services if provided
            if (!empty($request->services)) {
                foreach ($request->services as $serviceData) {
                    $lease->leaseServices()->create([
                        'service_id' => $serviceData['service_id'],
                        'price' => $serviceData['price'],
                    ]);
                }
            }

            // Update unit status if lease is active
            if ($request->status === 'active') {
                $unit->update(['status' => 'occupied']);
            }

            // Create commission events if lease is active
            if ($request->status === 'active') {
                try {
                    $this->createCommissionEvents($lease, $organization);
                } catch (\Exception $e) {
                    Log::error('Error creating commission events: ' . $e->getMessage());
                    // Don't fail the lease creation if commission events fail
                }
            }

            DB::commit();

            return redirect()->route('agent.leases.show', $lease->id)
                ->with('success', 'Hợp đồng đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating lease: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo hợp đồng: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified lease.
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get lease created by this agent
        $lease = Lease::where('agent_id', $user->id)
            ->with([
                'unit.property.propertyType',
                'unit.property.location',
                'unit.property.location2025',
                'tenant',
                'organization',
                'leaseServices.service',
                'residents'
            ])
            ->findOrFail($id);

        return view('agent.leases.show', compact('lease'));
    }

    /**
     * Show the form for editing the specified lease.
     */
    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get lease created by this agent
        $lease = Lease::where('agent_id', $user->id)
            ->with(['unit.property', 'leaseServices.service'])
            ->findOrFail($id);

        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        $properties = \App\Models\Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        // Get tenants
        $tenants = User::whereHas('userRoles', function($q) {
            $q->where('key_code', 'tenant');
        })->get();

        // Get services
        $services = Service::all();

        // Get units for selected property
        $units = Unit::where('property_id', $lease->unit->property_id)->get();

        return view('agent.leases.edit', [
            'lease' => $lease,
            'properties' => $properties,
            'tenants' => $tenants,
            'services' => $services,
            'units' => $units
        ]);
    }

    /**
     * Update the specified lease in storage.
     */
    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get lease created by this agent
        $lease = Lease::where('agent_id', $user->id)->findOrFail($id);

        // Clean and validate currency inputs
        $rentAmount = str_replace(['.', ','], '', $request->rent_amount);
        $depositAmount = $request->deposit_amount ? str_replace(['.', ','], '', $request->deposit_amount) : null;

        // Validate request
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'tenant_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'rent_amount' => 'required|string|regex:/^[\d.,]+$/',
            'deposit_amount' => 'nullable|string|regex:/^[\d.,]+$/',
            'billing_day' => 'nullable|integer|min:1|max:28',
            'status' => 'required|in:draft,active,terminated,expired',
            'contract_no' => 'nullable|string|max:100|unique:leases,contract_no,' . $id,
            'signed_at' => 'nullable|date',
            'services' => 'nullable|array',
            'services.*.service_id' => 'required_with:services|exists:services,id',
            'services.*.price' => 'required_with:services|numeric|min:0',
        ], [
            'unit_id.required' => 'Vui lòng chọn phòng.',
            'unit_id.exists' => 'Phòng không tồn tại.',
            'tenant_id.required' => 'Vui lòng chọn khách thuê.',
            'tenant_id.exists' => 'Khách thuê không tồn tại.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'rent_amount.required' => 'Vui lòng nhập giá thuê.',
            'rent_amount.regex' => 'Giá thuê không hợp lệ.',
            'deposit_amount.regex' => 'Tiền cọc không hợp lệ.',
            'billing_day.integer' => 'Ngày thanh toán phải là số nguyên.',
            'billing_day.min' => 'Ngày thanh toán phải từ 1 đến 28.',
            'billing_day.max' => 'Ngày thanh toán phải từ 1 đến 28.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'contract_no.unique' => 'Mã hợp đồng đã tồn tại.',
            'signed_at.date' => 'Ngày ký không hợp lệ.',
            'services.array' => 'Dịch vụ phải là mảng.',
            'services.*.service_id.required_with' => 'Vui lòng chọn dịch vụ.',
            'services.*.service_id.exists' => 'Dịch vụ không tồn tại.',
            'services.*.price.required_with' => 'Vui lòng nhập giá dịch vụ.',
            'services.*.price.numeric' => 'Giá dịch vụ phải là số.',
            'services.*.price.min' => 'Giá dịch vụ phải lớn hơn 0.'
        ]);

        // Check if new unit belongs to assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        $unit = Unit::with('property')->findOrFail($request->unit_id);
        
        if (!$assignedPropertyIds->contains($unit->property_id)) {
            return back()->withErrors(['unit_id' => 'Bạn không có quyền chỉnh sửa hợp đồng cho phòng này.']);
        }

        // Check if new unit already has active lease (excluding current lease)
        if ($request->unit_id != $lease->unit_id) {
            $hasActiveLease = Lease::where('unit_id', $request->unit_id)
                ->where('status', 'active')
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->exists();

            if ($hasActiveLease) {
                return back()->withErrors(['unit_id' => 'Phòng này đã có hợp đồng hoạt động.']);
            }
        }

        try {
            DB::beginTransaction();

            // Update lease
            $lease->update([
                'unit_id' => $request->unit_id,
                'tenant_id' => $request->tenant_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'rent_amount' => $rentAmount,
                'deposit_amount' => $depositAmount ?? 0,
                'billing_day' => $request->billing_day ?? 1,
                'status' => $request->status,
                'contract_no' => $request->contract_no,
                'signed_at' => $request->signed_at,
            ]);

            // Update services
            $lease->leaseServices()->delete();
            if (!empty($request->services)) {
                foreach ($request->services as $serviceData) {
                    $lease->leaseServices()->create([
                        'service_id' => $serviceData['service_id'],
                        'price' => $serviceData['price'],
                    ]);
                }
            }

            // Update unit status based on lease status
            $this->updateUnitStatusBasedOnLease($lease, $request->status);

            // Create commission events if status changed to active
            if ($request->status === 'active' && $lease->status !== 'active') {
                $organization = $user->organizations()->first();
                try {
                    $this->createCommissionEvents($lease, $organization);
                } catch (\Exception $e) {
                    Log::error('Error creating commission events: ' . $e->getMessage());
                    // Don't fail the lease update if commission events fail
                }
            }

            DB::commit();

            return redirect()->route('agent.leases.show', $lease->id)
                ->with('success', 'Hợp đồng đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating lease: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật hợp đồng: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified lease from storage.
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get lease created by this agent
        $lease = Lease::where('agent_id', $user->id)->findOrFail($id);

        try {
            DB::beginTransaction();
            
            // Soft delete the lease
            $lease->delete();

            // Update unit status after lease deletion
            $this->updateUnitStatusAfterLeaseDeletion($lease);

            DB::commit();

            return redirect()->route('agent.leases.index')
                ->with('success', 'Hợp đồng đã được xóa thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting lease: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xóa hợp đồng: ' . $e->getMessage());
        }
    }

    /**
     * Generate contract number
     */
    private function generateContractNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        // Find the highest contract number for current year/month
        $lastContract = Lease::where('contract_no', 'like', "HD{$year}{$month}%")
            ->orderBy('contract_no', 'desc')
            ->first();
        
        if ($lastContract) {
            $lastNumber = (int) substr($lastContract->contract_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "HD{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create commission events for lease
     */
    private function createCommissionEvents($lease, $organization)
    {
        if (!$organization) {
            Log::warning('No organization found for user, skipping commission events');
            return;
        }

        // Get commission policies for lease_signed trigger
        $leasePolicies = CommissionPolicy::where('organization_id', $organization->id)
            ->where('trigger_event', 'lease_signed')
            ->where('active', true)
            ->get();

        Log::info('Found ' . $leasePolicies->count() . ' lease_signed commission policies for organization ' . $organization->id);

        foreach ($leasePolicies as $policy) {
            $baseAmount = $lease->rent_amount;
            $commissionTotal = $this->calculateCommission($policy, $baseAmount);

            if ($commissionTotal > 0) {
                $event = CommissionEvent::create([
                    'policy_id' => $policy->id,
                    'organization_id' => $organization->id,
                    'trigger_event' => 'lease_signed',
                    'ref_type' => 'lease',
                    'ref_id' => $lease->id,
                    'lease_id' => $lease->id,
                    'unit_id' => $lease->unit_id,
                    'agent_id' => $lease->agent_id,
                    'user_id' => $lease->agent_id,
                    'occurred_at' => $lease->signed_at ?? now(),
                    'amount_base' => $baseAmount,
                    'commission_total' => $commissionTotal,
                    'status' => 'pending'
                ]);
            }
        }

        // Create deposit commission event if deposit amount > 0
        if ($lease->deposit_amount > 0) {
            $depositPolicies = CommissionPolicy::where('organization_id', $organization->id)
                ->where('trigger_event', 'deposit_paid')
                ->where('active', true)
                ->get();

            Log::info('Found ' . $depositPolicies->count() . ' deposit_paid commission policies for organization ' . $organization->id);

            foreach ($depositPolicies as $policy) {
                $baseAmount = $lease->deposit_amount;
                $commissionTotal = $this->calculateCommission($policy, $baseAmount);

                if ($commissionTotal > 0) {
                    $event = CommissionEvent::create([
                        'policy_id' => $policy->id,
                        'organization_id' => $organization->id,
                        'trigger_event' => 'deposit_paid',
                        'ref_type' => 'lease',
                        'ref_id' => $lease->id,
                        'lease_id' => $lease->id,
                        'unit_id' => $lease->unit_id,
                        'agent_id' => $lease->agent_id,
                        'user_id' => $lease->agent_id,
                        'occurred_at' => $lease->signed_at ?? now(),
                        'amount_base' => $baseAmount,
                        'commission_total' => $commissionTotal,
                        'status' => 'pending'
                    ]);
                }
            }
        }
    }


    /**
     * Calculate commission amount based on policy
     */
    private function calculateCommission($policy, $baseAmount)
    {
        $commission = 0;

        switch ($policy->calc_type) {
            case 'percent':
                $commission = ($baseAmount * $policy->percent_value) / 100;
                break;
            case 'flat':
                $commission = $policy->flat_amount;
                break;
        }

        // Apply minimum amount
        if ($policy->min_amount && $commission < $policy->min_amount) {
            $commission = $policy->min_amount;
        }

        // Apply cap amount
        if ($policy->cap_amount && $commission > $policy->cap_amount) {
            $commission = $policy->cap_amount;
        }

        return $commission;
    }

    /**
     * Update unit status based on lease status
     */
    private function updateUnitStatusBasedOnLease($lease, $leaseStatus)
    {
        $unit = $lease->unit;
        if (!$unit) {
            return;
        }

        switch ($leaseStatus) {
            case 'active':
                $unit->update(['status' => 'occupied']);
                break;
                
            case 'terminated':
            case 'expired':
                $hasOtherActiveLease = Lease::where('unit_id', $unit->id)
                    ->where('status', 'active')
                    ->where('id', '!=', $lease->id)
                    ->whereNull('deleted_at')
                    ->exists();
                
                if (!$hasOtherActiveLease) {
                    $unit->update(['status' => 'available']);
                }
                break;
                
            case 'draft':
                if ($unit->status === 'occupied') {
                    $hasOtherActiveLease = Lease::where('unit_id', $unit->id)
                        ->where('status', 'active')
                        ->where('id', '!=', $lease->id)
                        ->whereNull('deleted_at')
                        ->exists();
                    
                    if (!$hasOtherActiveLease) {
                        $unit->update(['status' => 'available']);
                    }
                }
                break;
        }
    }

    /**
     * Update unit status after lease deletion
     */
    private function updateUnitStatusAfterLeaseDeletion($deletedLease)
    {
        $unit = $deletedLease->unit;
        if (!$unit) {
            return;
        }

        $hasOtherActiveLease = Lease::where('unit_id', $unit->id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->exists();

        if (!$hasOtherActiveLease) {
            $unit->update(['status' => 'available']);
        }
    }

    /**
     * API method to get units for a property
     */
    public function getUnits($propertyId)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Check if property is assigned to agent
            $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
            if (!$assignedPropertyIds->contains($propertyId)) {
                return response()->json(['error' => 'Bạn không có quyền truy cập bất động sản này'], 403);
            }

            $units = Unit::where('property_id', $propertyId)
                ->where('status', 'available')
                ->get()
                ->map(function ($unit) {
                    $hasActiveLease = Lease::where('unit_id', $unit->id)
                        ->where('status', 'active')
                        ->whereNull('deleted_at')
                        ->exists();
                    
                    $unit->has_active_lease = $hasActiveLease;
                    return $unit;
                });

            return response()->json($units);
        } catch (\Exception $e) {
            Log::error('Error in getUnits: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi tải dữ liệu phòng'], 500);
        }
    }

    /**
     * API method to get next contract number
     */
    public function getNextContractNumber()
    {
        try {
            $contractNumber = $this->generateContractNumber();
            return response()->json(['contract_no' => $contractNumber]);
        } catch (\Exception $e) {
            Log::error('Error generating contract number: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi sinh mã hợp đồng'], 500);
        }
    }
}
