<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Unit;
use App\Models\User;
// use App\Models\Property; // Commented out to use fully qualified name
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaseController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Start with basic query - temporarily disable organization scope for debugging
            $query = Lease::withoutGlobalScope('organization')->with([
                'unit.property', 
                'tenant', 
                'agent', 
                'organization',
                'leaseServices.service'
            ]);

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
                      })
                      ->orWhereHas('agent', function($agentQuery) use ($search) {
                          $agentQuery->where('full_name', 'like', "%{$search}%");
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

            // Filter by tenant
            if ($request->filled('tenant_id')) {
                $query->where('tenant_id', $request->tenant_id);
            }

            // Filter by agent
            if ($request->filled('agent_id')) {
                $query->where('agent_id', $request->agent_id);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('start_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('end_date', '<=', $request->date_to);
            }

            $leases = $query->orderBy('created_at', 'desc')->paginate(10);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading leases: ' . $e->getMessage());
            $leases = \App\Models\Lease::query()->paginate(10);
        }

        // Get filter data - ensure variables are always defined
        $properties = collect();
        $tenants = collect();
        $agents = collect();
        
        try {
            $properties = \App\Models\Property::all();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading properties: ' . $e->getMessage());
        }
        
        try {
            $tenants = User::whereHas('userRoles', function($q) {
                $q->where('key_code', 'tenant');
            })->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading tenants: ' . $e->getMessage());
        }
        
        try {
            $agents = User::whereHas('userRoles', function($q) {
                $q->whereIn('key_code', ['agent', 'manager']);
            })->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading agents: ' . $e->getMessage());
        }

        return view('manager.leases.index', [
            'leases' => $leases,
            'properties' => $properties,
            'tenants' => $tenants,
            'agents' => $agents
        ]);
    }

    public function create()
    {
        // Ensure variables are always defined
        $properties = collect();
        $tenants = collect();
        $agents = collect();
        $services = collect();
        
        try {
            $properties = \App\Models\Property::all();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading properties in create: ' . $e->getMessage());
        }
        
        try {
            $tenants = User::whereHas('userRoles', function($q) {
                $q->where('key_code', 'tenant');
            })->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading tenants in create: ' . $e->getMessage());
        }
        
        try {
            $agents = User::whereHas('userRoles', function($q) {
                $q->whereIn('key_code', ['agent', 'manager']);
            })->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading agents in create: ' . $e->getMessage());
        }
        
        try {
            $services = Service::all();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading services in create: ' . $e->getMessage());
        }

        return view('manager.leases.create', [
            'properties' => $properties,
            'tenants' => $tenants,
            'agents' => $agents,
            'services' => $services
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'unit_id' => 'required|exists:units,id',
                'tenant_id' => 'required|exists:users,id',
                'agent_id' => 'nullable|exists:users,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'rent_amount' => 'required|numeric|min:0',
                'deposit_amount' => 'nullable|numeric|min:0',
                'billing_day' => 'nullable|integer|min:1|max:28',
                'status' => 'required|in:draft,active,terminated,expired',
                'contract_no' => 'nullable|string|max:100|unique:leases,contract_no',
                'signed_at' => 'nullable|date',
                'services' => 'nullable|array',
                'services.*.service_id' => 'required_with:services|exists:services,id',
                'services.*.price' => 'required_with:services|numeric|min:0',
            ]);

            DB::beginTransaction();

            // Get organization from current user
            $currentUser = Auth::user();
            $organization = \App\Models\OrganizationUser::where('user_id', $currentUser->id)->first()?->organization;

            // Create lease
            $lease = Lease::create([
                'organization_id' => $organization?->id,
                'unit_id' => $validated['unit_id'],
                'tenant_id' => $validated['tenant_id'],
                'agent_id' => $validated['agent_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'rent_amount' => $validated['rent_amount'],
                'deposit_amount' => $validated['deposit_amount'] ?? 0,
                'billing_day' => $validated['billing_day'] ?? 1,
                'status' => $validated['status'],
                'contract_no' => $validated['contract_no'],
                'signed_at' => $validated['signed_at'],
            ]);

            // Add services if provided
            if (!empty($validated['services'])) {
                foreach ($validated['services'] as $serviceData) {
                    $lease->leaseServices()->create([
                        'service_id' => $serviceData['service_id'],
                        'price' => $serviceData['price'],
                    ]);
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hợp đồng đã được tạo thành công!',
                    'redirect' => route('manager.leases.show', $lease->id)
                ]);
            }

            return redirect()->route('manager.leases.show', $lease->id)
                ->with('success', 'Hợp đồng đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo hợp đồng: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo hợp đồng: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $lease = Lease::withoutGlobalScope('organization')->with([
            'unit.property.propertyType',
            'unit.property.location',
            'unit.property.location2025',
            'tenant',
            'agent',
            'organization',
            'leaseServices.service',
            'residents'
        ])->findOrFail($id);

        return view('manager.leases.show', compact('lease'));
    }

    public function edit($id)
    {
        $lease = Lease::withoutGlobalScope('organization')->with([
            'unit.property',
            'leaseServices.service'
        ])->findOrFail($id);

        // Ensure variables are always defined
        $properties = collect();
        $tenants = collect();
        $agents = collect();
        $services = collect();
        
        try {
            $properties = \App\Models\Property::all();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading properties in edit: ' . $e->getMessage());
        }
        
        try {
            $tenants = User::whereHas('userRoles', function($q) {
                $q->where('key_code', 'tenant');
            })->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading tenants in edit: ' . $e->getMessage());
        }
        
        try {
            $agents = User::whereHas('userRoles', function($q) {
                $q->whereIn('key_code', ['agent', 'manager']);
            })->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading agents in edit: ' . $e->getMessage());
        }
        
        try {
            $services = Service::all();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading services in edit: ' . $e->getMessage());
        }

        // Get units for selected property
        $units = Unit::where('property_id', $lease->unit->property_id)->get();

        return view('manager.leases.edit', [
            'lease' => $lease,
            'properties' => $properties,
            'tenants' => $tenants,
            'agents' => $agents,
            'services' => $services,
            'units' => $units
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $lease = Lease::withoutGlobalScope('organization')->findOrFail($id);

            $validated = $request->validate([
                'unit_id' => 'required|exists:units,id',
                'tenant_id' => 'required|exists:users,id',
                'agent_id' => 'nullable|exists:users,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'rent_amount' => 'required|numeric|min:0',
                'deposit_amount' => 'nullable|numeric|min:0',
                'billing_day' => 'nullable|integer|min:1|max:28',
                'status' => 'required|in:draft,active,terminated,expired',
                'contract_no' => 'nullable|string|max:100|unique:leases,contract_no,' . $id,
                'signed_at' => 'nullable|date',
                'services' => 'nullable|array',
                'services.*.service_id' => 'required_with:services|exists:services,id',
                'services.*.price' => 'required_with:services|numeric|min:0',
            ]);

            DB::beginTransaction();

            // Update lease
            $lease->update([
                'unit_id' => $validated['unit_id'],
                'tenant_id' => $validated['tenant_id'],
                'agent_id' => $validated['agent_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'rent_amount' => $validated['rent_amount'],
                'deposit_amount' => $validated['deposit_amount'] ?? 0,
                'billing_day' => $validated['billing_day'] ?? 1,
                'status' => $validated['status'],
                'contract_no' => $validated['contract_no'],
                'signed_at' => $validated['signed_at'],
            ]);

            // Update services
            $lease->leaseServices()->delete();
            if (!empty($validated['services'])) {
                foreach ($validated['services'] as $serviceData) {
                    $lease->leaseServices()->create([
                        'service_id' => $serviceData['service_id'],
                        'price' => $serviceData['price'],
                    ]);
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hợp đồng đã được cập nhật thành công!',
                    'redirect' => route('manager.leases.show', $lease->id)
                ]);
            }

            return redirect()->route('manager.leases.show', $lease->id)
                ->with('success', 'Hợp đồng đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật hợp đồng: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật hợp đồng: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $lease = Lease::withoutGlobalScope('organization')->findOrFail($id);
            
            // Soft delete the lease (trait sẽ tự động set deleted_by)
            $lease->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hợp đồng đã được xóa thành công!'
                ]);
            }

            return redirect()->route('manager.leases.index')
                ->with('success', 'Hợp đồng đã được xóa thành công!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting lease: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa hợp đồng: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi xóa hợp đồng: ' . $e->getMessage());
        }
    }

    // API method to get units for a property
    public function getUnits($propertyId)
    {
        $units = Unit::where('property_id', $propertyId)
            ->where('status', 'available')
            ->get();

        return response()->json($units);
    }
}