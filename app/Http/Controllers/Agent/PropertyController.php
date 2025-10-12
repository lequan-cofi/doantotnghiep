<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties assigned to the agent.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy các properties được gán cho agent này
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            return view('agent.properties.index', [
                'properties' => collect(),
                'propertyTypes' => collect()
            ]);
        }

        // Query properties với filter
        $query = Property::whereIn('id', $assignedPropertyIds)
            ->with([
                'propertyType', 
                'owner',
                'location' => function($q) {
                    $q->with(['province', 'district', 'ward']);
                },
                'location2025' => function($q) {
                    $q->with(['province', 'ward']);
                },
                'units' => function($q) {
                    $q->with(['leases' => function($leaseQuery) {
                        $leaseQuery->where('status', 'active')->whereNull('deleted_at');
                    }]);
                }
            ]);

        // Filter by search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by property type
        if ($request->filled('property_type_id')) {
            $query->where('property_type_id', $request->property_type_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get properties with sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Validate sort fields
        $allowedSortFields = ['name', 'created_at', 'total_rooms', 'total_floors'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';
        }
        
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }
        
        $properties = $query->orderBy($sortBy, $sortOrder)->paginate(12);

        // Lấy danh sách property types để hiển thị trong filter
        $propertyTypes = PropertyType::orderBy('name')->get();

        // Thêm thông tin thống kê cho mỗi property
        $properties->getCollection()->each(function ($property) {
            $property->total_units = $property->getTotalUnitsCount();
            $property->occupied_units = $property->getOccupiedUnitsCount();
            $property->available_units = $property->getAvailableUnitsCount();
            $property->occupancy_rate = $property->getOccupancyRate();
        });

        return view('agent.properties.index', [
            'properties' => $properties,
            'propertyTypes' => $propertyTypes,
            'search' => $request->search,
            'selectedPropertyType' => $request->property_type_id,
            'selectedStatus' => $request->status
        ]);
    }

    /**
     * Display the specified property.
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy property và kiểm tra quyền truy cập
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        $property = Property::whereIn('id', $assignedPropertyIds)
            ->with([
                'propertyType', 
                'owner',
                'location' => function($q) {
                    $q->with(['province', 'district', 'ward']);
                },
                'location2025' => function($q) {
                    $q->with(['province', 'ward']);
                },
                'units' => function($q) {
                    $q->with(['leases' => function($leaseQuery) {
                        $leaseQuery->where('status', 'active')->whereNull('deleted_at');
                    }]);
                }
            ])
            ->findOrFail($id);

        return view('agent.properties.show', compact('property'));
    }
}