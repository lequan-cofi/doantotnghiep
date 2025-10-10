<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the agent dashboard.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy các properties được gán cho agent này
        $assignedProperties = $user->assignedProperties()
            ->with(['location', 'location2025', 'units' => function($query) {
                $query->with(['leases' => function($leaseQuery) {
                    $leaseQuery->where('status', 'active')
                        ->whereNull('deleted_at')
                        ->with('tenant');
                }]);
            }])
            ->where('properties.status', 1)
            ->get();

        // Tính toán thống kê
        $totalUnits = 0;
        $availableUnits = 0;
        $occupiedUnits = 0;
        $activeLeases = 0;
        $monthlyRevenue = 0;

        foreach ($assignedProperties as $property) {
            $totalUnits += $property->units->count();
            $availableUnits += $property->units->where('status', 'available')->count();
            
            foreach ($property->units as $unit) {
                if ($unit->leases->count() > 0) {
                    $occupiedUnits++;
                }
                $activeLeases += $unit->leases->count();
                $monthlyRevenue += $unit->leases->sum('rent_amount');
            }
        }

        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

        // Lấy các hợp đồng gần đây
        $recentLeases = Lease::whereHas('unit', function($query) use ($assignedProperties) {
            $query->whereIn('property_id', $assignedProperties->pluck('id'));
        })
        ->with(['unit.property', 'tenant'])
        ->where('status', 'active')
        ->whereNull('deleted_at')
        ->latest()
        ->limit(10)
        ->get();

        return view('agent.dashboard', compact(
            'assignedProperties',
            'totalUnits',
            'availableUnits',
            'occupiedUnits',
            'activeLeases',
            'monthlyRevenue',
            'occupancyRate',
            'recentLeases'
        ));
    }
}
