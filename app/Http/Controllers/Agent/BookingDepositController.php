<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\BookingDeposit;
use App\Models\Unit;
use App\Models\Lead;
use App\Models\User;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingDepositController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get organization ID
        $organizationId = $user->organizations()->first()->id ?? 1;
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            // If no properties assigned, get all active properties for the organization
            $assignedPropertyIds = Property::where('organization_id', $organizationId)
                ->where('status', 1)
                ->pluck('id');
        }

        // Query booking deposits
        $query = BookingDeposit::where('organization_id', $organizationId)
            ->whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })
            ->with(['unit.property', 'tenantUser', 'lead', 'agent']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('tenantUser', function($userQuery) use ($search) {
                      $userQuery->where('full_name', 'like', "%{$search}%")
                               ->orWhere('phone', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('lead', function($leadQuery) use ($search) {
                      $leadQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('phone', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('unit.property', function($propertyQuery) use ($search) {
                      $propertyQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by deposit type
        if ($request->filled('deposit_type')) {
            $query->where('deposit_type', $request->deposit_type);
        }

        // Filter by property
        if ($request->filled('property_id')) {
            $query->whereHas('unit', function($q) use ($request) {
                $q->where('property_id', $request->property_id);
            });
        }

        // Get deposits with sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort fields
        $allowedSortFields = ['id', 'created_at', 'amount', 'hold_until', 'payment_status', 'deposit_type'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        $deposits = $query->orderBy($sortBy, $sortOrder)->get();

        // Get assigned properties for filter
        $properties = Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return view('agent.booking-deposits.index', [
            'deposits' => $deposits,
            'properties' => $properties,
            'selectedProperty' => $request->property_id,
            'selectedPaymentStatus' => $request->payment_status,
            'selectedDepositType' => $request->deposit_type,
            'search' => $request->search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get organization ID
        $organizationId = $user->organizations()->first()->id ?? 1;
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            // If no properties assigned, get all active properties for the organization
            $assignedPropertyIds = Property::where('organization_id', $organizationId)
                ->where('status', 1)
                ->pluck('id');
        }
        
        // Get properties with available units only
        $properties = Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->whereHas('units', function($q) {
                $q->where('status', '!=', 'inactive')
                  ->whereDoesntHave('leases', function($leaseQuery) {
                      $leaseQuery->where('status', 'active')->whereNull('deleted_at');
                  })
                  ->whereDoesntHave('bookingDeposits', function($depositQuery) {
                      $depositQuery->whereIn('status', ['pending', 'confirmed']);
                  });
            })
            ->with(['units' => function($q) {
                $q->where('status', '!=', 'inactive')
                  ->whereDoesntHave('leases', function($leaseQuery) {
                      $leaseQuery->where('status', 'active')->whereNull('deleted_at');
                  })
                  ->whereDoesntHave('bookingDeposits', function($depositQuery) {
                      $depositQuery->whereIn('status', ['pending', 'confirmed']);
                  });
            }])
            ->orderBy('name')
            ->get();

        // Get tenant users from organization and default organization
        $defaultOrgs = \App\Models\Organization::where('name', 'Default Organization')
            ->orWhere('code', 'ORG_MAIN')
            ->orWhere('name', 'Tổ chức mặc định')
            ->orWhere('name', 'like', '%Default%')
            ->orWhere('name', 'like', '%Mặc định%')
            ->orWhere('id', 3) // Force include organization with ID = 3
            ->get();
        $agentOrg = $user->organizations()->first();
        
        $tenantOrgIds = collect();
        foreach ($defaultOrgs as $defaultOrg) {
            $tenantOrgIds->push($defaultOrg->id);
        }
        if ($agentOrg) {
            $tenantOrgIds->push($agentOrg->id);
        }
        
        $tenantUsers = User::whereHas('userRoles', function($q) {
            $q->where('key_code', 'tenant');
        })->whereHas('organizations', function($q) use ($tenantOrgIds) {
            $q->whereIn('organizations.id', $tenantOrgIds);
        })->with(['organizations' => function($q) use ($tenantOrgIds) {
            $q->whereIn('organizations.id', $tenantOrgIds);
        }])->orderBy('full_name')->get();

        // Get leads from organization
        $leads = Lead::where('organization_id', $organizationId)
            ->whereIn('status', ['new', 'contacted', 'qualified'])
            ->orderBy('name')
            ->get();

        // Pre-select property if provided
        $selectedProperty = null;
        $units = collect();
        if ($request->filled('property_id')) {
            $selectedProperty = $properties->find($request->property_id);
            if ($selectedProperty) {
                // Load units for the selected property (only available units)
                $units = Unit::where('property_id', $selectedProperty->id)
                    ->where('status', '!=', 'inactive')
                    ->whereDoesntHave('leases', function($q) {
                        $q->where('status', 'active')->whereNull('deleted_at');
                    })
                    ->whereDoesntHave('bookingDeposits', function($q) {
                        $q->whereIn('status', ['pending', 'confirmed']);
                    })
                    ->get()
                    ->map(function($unit) {
                        $unit->has_active_lease = false;
                        $unit->has_active_deposit = false;
                        return $unit;
                    });
            }
        }

        // Debug logging
        Log::info('BookingDeposit create method data', [
            'organization_id' => $organizationId,
            'properties_count' => $properties->count(),
            'tenant_users_count' => $tenantUsers->count(),
            'leads_count' => $leads->count(),
            'assigned_property_ids' => $assignedPropertyIds->toArray(),
            'tenant_org_ids' => $tenantOrgIds->toArray()
        ]);

        return view('agent.booking-deposits.create', [
            'properties' => $properties,
            'tenantUsers' => $tenantUsers,
            'leads' => $leads,
            'selectedProperty' => $selectedProperty,
            'units' => $units
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get organization ID
        $organizationId = $user->organizations()->first()->id ?? 1;
        
        // Clean amount input
        $amount = str_replace(['.', ','], '', $request->amount);
        
        // Validate request
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'tenant_user_id' => 'nullable|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'amount' => 'required|string|regex:/^[\d.,]+$/',
            'deposit_type' => 'required|in:booking,security,advance',
            'hold_until' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ], [
            'unit_id.required' => 'Vui lòng chọn phòng/căn hộ.',
            'unit_id.exists' => 'Phòng/căn hộ không tồn tại.',
            'tenant_user_id.exists' => 'Người thuê không tồn tại.',
            'lead_id.exists' => 'Lead không tồn tại.',
            'amount.required' => 'Vui lòng nhập số tiền đặt cọc.',
            'amount.regex' => 'Số tiền không hợp lệ.',
            'deposit_type.required' => 'Vui lòng chọn loại đặt cọc.',
            'deposit_type.in' => 'Loại đặt cọc không hợp lệ.',
            'hold_until.required' => 'Vui lòng chọn ngày hết hạn.',
            'hold_until.date' => 'Ngày hết hạn không hợp lệ.',
            'hold_until.after' => 'Ngày hết hạn phải sau thời điểm hiện tại.',
            'notes.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ]);

        // Ensure either tenant_user_id or lead_id is provided
        if (!$request->tenant_user_id && !$request->lead_id) {
            return back()->withErrors(['tenant_user_id' => 'Vui lòng chọn người thuê hoặc lead.'])->withInput();
        }

        // Check if unit belongs to assigned properties or organization
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        $unit = Unit::with('property')->findOrFail($request->unit_id);
        
        // If user has no assigned properties, allow access to all organization properties
        if ($assignedPropertyIds->isNotEmpty() && !$assignedPropertyIds->contains($unit->property_id)) {
            return back()->withErrors(['unit_id' => 'Bạn không có quyền tạo đặt cọc cho phòng này.'])->withInput();
        }
        
        // Check if unit belongs to user's organization
        if ($unit->property->organization_id !== $organizationId) {
            return back()->withErrors(['unit_id' => 'Phòng này không thuộc tổ chức của bạn.'])->withInput();
        }

        // Check if unit already has active booking deposit
        $hasActiveDeposit = BookingDeposit::where('unit_id', $request->unit_id)
            ->whereIn('payment_status', ['pending', 'paid'])
            ->where('hold_until', '>', now())
            ->whereNull('deleted_at')
            ->exists();

        if ($hasActiveDeposit) {
            return back()->withErrors(['unit_id' => 'Phòng này đã có đặt cọc đang hoạt động.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate reference number
            $referenceNumber = BookingDeposit::generateReferenceNumber();

            // Create booking deposit
            $deposit = BookingDeposit::create([
                'organization_id' => $organizationId,
                'unit_id' => $request->unit_id,
                'tenant_user_id' => $request->tenant_user_id,
                'lead_id' => $request->lead_id,
                'agent_id' => $user->id,
                'amount' => $amount,
                'deposit_type' => $request->deposit_type,
                'payment_status' => 'pending',
                'hold_until' => $request->hold_until,
                'notes' => $request->notes,
                'reference_number' => $referenceNumber,
            ]);

            // Invoice will be created automatically by BookingDepositObserver

            DB::commit();

            return redirect()->route('agent.booking-deposits.show', $deposit->id)
                ->with('success', 'Đặt cọc đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating booking deposit: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo đặt cọc: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get organization ID
        $organizationId = $user->organizations()->first()->id ?? 1;
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            // If no properties assigned, get all active properties for the organization
            $assignedPropertyIds = Property::where('organization_id', $organizationId)
                ->where('status', 1)
                ->pluck('id');
        }

        // Get deposit
        $deposit = BookingDeposit::where('organization_id', $organizationId)
            ->whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })
            ->with(['unit.property', 'tenantUser', 'lead', 'agent', 'organization'])
        ->findOrFail($id);

        return view('agent.booking-deposits.show', compact('deposit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get organization ID
        $organizationId = $user->organizations()->first()->id ?? 1;
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            // If no properties assigned, get all active properties for the organization
            $assignedPropertyIds = Property::where('organization_id', $organizationId)
                ->where('status', 1)
                ->pluck('id');
        }

        // Get deposit
        $deposit = BookingDeposit::where('organization_id', $organizationId)
            ->whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })
            ->with(['unit.property', 'tenantUser', 'lead'])
            ->findOrFail($id);

        // Only allow editing if deposit is pending
        if ($deposit->payment_status !== 'pending') {
            return redirect()->route('agent.booking-deposits.show', $deposit->id)
                ->with('error', 'Chỉ có thể chỉnh sửa đặt cọc đang chờ thanh toán.');
        }
        
        // Get properties with available units only
        $properties = Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->whereHas('units', function($q) {
                $q->where('status', '!=', 'inactive')
                  ->whereDoesntHave('leases', function($leaseQuery) {
                      $leaseQuery->where('status', 'active')->whereNull('deleted_at');
                  })
                  ->whereDoesntHave('bookingDeposits', function($depositQuery) {
                      $depositQuery->whereIn('status', ['pending', 'confirmed']);
                  });
            })
            ->with(['units' => function($q) {
                $q->where('status', '!=', 'inactive')
                  ->whereDoesntHave('leases', function($leaseQuery) {
                      $leaseQuery->where('status', 'active')->whereNull('deleted_at');
                  })
                  ->whereDoesntHave('bookingDeposits', function($depositQuery) {
                      $depositQuery->whereIn('status', ['pending', 'confirmed']);
                  });
            }])
            ->orderBy('name')
            ->get();

        // Get tenant users from organization and default organization
        $defaultOrgs = \App\Models\Organization::where('name', 'Default Organization')
            ->orWhere('code', 'ORG_MAIN')
            ->orWhere('name', 'Tổ chức mặc định')
            ->orWhere('name', 'like', '%Default%')
            ->orWhere('name', 'like', '%Mặc định%')
            ->orWhere('id', 3) // Force include organization with ID = 3
            ->get();
        $agentOrg = $user->organizations()->first();
        
        $tenantOrgIds = collect();
        foreach ($defaultOrgs as $defaultOrg) {
            $tenantOrgIds->push($defaultOrg->id);
        }
        if ($agentOrg) {
            $tenantOrgIds->push($agentOrg->id);
        }
        
        $tenantUsers = User::whereHas('userRoles', function($q) {
            $q->where('key_code', 'tenant');
        })->whereHas('organizations', function($q) use ($tenantOrgIds) {
            $q->whereIn('organizations.id', $tenantOrgIds);
        })->with(['organizations' => function($q) use ($tenantOrgIds) {
            $q->whereIn('organizations.id', $tenantOrgIds);
        }])->orderBy('full_name')->get();

        // Get leads from organization
        $leads = Lead::where('organization_id', $organizationId)
            ->whereIn('status', ['new', 'contacted', 'qualified'])
            ->orderBy('name')
            ->get();

        // Get units for selected property (only available units)
        $selectedPropertyId = $request->filled('property_id') ? $request->property_id : $deposit->unit->property_id;
        $units = Unit::where('property_id', $selectedPropertyId)
            ->where('status', '!=', 'inactive')
            ->whereDoesntHave('leases', function($q) {
                $q->where('status', 'active')->whereNull('deleted_at');
            })
            ->whereDoesntHave('bookingDeposits', function($q) {
                $q->whereIn('status', ['pending', 'confirmed']);
            })
            ->get()
            ->map(function($unit) {
                $unit->has_active_lease = false;
                $unit->has_active_deposit = false;
                return $unit;
            });

        return view('agent.booking-deposits.edit', [
            'deposit' => $deposit,
            'properties' => $properties,
            'tenantUsers' => $tenantUsers,
            'leads' => $leads,
            'units' => $units
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
        // Get organization ID
        $organizationId = $user->organizations()->first()->id ?? 1;
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            // If no properties assigned, get all active properties for the organization
            $assignedPropertyIds = Property::where('organization_id', $organizationId)
                ->where('status', 1)
                ->pluck('id');
        }

        // Get deposit
        $deposit = BookingDeposit::where('organization_id', $organizationId)
            ->whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })
                ->findOrFail($id);

            // Only allow editing if deposit is pending
            if ($deposit->payment_status !== 'pending') {
            return redirect()->route('agent.booking-deposits.show', $deposit->id)
                ->with('error', 'Chỉ có thể chỉnh sửa đặt cọc đang chờ thanh toán.');
        }

        // Clean amount input
        $amount = str_replace(['.', ','], '', $request->amount);

        // Validate request
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'tenant_user_id' => 'nullable|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'amount' => 'required|string|regex:/^[\d.,]+$/',
            'deposit_type' => 'required|in:booking,security,advance',
            'hold_until' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ], [
            'unit_id.required' => 'Vui lòng chọn phòng/căn hộ.',
            'unit_id.exists' => 'Phòng/căn hộ không tồn tại.',
            'tenant_user_id.exists' => 'Người thuê không tồn tại.',
            'lead_id.exists' => 'Lead không tồn tại.',
            'amount.required' => 'Vui lòng nhập số tiền đặt cọc.',
            'amount.regex' => 'Số tiền không hợp lệ.',
            'deposit_type.required' => 'Vui lòng chọn loại đặt cọc.',
            'deposit_type.in' => 'Loại đặt cọc không hợp lệ.',
            'hold_until.required' => 'Vui lòng chọn ngày hết hạn.',
            'hold_until.date' => 'Ngày hết hạn không hợp lệ.',
            'hold_until.after' => 'Ngày hết hạn phải sau thời điểm hiện tại.',
            'notes.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ]);

        // Ensure either tenant_user_id or lead_id is provided
        if (!$request->tenant_user_id && !$request->lead_id) {
            return back()->withErrors(['tenant_user_id' => 'Vui lòng chọn người thuê hoặc lead.'])->withInput();
        }

        // Check if new unit belongs to assigned properties
        $unit = Unit::with('property')->findOrFail($request->unit_id);
        
        if (!$assignedPropertyIds->contains($unit->property_id)) {
            return back()->withErrors(['unit_id' => 'Bạn không có quyền chỉnh sửa đặt cọc cho phòng này.'])->withInput();
        }

        // Check if new unit already has active booking deposit (excluding current deposit)
        if ($request->unit_id != $deposit->unit_id) {
            $hasActiveDeposit = BookingDeposit::where('unit_id', $request->unit_id)
                    ->whereIn('payment_status', ['pending', 'paid'])
                    ->where('hold_until', '>', now())
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->exists();

            if ($hasActiveDeposit) {
                return back()->withErrors(['unit_id' => 'Phòng này đã có đặt cọc đang hoạt động.'])->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Update deposit
            $deposit->update([
                'unit_id' => $request->unit_id,
                'tenant_user_id' => $request->tenant_user_id,
                'lead_id' => $request->lead_id,
                'amount' => $amount,
                'deposit_type' => $request->deposit_type,
                'hold_until' => $request->hold_until,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->route('agent.booking-deposits.show', $deposit->id)
                ->with('success', 'Đặt cọc đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating booking deposit: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật đặt cọc: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
        // Get organization ID
        $organizationId = $user->organizations()->first()->id ?? 1;
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            // If no properties assigned, get all active properties for the organization
            $assignedPropertyIds = Property::where('organization_id', $organizationId)
                ->where('status', 1)
                ->pluck('id');
        }

        // Get deposit
        $deposit = BookingDeposit::where('organization_id', $organizationId)
            ->whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })
                ->findOrFail($id);

            // Only allow deleting if deposit is pending
            if ($deposit->payment_status !== 'pending') {
            return redirect()->route('agent.booking-deposits.show', $deposit->id)
                ->with('error', 'Chỉ có thể xóa đặt cọc đang chờ thanh toán.');
        }

        try {
            DB::beginTransaction();
            
            // Soft delete the deposit
            $deposit->delete();

            DB::commit();

            return redirect()->route('agent.booking-deposits.index')
                ->with('success', 'Đặt cọc đã được xóa thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting booking deposit: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xóa đặt cọc: ' . $e->getMessage());
        }
    }

    /**
     * Get units for a property (AJAX).
     */
    public function getUnits(Request $request)
    {
        // Simple test response first
        if ($request->has('test')) {
            return response()->json([
                'success' => true,
                'message' => 'AJAX endpoint is working!',
                'timestamp' => now()->toDateTimeString()
            ]);
        }

        $propertyId = $request->property_id;
        
        if (!$propertyId) {
            return response()->json(['units' => []]);
        }

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            Log::info('getUnits called', [
                'property_id' => $propertyId,
                'user_id' => $user->id,
                'user_name' => $user->full_name
            ]);
            
            // Check if property is assigned to agent
            $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
            
            // If no properties assigned, get all active properties for the organization
            if ($assignedPropertyIds->isEmpty()) {
                $organizationId = $user->organizations()->first()->id ?? 1;
                $assignedPropertyIds = \App\Models\Property::where('organization_id', $organizationId)
                    ->where('status', 1)
                    ->pluck('id');
            }
            
            Log::info('Assigned properties', [
                'assigned_property_ids' => $assignedPropertyIds->toArray(),
                'requested_property_id' => $propertyId
            ]);
            
            if (!$assignedPropertyIds->contains($propertyId)) {
                Log::warning('User ' . $user->id . ' tried to access property ' . $propertyId . ' without permission');
                return response()->json(['error' => 'Bạn không có quyền truy cập bất động sản này'], 403);
            }

            $units = Unit::where('property_id', $propertyId)
                ->get()
                ->map(function ($unit) {
                    $hasActiveLease = \App\Models\Lease::where('unit_id', $unit->id)
                        ->where('status', 'active')
                        ->whereNull('deleted_at')
                        ->exists();
                    
                    $hasActiveDeposit = BookingDeposit::where('unit_id', $unit->id)
                        ->whereIn('payment_status', ['pending', 'paid'])
                        ->where('hold_until', '>', now())
                        ->whereNull('deleted_at')
                        ->exists();
                    
                    $unit->has_active_lease = $hasActiveLease;
                    $unit->has_active_deposit = $hasActiveDeposit;
                    return $unit;
                });

            Log::info('Found ' . $units->count() . ' units for property ' . $propertyId, [
                'units' => $units->map(function($unit) {
                    return [
                        'id' => $unit->id,
                        'code' => $unit->code,
                        'has_active_lease' => $unit->has_active_lease,
                        'has_active_deposit' => $unit->has_active_deposit
                    ];
                })->toArray()
            ]);
            
            return response()->json($units);
        } catch (\Exception $e) {
            Log::error('Error in getUnits: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Có lỗi xảy ra khi tải dữ liệu phòng: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mark deposit as paid.
     */
    public function markAsPaid($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get organization ID
        $organizationId = $user->organizations()->first()->id ?? 1;
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            // If no properties assigned, get all active properties for the organization
            $assignedPropertyIds = Property::where('organization_id', $organizationId)
                ->where('status', 1)
                ->pluck('id');
        }

        // Get deposit
        $deposit = BookingDeposit::where('organization_id', $organizationId)
            ->whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })
            ->findOrFail($id);

        // Only allow marking as paid if deposit is pending
        if ($deposit->payment_status !== 'pending') {
            return redirect()->route('agent.booking-deposits.show', $deposit->id)
                ->with('error', 'Chỉ có thể đánh dấu thanh toán cho đặt cọc đang chờ thanh toán.');
        }

        try {
            DB::beginTransaction();

            $deposit->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('agent.booking-deposits.show', $deposit->id)
                ->with('success', 'Đặt cọc đã được đánh dấu là đã thanh toán!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking deposit as paid: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi đánh dấu thanh toán: ' . $e->getMessage());
        }
    }

    /**
     * Cancel deposit.
     */
    public function cancel($id)
    {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
        // Get organization ID
        $organizationId = $user->organizations()->first()->id ?? 1;
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            // If no properties assigned, get all active properties for the organization
            $assignedPropertyIds = Property::where('organization_id', $organizationId)
                ->where('status', 1)
                ->pluck('id');
        }

        // Get deposit
        $deposit = BookingDeposit::where('organization_id', $organizationId)
            ->whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })
                ->findOrFail($id);

            // Only allow cancelling if deposit is pending
            if ($deposit->payment_status !== 'pending') {
            return redirect()->route('agent.booking-deposits.show', $deposit->id)
                ->with('error', 'Chỉ có thể hủy đặt cọc đang chờ thanh toán.');
        }

        try {
            DB::beginTransaction();
            
            $deposit->update([
                'payment_status' => 'cancelled',
            ]);

            DB::commit();

            return redirect()->route('agent.booking-deposits.show', $deposit->id)
                ->with('success', 'Đặt cọc đã được hủy!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling deposit: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi hủy đặt cọc: ' . $e->getMessage());
        }
    }


}
