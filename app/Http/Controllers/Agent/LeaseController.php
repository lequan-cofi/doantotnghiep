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

        // Only get properties that have available units
        $properties = \App\Models\Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->whereHas('units', function($query) {
                $query->where('status', 'available');
            })
            ->orderBy('name')
            ->get();

        // If no properties with available units found, show a message
        if ($properties->isEmpty()) {
            return redirect()->route('agent.leases.index')
                ->with('warning', 'Hiện tại không có bất động sản nào có phòng trống. Vui lòng liên hệ quản lý để thêm phòng mới hoặc kiểm tra trạng thái phòng.');
        }

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
            'lease_payment_cycle' => 'nullable|in:monthly,quarterly,yearly,custom',
            'lease_payment_day' => 'nullable|integer|min:1|max:31',
            'lease_payment_notes' => 'nullable|string|max:1000',
            'lease_custom_months' => 'nullable|integer|min:1|max:60',
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
                'lease_payment_cycle' => $request->lease_payment_cycle,
                'lease_payment_day' => $request->lease_payment_day,
                'lease_payment_notes' => $request->lease_payment_notes,
                'lease_custom_months' => $request->lease_custom_months,
                'status' => $request->status,
                'contract_no' => $contractNo,
                'signed_at' => $request->signed_at,
            ]);

            // Move tenant to agent's organization when creating lease
            $this->assignUserToAgentOrganization($request->tenant_id, $user->id, $lease->id, 'lease_create');

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

            // Commission events will be created automatically via LeaseObserver

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
                'residents.user',
                'invoices' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->findOrFail($id);

        // Get meters for this unit with their readings after lease signed date
        $meters = \App\Models\Meter::where('unit_id', $lease->unit_id)
            ->with([
                'service',
                'readings' => function($query) use ($lease) {
                    if ($lease->signed_at) {
                        $query->where('reading_date', '>=', $lease->signed_at->format('Y-m-d'))
                              ->orderBy('reading_date', 'desc');
                    } else {
                        $query->orderBy('reading_date', 'desc');
                    }
                }
            ])
            ->get();

        return view('agent.leases.show', compact('lease', 'meters'));
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
        
        // Only get properties that have available units (or the current property for editing)
        $properties = \App\Models\Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->where(function($query) use ($lease) {
                $query->whereHas('units', function($unitQuery) {
                    $unitQuery->where('status', 'available');
                })
                ->orWhere('id', $lease->unit->property_id); // Always include current property
            })
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
            'lease_payment_cycle' => 'nullable|in:monthly,quarterly,yearly,custom',
            'lease_payment_day' => 'nullable|integer|min:1|max:31',
            'lease_payment_notes' => 'nullable|string|max:1000',
            'lease_custom_months' => 'nullable|integer|min:1|max:60',
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

            // Handle tenant organization assignment when tenant changes
            $oldTenantId = $lease->tenant_id;
            $newTenantId = $request->tenant_id;
            
            if ($oldTenantId != $newTenantId) {
                // Move old tenant back to Default Organization if they have no other active leases
                if ($oldTenantId) {
                    $oldTenant = User::find($oldTenantId);
                    if ($oldTenant) {
                        // Check if old tenant should be moved to Default Organization
                        $this->checkAndMoveUserToDefaultOrganization($oldTenant, $user->id);
                    }
                }
                
                // Assign new tenant to agent's organization
                if ($newTenantId) {
                    $this->assignUserToAgentOrganization($newTenantId, $user->id, $id, 'lease_update');
                }
            }

            // Update lease
            $lease->update([
                'unit_id' => $request->unit_id,
                'tenant_id' => $request->tenant_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'rent_amount' => $rentAmount,
                'deposit_amount' => $depositAmount ?? 0,
                'billing_day' => $request->billing_day ?? 1,
                'lease_payment_cycle' => $request->lease_payment_cycle,
                'lease_payment_day' => $request->lease_payment_day,
                'lease_payment_notes' => $request->lease_payment_notes,
                'lease_custom_months' => $request->lease_custom_months,
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

            // Commission events will be created automatically via LeaseObserver when status changes

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
            
            // Move tenant back to Default Organization before deleting lease
            if ($lease->tenant_id) {
                $tenant = User::find($lease->tenant_id);
                if ($tenant) {
                    $this->checkAndMoveUserToDefaultOrganization($tenant, $user->id);
                }
            }
            
            // Move all residents back to Default Organization before deleting lease
            $residents = $lease->residents()->whereNotNull('user_id')->get();
            foreach ($residents as $resident) {
                if ($resident->user_id) {
                    $residentUser = User::find($resident->user_id);
                    if ($residentUser) {
                        $this->checkAndMoveUserToDefaultOrganization($residentUser, $user->id);
                    }
                }
            }
            
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

    // Commission events logic moved to CommissionEventService and handled by LeaseObserver

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
                if ($lease->tenant_id) {
                    $tenant = User::find($lease->tenant_id);
                    if ($tenant) {
                        $this->checkAndMoveUserToDefaultOrganization($tenant, $lease->agent_id);
                    }
                }
                
                // Move all residents back to default organization when lease ends
                $residents = $lease->residents()->whereNotNull('user_id')->get();
                foreach ($residents as $resident) {
                    if ($resident->user_id) {
                        $residentUser = User::find($resident->user_id);
                        if ($residentUser) {
                            $this->checkAndMoveUserToDefaultOrganization($residentUser, $lease->agent_id);
                        }
                    }
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
     * API method to get property payment cycle settings
     */
    public function getPropertyPaymentCycle($propertyId)
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

            // Get property
            $property = \App\Models\Property::findOrFail($propertyId);

            return response()->json([
                'success' => true,
                'property' => [
                    'id' => $property->id,
                    'name' => $property->name,
                    'prop_payment_cycle' => $property->prop_payment_cycle,
                    'prop_payment_day' => $property->prop_payment_day,
                    'prop_payment_notes' => $property->prop_payment_notes,
                    'prop_custom_months' => $property->prop_custom_months,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting property payment cycle: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi tải cài đặt chu kỳ thanh toán: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API method to get existing deposits for a unit
     */
    public function getUnitDeposits($unitId)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Get the unit and check if it belongs to assigned properties
            $unit = Unit::with('property')->findOrFail($unitId);
            $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
            
            if (!$assignedPropertyIds->contains($unit->property_id)) {
                Log::warning('User ' . $user->id . ' tried to access unit ' . $unitId . ' without permission');
                return response()->json(['error' => 'Bạn không có quyền truy cập phòng này'], 403);
            }

            // Get existing deposits for this unit
            $deposits = \App\Models\BookingDeposit::where('unit_id', $unitId)
                ->whereNull('deleted_at')
                ->whereIn('payment_status', ['pending', 'paid']) // Only active deposits
                ->with(['tenantUser', 'lead', 'agent'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($deposit) {
                    return [
                        'id' => $deposit->id,
                        'amount' => $deposit->amount,
                        'amount_formatted' => number_format($deposit->amount, 0, ',', '.') . ' VNĐ',
                        'payment_status' => $deposit->payment_status,
                        'payment_status_text' => $this->getPaymentStatusText($deposit->payment_status),
                        'deposit_type' => $deposit->deposit_type,
                        'deposit_type_text' => $this->getDepositTypeText($deposit->deposit_type),
                        'hold_until' => $deposit->hold_until ? $deposit->hold_until->format('d/m/Y H:i') : null,
                        'paid_at' => $deposit->paid_at ? $deposit->paid_at->format('d/m/Y H:i') : null,
                        'created_at' => $deposit->created_at->format('d/m/Y H:i'),
                        'tenant_name' => $deposit->tenantUser ? $deposit->tenantUser->full_name : 
                                       ($deposit->lead ? $deposit->lead->name : 'N/A'),
                        'tenant_phone' => $deposit->tenantUser ? $deposit->tenantUser->phone : 
                                        ($deposit->lead ? $deposit->lead->phone : 'N/A'),
                        'agent_name' => $deposit->agent ? $deposit->agent->full_name : 'N/A',
                        'notes' => $deposit->notes,
                        'reference_number' => $deposit->reference_number,
                    ];
                });

            Log::info('Found ' . $deposits->count() . ' deposits for unit ' . $unitId);
            return response()->json([
                'success' => true,
                'deposits' => $deposits,
                'total_amount' => $deposits->sum('amount'),
                'total_amount_formatted' => number_format($deposits->sum('amount'), 0, ',', '.') . ' VNĐ'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getUnitDeposits: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Có lỗi xảy ra khi tải thông tin cọc: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get payment status text in Vietnamese
     */
    private function getPaymentStatusText($status)
    {
        $statusTexts = [
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'refunded' => 'Đã hoàn tiền',
            'expired' => 'Hết hạn',
            'cancelled' => 'Đã hủy'
        ];
        
        return $statusTexts[$status] ?? $status;
    }

    /**
     * Get deposit type text in Vietnamese
     */
    private function getDepositTypeText($type)
    {
        $typeTexts = [
            'booking' => 'Đặt cọc giữ chỗ',
            'security' => 'Cọc an ninh',
            'advance' => 'Cọc trước'
        ];
        
        return $typeTexts[$type] ?? $type;
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
     * Only move if user has no active leases (as tenant or resident) in ANY organization
     */
    private function checkAndMoveUserToDefaultOrganization($user, $agentId)
    {
        // Check if user has active leases (as tenant or resident) in ANY organization
        $hasActiveLeases = \App\Models\Lease::where('tenant_id', $user->id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->exists();
            
        $hasActiveResidentLeases = \App\Models\LeaseResident::where('user_id', $user->id)
            ->whereHas('lease', function($query) {
                $query->where('status', 'active')->whereNull('deleted_at');
            })
            ->exists();
        
        // Check if user can be added to any other leases (draft status or as resident)
        $canBeAddedToOtherLeases = $this->canUserBeAddedToOtherLeases($user);
        
        // Only move to Default Organization if:
        // 1. User has no active leases (as tenant or resident) AND
        // 2. User cannot be added to any other draft leases
        if (!$hasActiveLeases && !$hasActiveResidentLeases && !$canBeAddedToOtherLeases) {
            $this->moveUserToDefaultOrganization($user);
            
            Log::info('User moved to Default Organization after checking conditions', [
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'has_active_leases' => $hasActiveLeases,
                'has_active_resident_leases' => $hasActiveResidentLeases,
                'can_be_added_to_other_leases' => $canBeAddedToOtherLeases,
                'agent_id' => $agentId
            ]);
        } else {
            Log::info('User kept in current organization after checking conditions', [
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'has_active_leases' => $hasActiveLeases,
                'has_active_resident_leases' => $hasActiveResidentLeases,
                'can_be_added_to_other_leases' => $canBeAddedToOtherLeases,
                'agent_id' => $agentId,
                'reason' => $hasActiveLeases ? 'has_active_leases' : 
                           ($hasActiveResidentLeases ? 'has_active_resident_leases' : 'can_be_added_to_other_leases')
            ]);
        }
    }

    /**
     * Check if user can be added to any other leases
     */
    private function canUserBeAddedToOtherLeases($user)
    {
        // Check if user can be added as tenant to draft leases
        $canBeTenantInDraftLeases = \App\Models\Lease::where('status', 'draft')
            ->whereNull('deleted_at')
            ->where(function($query) use ($user) {
                // Check if user is not already a tenant in this lease
                $query->where('tenant_id', '!=', $user->id)
                      ->orWhereNull('tenant_id');
            })
            ->exists();
        
        // Check if user can be added as resident to any active leases
        $canBeResidentInActiveLeases = \App\Models\Lease::where('status', 'active')
            ->whereNull('deleted_at')
            ->where('tenant_id', '!=', $user->id) // Not already the tenant
            ->whereDoesntHave('residents', function($query) use ($user) {
                $query->where('user_id', $user->id); // Not already a resident
            })
            ->exists();
        
        // Check if user can be added as resident to draft leases
        $canBeResidentInDraftLeases = \App\Models\Lease::where('status', 'draft')
            ->whereNull('deleted_at')
            ->where('tenant_id', '!=', $user->id) // Not already the tenant
            ->whereDoesntHave('residents', function($query) use ($user) {
                $query->where('user_id', $user->id); // Not already a resident
            })
            ->exists();
        
        $canBeAdded = $canBeTenantInDraftLeases || $canBeResidentInActiveLeases || $canBeResidentInDraftLeases;
        
        Log::info('Checking if user can be added to other leases', [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'can_be_tenant_in_draft_leases' => $canBeTenantInDraftLeases,
            'can_be_resident_in_active_leases' => $canBeResidentInActiveLeases,
            'can_be_resident_in_draft_leases' => $canBeResidentInDraftLeases,
            'can_be_added' => $canBeAdded
        ]);
        
        return $canBeAdded;
    }

    /**
     * Assign user to agent's organization
     */
    private function assignUserToAgentOrganization($userId, $agentId, $leaseId, $context = 'lease')
    {
        $user = User::find($userId);
        $agent = User::find($agentId);
        
        if (!$user || !$agent) {
            return;
        }
        
        $agentOrganization = $agent->organizations()->first();
        if (!$agentOrganization) {
            Log::warning('Agent has no organization', [
                'agent_id' => $agentId,
                'user_id' => $userId,
                'context' => $context
            ]);
            return;
        }
        
        // Get tenant role
        $tenantRole = \App\Models\Role::where('key_code', 'tenant')->first();
        if (!$tenantRole) {
            Log::warning('Tenant role not found', [
                'agent_id' => $agentId,
                'user_id' => $userId,
                'context' => $context
            ]);
            return;
        }
        
        // Remove user from current organizations and add to agent's organization
        $user->organizations()->detach();
        
        // Create organization user record with role_id
        \App\Models\OrganizationUser::create([
            'organization_id' => $agentOrganization->id,
            'user_id' => $user->id,
            'role_id' => $tenantRole->id,
            'status' => 'active'
        ]);
        
        Log::info('User assigned to agent organization', [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'agent_id' => $agentId,
            'agent_org_id' => $agentOrganization->id,
            'agent_org_name' => $agentOrganization->name,
            'role_id' => $tenantRole->id,
            'lease_id' => $leaseId,
            'context' => $context
        ]);
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
            DB::beginTransaction();
            
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
                    $this->assignUserToAgentOrganization($selectedUser->id, $user->id, $lease->id, 'resident_add');
                }
            }

            $resident = \App\Models\LeaseResident::create($residentData);

            // Load user relationship for response
            $resident->load('user.userProfile');
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm người ở cùng thành công!',
                'resident' => $resident
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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
            DB::beginTransaction();
            
            $updateData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'id_number' => $request->id_number,
                'note' => $request->note,
            ];

            // Handle user_id update
            if ($request->has('user_id')) {
                $oldUserId = $resident->user_id;
                $newUserId = $request->user_id;
                
                if ($oldUserId != $newUserId) {
                    // If changing user_id, handle old user first
                    if ($oldUserId) {
                        $oldUser = \App\Models\User::find($oldUserId);
                        if ($oldUser) {
                            // Check if old user should be moved back to Default Organization
                            $this->checkAndMoveUserToDefaultOrganization($oldUser, $lease->agent_id);
                        }
                    }
                    
                    // Handle new user
                    if ($newUserId) {
                        $selectedUser = \App\Models\User::find($newUserId);
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
                            $this->assignUserToAgentOrganization($selectedUser->id, $user->id, $lease->id, 'resident_update');
                        }
                    } else {
                        $updateData['user_id'] = null;
                    }
                }
            }

            $resident->update($updateData);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật thông tin người ở cùng thành công!',
                'resident' => $resident
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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
            DB::beginTransaction();
            
            // If resident has a linked user, check if they should be moved back to Default Organization
            if ($resident->user_id) {
                $linkedUser = \App\Models\User::find($resident->user_id);
                if ($linkedUser) {
                    // Check if user should be moved back to Default Organization
                    $this->checkAndMoveUserToDefaultOrganization($linkedUser, $lease->agent_id);
                }
            }
            
            $resident->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa người ở cùng thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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

        // Only get properties that have available units
        $properties = \App\Models\Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->whereHas('units', function($query) {
                $query->where('status', 'available');
            })
            ->orderBy('name')
            ->get();

        // If no properties with available units found, show a message
        if ($properties->isEmpty()) {
            return redirect()->route('agent.leads.index')
                ->with('warning', 'Hiện tại không có bất động sản nào có phòng trống. Vui lòng liên hệ quản lý để thêm phòng mới hoặc kiểm tra trạng thái phòng.');
        }

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
            'lease_payment_cycle' => 'nullable|in:monthly,quarterly,yearly,custom',
            'lease_payment_day' => 'nullable|integer|min:1|max:31',
            'lease_payment_notes' => 'nullable|string|max:1000',
            'lease_custom_months' => 'nullable|integer|min:1|max:60',
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
                'lease_payment_cycle' => $request->lease_payment_cycle,
                'lease_payment_day' => $request->lease_payment_day,
                'lease_payment_notes' => $request->lease_payment_notes,
                'lease_custom_months' => $request->lease_custom_months,
                'status' => $request->status,
                'contract_no' => $contractNo,
                'signed_at' => $request->signed_at,
            ]);

            // Move tenant to agent's organization when creating lease from lead
            if ($request->tenant_id) {
                $this->assignUserToAgentOrganization($request->tenant_id, $user->id, $lease->id, 'lease_create_from_lead');
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

            // Commission events will be created automatically via LeaseObserver

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

    /**
     * Debug method to check user organization status
     */
    public function debugUserOrganization($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Get user's current organizations
            $currentOrganizations = $user->organizations()->get();

            // Check active leases as tenant
            $activeLeasesAsTenant = Lease::where('tenant_id', $user->id)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->with(['unit.property', 'organization'])
                ->get();

            // Check active leases as resident
            $activeLeasesAsResident = \App\Models\LeaseResident::where('user_id', $user->id)
                ->whereHas('lease', function($query) {
                    $query->where('status', 'active')->whereNull('deleted_at');
                })
                ->with(['lease.unit.property', 'lease.organization'])
                ->get();

            // Check if user can be added to other leases
            $canBeAddedToOtherLeases = $this->canUserBeAddedToOtherLeases($user);

            // Get Default Organization
            $defaultOrg = \App\Models\Organization::find(3);
            if (!$defaultOrg) {
                $defaultOrg = \App\Models\Organization::where('name', 'Default Organization')
                    ->orWhere('code', 'ORG_MAIN')
                    ->orWhere('name', 'Tổ chức mặc định')
                    ->first();
            }

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone
                ],
                'current_organizations' => $currentOrganizations->map(function($org) {
                    return [
                        'id' => $org->id,
                        'name' => $org->name,
                        'code' => $org->code
                    ];
                }),
                'active_leases_as_tenant' => $activeLeasesAsTenant->map(function($lease) {
                    return [
                        'id' => $lease->id,
                        'contract_no' => $lease->contract_no,
                        'status' => $lease->status,
                        'unit' => $lease->unit->name ?? 'N/A',
                        'property' => $lease->unit->property->name ?? 'N/A',
                        'organization' => $lease->organization->name ?? 'N/A'
                    ];
                }),
                'active_leases_as_resident' => $activeLeasesAsResident->map(function($resident) {
                    return [
                        'resident_id' => $resident->id,
                        'lease_id' => $resident->lease->id,
                        'contract_no' => $resident->lease->contract_no,
                        'status' => $resident->lease->status,
                        'unit' => $resident->lease->unit->name ?? 'N/A',
                        'property' => $resident->lease->unit->property->name ?? 'N/A',
                        'organization' => $resident->lease->organization->name ?? 'N/A'
                    ];
                }),
                'can_be_added_to_other_leases' => $canBeAddedToOtherLeases,
                'default_organization' => $defaultOrg ? [
                    'id' => $defaultOrg->id,
                    'name' => $defaultOrg->name,
                    'code' => $defaultOrg->code
                ] : null,
                'should_move_to_default' => !$activeLeasesAsTenant->count() && !$activeLeasesAsResident->count() && !$canBeAddedToOtherLeases
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
