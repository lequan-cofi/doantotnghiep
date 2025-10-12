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

        // Get tenants from default organizations and agent's organization
        $defaultOrgs = \App\Models\Organization::where('name', 'Default Organization')
            ->orWhere('code', 'ORG_MAIN')
            ->orWhere('name', 'Tổ chức mặc định')
            ->get();
        $agentOrg = $user->organizations()->first();
        
        $tenantOrgIds = collect();
        foreach ($defaultOrgs as $defaultOrg) {
            $tenantOrgIds->push($defaultOrg->id);
        }
        if ($agentOrg) {
            $tenantOrgIds->push($agentOrg->id);
        }
        
        $tenants = User::whereHas('userRoles', function($q) {
            $q->where('key_code', 'tenant');
        })->whereHas('organizations', function($q) use ($tenantOrgIds) {
            $q->whereIn('organizations.id', $tenantOrgIds);
        })->with(['organizations' => function($q) use ($tenantOrgIds) {
            $q->whereIn('organizations.id', $tenantOrgIds);
        }])->get();

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

            // Move tenant to agent's organization when creating lease
            $tenant = User::find($request->tenant_id);
            if ($tenant && $organization) {
                // Get tenant role
                $tenantRole = \App\Models\Role::where('key_code', 'tenant')->first();
                if ($tenantRole) {
                    // Remove tenant from current organizations and add to agent's organization
                    $tenant->organizations()->detach();
                    
                    // Create organization user record with role_id
                    \App\Models\OrganizationUser::create([
                        'organization_id' => $organization->id,
                        'user_id' => $tenant->id,
                        'role_id' => $tenantRole->id,
                        'status' => 'active'
                    ]);
                    
                    Log::info('Tenant assigned to agent organization when creating lease', [
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->full_name,
                        'agent_id' => $user->id,
                        'agent_org_id' => $organization->id,
                        'agent_org_name' => $organization->name,
                        'role_id' => $tenantRole->id,
                        'lease_id' => $lease->id
                    ]);
                }
            }

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
                'residents.user'
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
                
                // Move tenant back to default organization when lease ends
                $this->moveTenantToDefaultOrganization($lease->tenant_id);
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
                Log::warning('User ' . $user->id . ' tried to access property ' . $propertyId . ' without permission');
                return response()->json(['error' => 'Bạn không có quyền truy cập bất động sản này'], 403);
            }

            $units = Unit::where('property_id', $propertyId)
                ->get()
                ->map(function ($unit) {
                    $hasActiveLease = Lease::where('unit_id', $unit->id)
                        ->where('status', 'active')
                        ->whereNull('deleted_at')
                        ->exists();
                    
                    $unit->has_active_lease = $hasActiveLease;
                    return $unit;
                });

            Log::info('Found ' . $units->count() . ' units for property ' . $propertyId);
            return response()->json($units);
        } catch (\Exception $e) {
            Log::error('Error in getUnits: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Có lỗi xảy ra khi tải dữ liệu phòng: ' . $e->getMessage()], 500);
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

    /**
     * API method to search users for resident selection
     * Only searches tenant users in agent's organization and default organization
     */
    public function searchUsers(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            Log::info('Search tenant users request', ['query' => $query]);

            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Get agent's organization
            $agentOrganization = $user->organizations()->first();
            $agentOrgId = $agentOrganization ? $agentOrganization->id : null;
            
            // Get Default Organization - try multiple ways to find it
            $defaultOrg = \App\Models\Organization::where('name', 'Default Organization')
                ->orWhere('code', 'ORG_MAIN')
                ->orWhere('name', 'Tổ chức mặc định')
                ->orWhere('name', 'like', '%Default%')
                ->orWhere('name', 'like', '%Mặc định%')
                ->orWhere('id', 3) // Force include organization with ID = 3
                ->first();
            $defaultOrgId = $defaultOrg ? $defaultOrg->id : null;
            
            // If still not found, try to get organization with ID = 3 directly
            if (!$defaultOrgId) {
                $orgWithId3 = \App\Models\Organization::find(3);
                if ($orgWithId3) {
                    $defaultOrg = $orgWithId3;
                    $defaultOrgId = 3;
                }
            }
            
            Log::info('Organization IDs', [
                'agent_org_id' => $agentOrgId,
                'default_org_id' => $defaultOrgId,
                'default_org_name' => $defaultOrg ? $defaultOrg->name : null,
                'agent_org_name' => $agentOrganization ? $agentOrganization->name : null,
                'org_id_3_exists' => \App\Models\Organization::find(3) ? true : false,
                'org_id_3_name' => \App\Models\Organization::find(3) ? \App\Models\Organization::find(3)->name : null
            ]);

            // Build organization IDs array
            $orgIds = [];
            if ($agentOrgId) {
                $orgIds[] = $agentOrgId;
            }
            if ($defaultOrgId && $defaultOrgId !== $agentOrgId) {
                $orgIds[] = $defaultOrgId;
            }
            
            // Always include organization ID = 3 if it exists
            if (!in_array(3, $orgIds)) {
                $org3 = \App\Models\Organization::find(3);
                if ($org3) {
                    $orgIds[] = 3;
                }
            }

            Log::info('Final organization IDs for search', ['org_ids' => $orgIds]);

            // If no organizations found, return empty result
            if (empty($orgIds)) {
                Log::warning('No organizations found for user search');
                return response()->json([]);
            }

            // Search tenant users in agent's organization and default organization
            $usersQuery = \App\Models\User::whereHas('userRoles', function($roleQuery) {
                    $roleQuery->where('key_code', 'tenant');
                })
                ->whereHas('organizations', function($orgQuery) use ($orgIds) {
                    $orgQuery->whereIn('organizations.id', $orgIds);
                })
                ->with(['userProfile', 'organizations']);

            // Apply search filter if query is provided
            if (!empty($query)) {
                $usersQuery->where(function($q) use ($query) {
                    $q->where('full_name', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                });
            }

            $users = $usersQuery->limit(20)
                ->get()
                ->map(function($user) {
                    $orgNames = $user->organizations->pluck('name')->join(', ');
                    return [
                        'id' => $user->id,
                        'text' => $user->full_name . ' (' . $user->phone . ') - ' . $orgNames,
                        'name' => $user->full_name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'id_number' => $user->userProfile?->id_number,
                        'organizations' => $orgNames,
                    ];
                });

            Log::info('Found tenant users', ['count' => $users->count(), 'query' => $query]);
            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Error searching tenant users: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Có lỗi xảy ra khi tìm kiếm người dùng: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Debug method to check all organizations and users
     */
    public function debugOrganizations()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            $allOrgs = \App\Models\Organization::all();
            
            // Get agent's organization
            $agentOrganization = $user->organizations()->first();
            
            // Try to find Default Organization
            $defaultOrg = \App\Models\Organization::where('name', 'Default Organization')
                ->orWhere('code', 'ORG_MAIN')
                ->orWhere('name', 'Tổ chức mặc định')
                ->orWhere('name', 'like', '%Default%')
                ->orWhere('name', 'like', '%Mặc định%')
                ->first();
            
            // Get users in each organization
            $orgsWithUsers = $allOrgs->map(function($org) {
                $users = $org->users()->limit(5)->get();
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'code' => $org->code,
                    'user_count' => $org->users()->count(),
                    'sample_users' => $users->map(function($u) {
                        return [
                            'id' => $u->id,
                            'name' => $u->full_name,
                            'email' => $u->email,
                            'phone' => $u->phone
                        ];
                    })
                ];
            });
            
            return response()->json([
                'current_user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email
                ],
                'agent_organization' => $agentOrganization ? [
                    'id' => $agentOrganization->id,
                    'name' => $agentOrganization->name,
                    'code' => $agentOrganization->code
                ] : null,
                'default_organization' => $defaultOrg ? [
                    'id' => $defaultOrg->id,
                    'name' => $defaultOrg->name,
                    'code' => $defaultOrg->code
                ] : null,
                'all_organizations' => $orgsWithUsers
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Test search with specific query
     */
    public function testSearch($query = 'test')
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Get agent's organization
            $agentOrganization = $user->organizations()->first();
            $agentOrgId = $agentOrganization ? $agentOrganization->id : null;
            
            // Get Default Organization
            $defaultOrg = \App\Models\Organization::where('name', 'Default Organization')
                ->orWhere('code', 'ORG_MAIN')
                ->orWhere('name', 'Tổ chức mặc định')
                ->orWhere('name', 'like', '%Default%')
                ->orWhere('name', 'like', '%Mặc định%')
                ->first();
            $defaultOrgId = $defaultOrg ? $defaultOrg->id : null;
            
            // Build organization IDs array
            $orgIds = [];
            if ($agentOrgId) {
                $orgIds[] = $agentOrgId;
            }
            if ($defaultOrgId && $defaultOrgId !== $agentOrgId) {
                $orgIds[] = $defaultOrgId;
            }
            
            // Test search in organizations
            $usersInOrgs = [];
            if (!empty($orgIds)) {
                $usersInOrgs = \App\Models\User::where(function($q) use ($query) {
                        $q->where('full_name', 'like', "%{$query}%")
                          ->orWhere('phone', 'like', "%{$query}%")
                          ->orWhere('email', 'like', "%{$query}%");
                    })
                    ->whereHas('organizations', function($orgQuery) use ($orgIds) {
                        $orgQuery->whereIn('organizations.id', $orgIds);
                    })
                    ->with(['userProfile', 'organizations'])
                    ->limit(10)
                    ->get()
                    ->map(function($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->full_name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'organizations' => $user->organizations->pluck('name')->join(', ')
                        ];
                    });
            }
            
            // Test search all users
            $allUsers = \App\Models\User::where(function($q) use ($query) {
                    $q->where('full_name', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->with(['userProfile', 'organizations'])
                ->limit(10)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'organizations' => $user->organizations->pluck('name')->join(', ')
                    ];
                });
            
            return response()->json([
                'query' => $query,
                'agent_org_id' => $agentOrgId,
                'default_org_id' => $defaultOrgId,
                'org_ids' => $orgIds,
                'users_in_organizations' => $usersInOrgs,
                'all_users' => $allUsers,
                'count_in_orgs' => count($usersInOrgs),
                'count_all' => count($allUsers)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Debug organization ID = 3
     */
    public function debugOrg3()
    {
        try {
            $org3 = \App\Models\Organization::find(3);
            $usersInOrg3 = $org3 ? $org3->users()->with('userRoles')->get() : collect();
            
            return response()->json([
                'org_3_exists' => $org3 ? true : false,
                'org_3_details' => $org3 ? [
                    'id' => $org3->id,
                    'name' => $org3->name,
                    'code' => $org3->code
                ] : null,
                'users_in_org_3' => $usersInOrg3->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'roles' => $user->userRoles->pluck('key_code')->toArray()
                    ];
                }),
                'tenant_users_in_org_3' => $usersInOrg3->filter(function($user) {
                    return $user->userRoles->where('key_code', 'tenant')->isNotEmpty();
                })->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'phone' => $user->phone
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Simple test method
     */
    public function simpleTest()
    {
        try {
            // Test 1: Get all organizations
            $allOrgs = \App\Models\Organization::all();
            
            // Test 2: Get all users
            $allUsers = \App\Models\User::limit(5)->get();
            
            // Test 3: Get users with organizations
            $usersWithOrgs = \App\Models\User::with('organizations')->limit(5)->get();
            
            // Test 4: Find Default Organization specifically
            $defaultOrg1 = \App\Models\Organization::where('name', 'Default Organization')->first();
            $defaultOrg2 = \App\Models\Organization::where('code', 'ORG_MAIN')->first();
            $defaultOrg3 = \App\Models\Organization::where('name', 'like', '%Default%')->first();
            
            return response()->json([
                'total_organizations' => $allOrgs->count(),
                'organizations' => $allOrgs->map(function($org) {
                    return [
                        'id' => $org->id,
                        'name' => $org->name,
                        'code' => $org->code
                    ];
                }),
                'total_users' => \App\Models\User::count(),
                'sample_users' => $allUsers->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email
                    ];
                }),
                'users_with_organizations' => $usersWithOrgs->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'organizations' => $user->organizations->pluck('name')->toArray()
                    ];
                }),
                'default_org_tests' => [
                    'by_name_exact' => $defaultOrg1 ? ['id' => $defaultOrg1->id, 'name' => $defaultOrg1->name] : null,
                    'by_code' => $defaultOrg2 ? ['id' => $defaultOrg2->id, 'name' => $defaultOrg2->name] : null,
                    'by_name_like' => $defaultOrg3 ? ['id' => $defaultOrg3->id, 'name' => $defaultOrg3->name] : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Test method to check users and organizations
     */
    public function testUsers()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Get agent's organization
            $agentOrganization = $user->organizations()->first();
            $agentOrgId = $agentOrganization ? $agentOrganization->id : null;
            
            // Get Default Organization - try multiple ways to find it
            $defaultOrg = \App\Models\Organization::where('name', 'Default Organization')
                ->orWhere('code', 'ORG_MAIN')
                ->orWhere('name', 'Tổ chức mặc định')
                ->orWhere('name', 'like', '%Default%')
                ->orWhere('name', 'like', '%Mặc định%')
                ->first();
            $defaultOrgId = $defaultOrg ? $defaultOrg->id : null;
            
            // Build organization IDs array
            $orgIds = [];
            if ($agentOrgId) {
                $orgIds[] = $agentOrgId;
            }
            if ($defaultOrgId && $defaultOrgId !== $agentOrgId) {
                $orgIds[] = $defaultOrgId;
            }
            
            // Count users in these organizations
            $usersInOrgs = \App\Models\User::whereHas('organizations', function($orgQuery) use ($orgIds) {
                $orgQuery->whereIn('organizations.id', $orgIds);
            })->count();
            
            $totalUsers = \App\Models\User::count();
            
            return response()->json([
                'current_user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                ],
                'agent_organization' => $agentOrganization ? [
                    'id' => $agentOrganization->id,
                    'name' => $agentOrganization->name,
                ] : null,
                'default_organization' => $defaultOrg ? [
                    'id' => $defaultOrg->id,
                    'name' => $defaultOrg->name,
                ] : null,
                'organization_ids' => $orgIds,
                'total_users' => $totalUsers,
                'users_in_organizations' => $usersInOrgs,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check if user should be moved to Default Organization
     * Only move if user is not in agent's organization and has no active leases
     */
    private function checkAndMoveUserToDefaultOrganization($user, $agentId)
    {
        // Get agent's organization
        $agent = \App\Models\User::find($agentId);
        $agentOrganization = $agent ? $agent->organizations()->first() : null;
        
        // Check if user is in agent's organization
        $isInAgentOrg = false;
        if ($agentOrganization) {
            $isInAgentOrg = $user->organizations()->where('organizations.id', $agentOrganization->id)->exists();
        }
        
        // Check if user has active leases (as tenant or resident)
        $hasActiveLeases = \App\Models\Lease::where('tenant_id', $user->id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->exists();
            
        $hasActiveResidentLeases = \App\Models\LeaseResident::where('user_id', $user->id)
            ->whereHas('lease', function($query) {
                $query->where('status', 'active')->whereNull('deleted_at');
            })
            ->exists();
        
        // Only move to Default Organization if:
        // 1. User is not in agent's organization AND
        // 2. User has no active leases (as tenant or resident)
        if (!$isInAgentOrg && !$hasActiveLeases && !$hasActiveResidentLeases) {
            $this->moveUserToDefaultOrganization($user);
            
            Log::info('User moved to Default Organization after checking conditions', [
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'is_in_agent_org' => $isInAgentOrg,
                'has_active_leases' => $hasActiveLeases,
                'has_active_resident_leases' => $hasActiveResidentLeases,
                'agent_id' => $agentId
            ]);
        } else {
            Log::info('User kept in current organization after checking conditions', [
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'is_in_agent_org' => $isInAgentOrg,
                'has_active_leases' => $hasActiveLeases,
                'has_active_resident_leases' => $hasActiveResidentLeases,
                'agent_id' => $agentId
            ]);
        }
    }

    /**
     * Move user to Default Organization (ID = 3)
     */
    private function moveUserToDefaultOrganization($user)
    {
        // Get Default Organization (ID = 3) or fallback to other default organizations
        $defaultOrg = \App\Models\Organization::find(3);
        if (!$defaultOrg) {
            $defaultOrgs = \App\Models\Organization::where('name', 'Default Organization')
                ->orWhere('code', 'ORG_MAIN')
                ->orWhere('name', 'Tổ chức mặc định')
                ->orWhere('name', 'like', '%Default%')
                ->orWhere('name', 'like', '%Mặc định%')
                ->get();
            $defaultOrg = $defaultOrgs->where('name', 'Default Organization')->first() 
                ?? $defaultOrgs->first();
        }
        
        if ($defaultOrg) {
            // Get tenant role
            $tenantRole = \App\Models\Role::where('key_code', 'tenant')->first();
            if ($tenantRole) {
                // Remove user from current organizations and add to Default Organization
                $user->organizations()->detach();
                
                // Create organization user record with role_id
                \App\Models\OrganizationUser::create([
                    'organization_id' => $defaultOrg->id,
                    'user_id' => $user->id,
                    'role_id' => $tenantRole->id,
                    'status' => 'active'
                ]);
                
                Log::info('User moved to Default Organization', [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'default_org_id' => $defaultOrg->id,
                    'default_org_name' => $defaultOrg->name,
                    'role_id' => $tenantRole->id
                ]);
            }
        } else {
            Log::warning('Default Organization not found when moving user', [
                'user_id' => $user->id,
                'user_name' => $user->full_name
            ]);
        }
    }

    /**
     * Move tenant back to default organization when lease ends
     */
    private function moveTenantToDefaultOrganization($tenantId)
    {
        $tenant = User::find($tenantId);
        if (!$tenant) {
            return;
        }

        // Use the new method to move tenant to Default Organization
        $this->moveUserToDefaultOrganization($tenant);
        
        Log::info('Tenant moved to Default Organization when lease ended', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->full_name
        ]);
    }

    /**
     * Add resident to lease
     */
    public function addResident(Request $request, $leaseId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get lease created by this agent
        $lease = Lease::where('agent_id', $user->id)->findOrFail($leaseId);

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|max:20',
            'note' => 'nullable|string|max:500',
        ], [
            'user_id.exists' => 'Người dùng không tồn tại trong hệ thống.',
            'name.required' => 'Vui lòng nhập tên người ở cùng.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'id_number.max' => 'Số CMND/CCCD không được vượt quá 20 ký tự.',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự.',
        ]);

        try {
            // If user_id is provided, get user info and auto-fill some fields
            $residentData = [
                'lease_id' => $lease->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'id_number' => $request->id_number,
                'note' => $request->note,
            ];

            if ($request->user_id) {
                $selectedUser = \App\Models\User::find($request->user_id);
                if ($selectedUser) {
                    $residentData['user_id'] = $selectedUser->id;
                    
                    // Auto-fill name and phone if not provided
                    if (empty($residentData['name'])) {
                        $residentData['name'] = $selectedUser->full_name;
                    }
                    if (empty($residentData['phone'])) {
                        $residentData['phone'] = $selectedUser->phone;
                    }
                    
                    // Auto-fill ID number from user profile if available
                    if (empty($residentData['id_number']) && $selectedUser->userProfile) {
                        $residentData['id_number'] = $selectedUser->userProfile->id_number;
                    }
                    
                    // Assign user to agent's organization
                    $agentOrganization = $user->organizations()->first();
                    if ($agentOrganization) {
                        // Get tenant role
                        $tenantRole = \App\Models\Role::where('key_code', 'tenant')->first();
                        if ($tenantRole) {
                            // Remove user from current organizations and add to agent's organization
                            $selectedUser->organizations()->detach();
                            
                            // Create organization user record with role_id
                            \App\Models\OrganizationUser::create([
                                'organization_id' => $agentOrganization->id,
                                'user_id' => $selectedUser->id,
                                'role_id' => $tenantRole->id,
                                'status' => 'active'
                            ]);
                            
                            Log::info('User assigned to agent organization when adding resident', [
                                'user_id' => $selectedUser->id,
                                'user_name' => $selectedUser->full_name,
                                'agent_id' => $user->id,
                                'agent_org_id' => $agentOrganization->id,
                                'agent_org_name' => $agentOrganization->name,
                                'role_id' => $tenantRole->id,
                                'lease_id' => $lease->id
                            ]);
                        }
                    }
                }
            }

            $resident = \App\Models\LeaseResident::create($residentData);

            // Load user relationship for response
            $resident->load('user.userProfile');

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm người ở cùng thành công!',
                'resident' => $resident
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding resident: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thêm người ở cùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update resident
     */
    public function updateResident(Request $request, $leaseId, $residentId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get lease created by this agent
        $lease = Lease::where('agent_id', $user->id)->findOrFail($leaseId);
        
        // Get resident
        $resident = \App\Models\LeaseResident::where('lease_id', $lease->id)
            ->where('id', $residentId)
            ->firstOrFail();

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|max:20',
            'note' => 'nullable|string|max:500',
        ], [
            'user_id.exists' => 'Người dùng không tồn tại trong hệ thống.',
            'name.required' => 'Vui lòng nhập tên người ở cùng.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'id_number.max' => 'Số CMND/CCCD không được vượt quá 20 ký tự.',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự.',
        ]);

        try {
            $updateData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'id_number' => $request->id_number,
                'note' => $request->note,
            ];

            // Handle user_id update
            if ($request->has('user_id')) {
                if ($request->user_id) {
                    $selectedUser = \App\Models\User::find($request->user_id);
                    if ($selectedUser) {
                        $updateData['user_id'] = $selectedUser->id;
                        
                        // Auto-fill name and phone if not provided
                        if (empty($updateData['name'])) {
                            $updateData['name'] = $selectedUser->full_name;
                        }
                        if (empty($updateData['phone'])) {
                            $updateData['phone'] = $selectedUser->phone;
                        }
                        
                        // Auto-fill ID number from user profile if available
                        if (empty($updateData['id_number']) && $selectedUser->userProfile) {
                            $updateData['id_number'] = $selectedUser->userProfile->id_number;
                        }
                        
                        // Assign user to agent's organization
                        $agentOrganization = $user->organizations()->first();
                        if ($agentOrganization) {
                            // Get tenant role
                            $tenantRole = \App\Models\Role::where('key_code', 'tenant')->first();
                            if ($tenantRole) {
                                // Remove user from current organizations and add to agent's organization
                                $selectedUser->organizations()->detach();
                                
                                // Create organization user record with role_id
                                \App\Models\OrganizationUser::create([
                                    'organization_id' => $agentOrganization->id,
                                    'user_id' => $selectedUser->id,
                                    'role_id' => $tenantRole->id,
                                    'status' => 'active'
                                ]);
                                
                                Log::info('User assigned to agent organization when updating resident', [
                                    'user_id' => $selectedUser->id,
                                    'user_name' => $selectedUser->full_name,
                                    'agent_id' => $user->id,
                                    'agent_org_id' => $agentOrganization->id,
                                    'agent_org_name' => $agentOrganization->name,
                                    'role_id' => $tenantRole->id,
                                    'resident_id' => $resident->id,
                                    'lease_id' => $lease->id
                                ]);
                            }
                        }
                    }
                } else {
                    // If removing user_id, check if user should be moved back to Default Organization
                    if ($resident->user_id) {
                        $previousUser = \App\Models\User::find($resident->user_id);
                        if ($previousUser) {
                            // Check if user should be moved back to Default Organization
                            $this->checkAndMoveUserToDefaultOrganization($previousUser, $lease->agent_id);
                        }
                    }
                    $updateData['user_id'] = null;
                }
            }

            $resident->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật thông tin người ở cùng thành công!',
                'resident' => $resident
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating resident: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thông tin người ở cùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete resident
     */
    public function deleteResident($leaseId, $residentId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get lease created by this agent
        $lease = Lease::where('agent_id', $user->id)->findOrFail($leaseId);
        
        // Get resident
        $resident = \App\Models\LeaseResident::where('lease_id', $lease->id)
            ->where('id', $residentId)
            ->firstOrFail();

        try {
            // If resident has a linked user, check if they should be moved back to Default Organization
            if ($resident->user_id) {
                $linkedUser = \App\Models\User::find($resident->user_id);
                if ($linkedUser) {
                    // Check if user should be moved back to Default Organization
                    $this->checkAndMoveUserToDefaultOrganization($linkedUser, $lease->agent_id);
                }
            }
            
            $resident->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa người ở cùng thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting resident: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa người ở cùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert lead to lease
     */
    public function createFromLead(Request $request, $leadId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get lead
        $lead = \App\Models\Lead::findOrFail($leadId);

        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            return redirect()->route('agent.leads.index')
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

        return view('agent.leases.create-from-lead', [
            'lead' => $lead,
            'properties' => $properties,
            'tenants' => $tenants,
            'services' => $services,
            'selectedProperty' => $selectedProperty
        ]);
    }

    /**
     * Store lease from lead
     */
    public function storeFromLead(Request $request, $leadId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get lead
        $lead = \App\Models\Lead::findOrFail($leadId);

        // Clean and validate currency inputs
        $rentAmount = str_replace(['.', ','], '', $request->rent_amount);
        $depositAmount = $request->deposit_amount ? str_replace(['.', ','], '', $request->deposit_amount) : null;
        
        // Validate request - tenant_id is optional when creating from lead
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'tenant_id' => 'nullable|exists:users,id',
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
        ]);

        // Ensure either tenant_id or lead_id is provided
        if (!$request->tenant_id && !$leadId) {
            return back()->withErrors(['tenant_id' => 'Vui lòng chọn khách thuê hoặc tạo từ lead.'])->withInput();
        }

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
                'lead_id' => $leadId, // Link to lead if creating from lead
                'agent_id' => $user->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'rent_amount' => $rentAmount,
                'deposit_amount' => $depositAmount ?? 0,
                'billing_day' => $request->billing_day ?? 1,
                'status' => $request->status,
                'contract_no' => $contractNo,
                'signed_at' => $request->signed_at,
            ]);

            // Move tenant to agent's organization when creating lease
            $tenant = User::find($request->tenant_id);
            if ($tenant && $organization) {
                // Get tenant role
                $tenantRole = \App\Models\Role::where('key_code', 'tenant')->first();
                if ($tenantRole) {
                    // Remove tenant from current organizations and add to agent's organization
                    $tenant->organizations()->detach();
                    
                    // Create organization user record with role_id
                    \App\Models\OrganizationUser::create([
                        'organization_id' => $organization->id,
                        'user_id' => $tenant->id,
                        'role_id' => $tenantRole->id,
                        'status' => 'active'
                    ]);
                    
                    Log::info('Tenant assigned to agent organization when creating lease from lead', [
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->full_name,
                        'agent_id' => $user->id,
                        'agent_org_id' => $organization->id,
                        'agent_org_name' => $organization->name,
                        'role_id' => $tenantRole->id,
                        'lease_id' => $lease->id,
                        'lead_id' => $leadId
                    ]);
                }
            }

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
                }
            }

            // Update lead status to converted
            $lead->update(['status' => 'converted']);

            DB::commit();

            return redirect()->route('agent.leases.show', $lease->id)
                ->with('success', 'Hợp đồng đã được tạo từ lead thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating lease from lead: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo hợp đồng từ lead: ' . $e->getMessage());
        }
    }

    /**
     * Link tenant_id to lease when lead creates user account
     */
    public function linkTenantToLease(Request $request, $leaseId)
    {
        $request->validate([
            'tenant_id' => 'required|exists:users,id',
        ]);

        $lease = Lease::findOrFail($leaseId);
        
        // Check if lease is from lead and doesn't have tenant_id yet
        if (!$lease->isFromLead()) {
            return response()->json([
                'success' => false,
                'message' => 'Hợp đồng này không phải từ lead hoặc đã có tenant.'
            ], 400);
        }

        try {
            $lease->update(['tenant_id' => $request->tenant_id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã gắn tài khoản khách thuê vào hợp đồng thành công.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error linking tenant to lease: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi gắn tài khoản khách thuê.'
            ], 500);
        }
    }

    /**
     * Get leases that need tenant linking (created from leads without tenant_id)
     */
    public function getLeasesNeedingTenantLink()
    {
        $leases = Lease::whereNotNull('lead_id')
            ->whereNull('tenant_id')
            ->with(['lead', 'unit.property'])
            ->get();

        return response()->json($leases);
    }
}
