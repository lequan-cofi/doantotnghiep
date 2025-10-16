<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentedController extends Controller
{
    /**
     * Display a listing of rented units.
     */
    public function index(Request $request)
    {
        $agent = Auth::user();
        
        // Get properties assigned to this agent
        $propertyIds = $agent->assignedProperties ? $agent->assignedProperties->pluck('id')->toArray() : [];
        
        // If no properties assigned, return empty result
        if (empty($propertyIds)) {
            $rentedUnits = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $properties = collect();
            return view('agent.rented.index', compact('rentedUnits', 'properties', 'request'));
        }
        
        // Base query for rented units
        $query = Unit::whereIn('property_id', $propertyIds)
            ->where('status', 'occupied')
            ->with([
                'property.location',
                'property.location2025',
                'leases.tenant.userProfile',
                'leases.residents',
                'amenities'
            ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('property', function($propertyQuery) use ($search) {
                      $propertyQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('leases.tenant', function($tenantQuery) use ($search) {
                      $tenantQuery->where('full_name', 'like', "%{$search}%")
                                  ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by property
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter by lease status
        if ($request->filled('lease_status')) {
            $query->whereHas('leases', function($leaseQuery) use ($request) {
                $leaseQuery->where('status', $request->lease_status);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'tenant_name') {
            $query->join('leases', 'units.id', '=', 'leases.unit_id')
                  ->join('users', 'leases.tenant_id', '=', 'users.id')
                  ->orderBy('users.full_name', $sortOrder)
                  ->select('units.*');
        } elseif ($sortBy === 'lease_start_date') {
            $query->join('leases', 'units.id', '=', 'leases.unit_id')
                  ->orderBy('leases.start_date', $sortOrder)
                  ->select('units.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $rentedUnits = $query->paginate(15)->withQueryString();

        // Get properties for filter dropdown
        $properties = Property::whereIn('id', $propertyIds)
            ->orderBy('name')
            ->get();

        return view('agent.rented.index', compact(
            'rentedUnits',
            'properties',
            'request'
        ));
    }

    /**
     * Display the specified rented unit with full details.
     */
    public function show($id)
    {
        $agent = Auth::user();
        
        // Get properties assigned to this agent
        $propertyIds = $agent->assignedProperties ? $agent->assignedProperties->pluck('id')->toArray() : [];
        
        // If no properties assigned, return 404
        if (empty($propertyIds)) {
            abort(404, 'Không tìm thấy bất động sản được gán cho agent này.');
        }
        
        $unit = Unit::whereIn('property_id', $propertyIds)
            ->where('id', $id)
            ->where('status', 'occupied')
            ->with([
                'property.location',
                'property.location2025',
                'leases.tenant.userProfile',
                'leases.residents',
                'leases.leaseServices',
                'amenities',
                'leases' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->firstOrFail();

        // Get lease history
        $leaseHistory = $unit->leases()
            ->with(['tenant', 'residents'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recent payments from tenant
        $currentLease = $unit->current_lease;
        $recentPayments = $currentLease && $currentLease->tenant
            ? $currentLease->tenant->payments()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
            : collect();

        return view('agent.rented.show', compact(
            'unit',
            'leaseHistory',
            'recentPayments'
        ));
    }

    /**
     * Get lease details for AJAX request.
     */
    public function getLeaseDetails($leaseId)
    {
        $agent = Auth::user();
        $propertyIds = $agent->assignedProperties ? $agent->assignedProperties->pluck('id')->toArray() : [];
        
        $lease = Lease::whereIn('property_id', $propertyIds)
            ->with([
                'tenant.userProfile',
                'unit.property.location',
                'unit.property.location2025',
                'residents',
                'leaseServices'
            ])
            ->findOrFail($leaseId);

        return response()->json([
            'success' => true,
            'lease' => $lease
        ]);
    }

    /**
     * Get tenant profile for AJAX request.
     */
    public function getTenantProfile($tenantId)
    {
        $agent = Auth::user();
        $propertyIds = $agent->assignedProperties ? $agent->assignedProperties->pluck('id')->toArray() : [];
        
        $tenant = \App\Models\User::whereHas('leasesAsTenant.unit', function($query) use ($propertyIds) {
                $query->whereIn('property_id', $propertyIds);
            })
            ->with([
                'userProfile',
                'leasesAsTenant.unit.property.location',
                'leasesAsTenant.unit.property.location2025',
                'leasesAsTenant' => function($query) use ($propertyIds) {
                    $query->whereHas('unit', function($unitQuery) use ($propertyIds) {
                        $unitQuery->whereIn('property_id', $propertyIds);
                    });
                },
                'payments' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(10);
                }
            ])
            ->findOrFail($tenantId);

        return response()->json([
            'success' => true,
            'tenant' => $tenant
        ]);
    }
}