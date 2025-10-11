<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * Display a listing of the properties assigned to the agent.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy các properties được gán cho agent này
        $properties = $user->assignedProperties()
            ->with(['location', 'location2025', 'propertyType', 'owner', 'units' => function($query) {
                $query->with(['leases' => function($leaseQuery) {
                    $leaseQuery->where('status', 'active')
                        ->whereNull('deleted_at')
                        ->with('tenant');
                }]);
            }])
            ->where('properties.status', 1)
            ->get();

        // Thêm thống kê cho mỗi property
        $properties->each(function ($property) {
            $property->total_units = $property->units->count();
            $property->available_units = $property->units->filter(function($unit) {
                return $unit->leases->count() == 0;
            })->count();
            $property->occupied_units = $property->units->filter(function($unit) {
                return $unit->leases->count() > 0;
            })->count();
            $property->active_leases = $property->units->sum(function($unit) {
                return $unit->leases->count();
            });
            $property->monthly_revenue = $property->units->sum(function($unit) {
                return $unit->leases->sum('rent_amount');
            });
            $property->occupancy_rate = $property->total_units > 0 ? 
                round(($property->occupied_units / $property->total_units) * 100, 1) : 0;
        });

        return view('agent.properties.index', compact('properties'));
    }

    /**
     * Display the specified property.
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Kiểm tra xem property có được gán cho agent này không
        $property = $user->assignedProperties()
            ->with(['location', 'location2025', 'propertyType', 'owner', 'units' => function($query) {
                $query->with(['leases' => function($leaseQuery) {
                    $leaseQuery->where('status', 'active')
                        ->whereNull('deleted_at')
                        ->with('tenant');
                }]);
            }])
            ->where('properties.id', $id)
            ->where('properties.status', 1)
            ->firstOrFail();

        // Thống kê chi tiết
        $stats = [
            'total_units' => $property->units->count(),
            'available_units' => $property->units->filter(function($unit) {
                return $unit->leases->count() == 0;
            })->count(),
            'occupied_units' => $property->units->filter(function($unit) {
                return $unit->leases->count() > 0;
            })->count(),
            'active_leases' => $property->units->sum(function($unit) {
                return $unit->leases->count();
            }),
            'monthly_revenue' => $property->units->sum(function($unit) {
                return $unit->leases->sum('rent_amount');
            }),
        ];
        
        $stats['occupancy_rate'] = $stats['total_units'] > 0 ? 
            round(($stats['occupied_units'] / $stats['total_units']) * 100, 1) : 0;

        // Lấy danh sách units với thông tin chi tiết
        $units = $property->units->map(function($unit) {
            $unit->current_lease = $unit->leases->first();
            $unit->is_available = $unit->leases->count() == 0;
            return $unit;
        });

        return view('agent.properties.show', compact('property', 'stats', 'units'));
    }
}
