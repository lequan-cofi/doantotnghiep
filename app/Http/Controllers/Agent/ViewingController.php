<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Viewing;
use App\Models\Unit;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViewingController extends Controller
{
    /**
     * Display a listing of viewings for the agent.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Query viewings for assigned properties
        $query = Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
            $q->whereIn('property_id', $assignedPropertyIds);
        })->with(['unit.property', 'tenant']);

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

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
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

        // Get viewings with sorting
        $sortBy = $request->get('sort_by', 'schedule_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort fields
        $allowedSortFields = ['id', 'created_at', 'schedule_at', 'status'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'schedule_at';
        }
        
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        $viewings = $query->orderBy($sortBy, $sortOrder)->get();

        // Get assigned properties for filter
        $properties = Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return view('agent.viewings.index', [
            'viewings' => $viewings,
            'properties' => $properties,
            'selectedProperty' => $request->property_id,
            'selectedStatus' => $request->status,
            'search' => $request->search
        ]);
    }

    /**
     * Display today's viewings for the agent.
     */
    public function today()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get today's viewings
        $viewings = Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
            $q->whereIn('property_id', $assignedPropertyIds);
        })
        ->whereDate('schedule_at', today())
        ->with(['unit.property', 'tenant'])
        ->orderBy('schedule_at')
        ->get();

        return view('agent.viewings.today', compact('viewings'));
    }

    /**
     * Display calendar view of viewings.
     */
    public function calendar()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get viewings for calendar
        $viewings = Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
            $q->whereIn('property_id', $assignedPropertyIds);
        })
        ->with(['unit.property', 'tenant'])
        ->where('schedule_at', '>=', now()->startOfMonth())
        ->where('schedule_at', '<=', now()->endOfMonth())
        ->get();

        return view('agent.viewings.calendar', compact('viewings'));
    }

    /**
     * Display viewing statistics.
     */
    public function statistics()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get statistics
        $stats = [
            'total_viewings' => Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })->count(),
            'confirmed_viewings' => Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })->where('status', 'confirmed')->count(),
            'pending_viewings' => Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })->where('status', 'pending')->count(),
            'cancelled_viewings' => Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })->where('status', 'cancelled')->count(),
            'today_viewings' => Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })->whereDate('schedule_at', today())->count(),
            'this_week_viewings' => Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })->whereBetween('schedule_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        // Get monthly statistics
        $monthlyStats = Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
            $q->whereIn('property_id', $assignedPropertyIds);
        })
        ->selectRaw('DATE(schedule_at) as date, COUNT(*) as count')
        ->where('schedule_at', '>=', now()->startOfMonth())
        ->where('schedule_at', '<=', now()->endOfMonth())
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return view('agent.viewings.statistics', compact('stats', 'monthlyStats'));
    }

    /**
     * Display the specified viewing.
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get viewing
        $viewing = Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
            $q->whereIn('property_id', $assignedPropertyIds);
        })
        ->with(['unit.property', 'tenant'])
        ->findOrFail($id);

        return view('agent.viewings.show', compact('viewing'));
    }

    /**
     * Confirm a viewing.
     */
    public function confirm($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get viewing
        $viewing = Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
            $q->whereIn('property_id', $assignedPropertyIds);
        })->findOrFail($id);

        $viewing->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $user->id
        ]);

        return redirect()->back()->with('success', 'Lịch hẹn đã được xác nhận thành công!');
    }

    /**
     * Cancel a viewing.
     */
    public function cancel($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get viewing
        $viewing = Viewing::whereHas('unit', function($q) use ($assignedPropertyIds) {
            $q->whereIn('property_id', $assignedPropertyIds);
        })->findOrFail($id);

        $viewing->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $user->id
        ]);

        return redirect()->back()->with('success', 'Lịch hẹn đã được hủy thành công!');
    }
}