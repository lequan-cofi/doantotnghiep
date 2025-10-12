<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\Viewing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the agent dashboard.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get statistics
        $stats = [
            'total_properties' => $assignedPropertyIds->count(),
            'total_units' => Unit::whereIn('property_id', $assignedPropertyIds)->count(),
            'available_units' => Unit::whereIn('property_id', $assignedPropertyIds)
                ->where('status', 'available')
                ->count(),
            'occupied_units' => Unit::whereIn('property_id', $assignedPropertyIds)
                ->where('status', 'occupied')
                ->count(),
            'active_leases' => Lease::where('agent_id', $user->id)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->count(),
            'total_viewings' => Viewing::where('agent_id', $user->id)->count(),
            'today_viewings' => Viewing::where('agent_id', $user->id)
                ->whereDate('schedule_at', today())
                ->count(),
        ];

        // Get recent activities
        $recentLeases = Lease::where('agent_id', $user->id)
            ->with(['unit.property', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentViewings = Viewing::where('agent_id', $user->id)
            ->with(['unit.property', 'tenant'])
            ->orderBy('schedule_at', 'desc')
            ->limit(5)
            ->get();

        // Get properties with their stats
        $properties = Property::whereIn('id', $assignedPropertyIds)
            ->with(['units' => function($query) {
                $query->with(['leases' => function($leaseQuery) {
                    $leaseQuery->where('status', 'active')->whereNull('deleted_at');
                }]);
            }])
            ->where('status', 1)
            ->get();

        // Add stats to each property
        $properties->each(function ($property) {
            $property->total_units = $property->units->count();
            $property->available_units = $property->units->where('status', 'available')->count();
            $property->occupied_units = $property->units->where('status', 'occupied')->count();
            $property->active_leases = $property->units->sum(function($unit) {
                return $unit->leases->count();
            });
            $property->occupancy_rate = $property->total_units > 0 ? 
                round(($property->occupied_units / $property->total_units) * 100, 1) : 0;
        });

        return view('agent.dashboard', compact('stats', 'recentLeases', 'recentViewings', 'properties'));
    }
}
