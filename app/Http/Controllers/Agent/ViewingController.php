<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Viewing;
use App\Models\Unit;
use App\Models\Property;
use App\Models\User;
use App\Models\Lead;
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
        $query = Viewing::whereIn('property_id', $assignedPropertyIds)
            ->with(['property', 'unit', 'agent', 'organization', 'tenant']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by property
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhere('lead_name', 'like', "%{$search}%")
                  ->orWhere('lead_phone', 'like', "%{$search}%")
                  ->orWhere('lead_email', 'like', "%{$search}%")
                  ->orWhereHas('property', function($propertyQuery) use ($search) {
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
        $viewings = Viewing::whereIn('property_id', $assignedPropertyIds)
        ->whereDate('schedule_at', today())
        ->with(['property', 'unit', 'agent', 'organization', 'tenant'])
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
        $viewings = Viewing::whereIn('property_id', $assignedPropertyIds)
        ->with(['property', 'unit', 'agent', 'organization', 'tenant'])
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
            'total_viewings' => Viewing::whereIn('property_id', $assignedPropertyIds)->count(),
            'confirmed_viewings' => Viewing::whereIn('property_id', $assignedPropertyIds)->where('status', 'confirmed')->count(),
            'requested_viewings' => Viewing::whereIn('property_id', $assignedPropertyIds)->where('status', 'requested')->count(),
            'done_viewings' => Viewing::whereIn('property_id', $assignedPropertyIds)->where('status', 'done')->count(),
            'cancelled_viewings' => Viewing::whereIn('property_id', $assignedPropertyIds)->where('status', 'cancelled')->count(),
            'today_viewings' => Viewing::whereIn('property_id', $assignedPropertyIds)->whereDate('schedule_at', today())->count(),
            'this_week_viewings' => Viewing::whereIn('property_id', $assignedPropertyIds)->whereBetween('schedule_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        // Get monthly statistics
        $monthlyStats = Viewing::whereIn('property_id', $assignedPropertyIds)
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
        $viewing = Viewing::whereIn('property_id', $assignedPropertyIds)
        ->with(['property', 'unit', 'agent', 'organization', 'tenant'])
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
        $viewing = Viewing::whereIn('property_id', $assignedPropertyIds)->findOrFail($id);

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
        $viewing = Viewing::whereIn('property_id', $assignedPropertyIds)->findOrFail($id);

        $viewing->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $user->id
        ]);

        return redirect()->back()->with('success', 'Lịch hẹn đã được hủy thành công!');
    }

    /**
     * Mark a viewing as done.
     */
    public function markDone(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get viewing
        $viewing = Viewing::whereIn('property_id', $assignedPropertyIds)->findOrFail($id);

        $request->validate([
            'result_note' => 'nullable|string|max:1000',
        ]);

        $viewing->update([
            'status' => 'done',
            'result_note' => $request->result_note,
            'completed_at' => now(),
            'completed_by' => $user->id
        ]);

        return redirect()->back()->with('success', 'Lịch hẹn đã được đánh dấu hoàn thành!');
    }

    /**
     * Show the form for creating a new viewing.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get properties for dropdown
        $properties = Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        // Get user's organization
        $userOrganization = $user->organizations()->first();
        $organizationId = $userOrganization ? $userOrganization->id : null;

        // Get leads for dropdown
        $leads = Lead::where('organization_id', $organizationId)
            ->where('status', 'new')
            ->orderBy('name')
            ->get();

        // Get tenants (users with tenant role) for dropdown
        $tenants = User::whereHas('organizationUsers', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId)
                      ->whereHas('role', function($roleQuery) {
                          $roleQuery->where('key_code', 'tenant');
                      });
            })
            ->orderBy('full_name')
            ->get();

        return view('agent.viewings.create', compact('properties', 'leads', 'tenants'));
    }

    /**
     * Store a newly created viewing in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        $request->validate([
            'customer_type' => 'required|in:lead,tenant',
            'tenant_id' => 'required_if:customer_type,tenant|nullable|exists:users,id',
            'lead_id' => 'required_if:customer_type,lead|nullable|exists:leads,id',
            'lead_name' => 'required_if:customer_type,lead|nullable|string|max:255',
            'lead_phone' => 'required_if:customer_type,lead|nullable|string|max:255',
            'lead_email' => 'nullable|email|max:255',
            'property_id' => 'required|exists:properties,id|in:' . $assignedPropertyIds->implode(','),
            'unit_id' => 'required|exists:units,id',
            'schedule_at' => 'required|date|after:now',
            'status' => 'required|in:requested,confirmed',
            'note' => 'nullable|string|max:1000',
        ]);

        // Verify unit belongs to selected property
        $unit = Unit::where('id', $request->unit_id)
            ->where('property_id', $request->property_id)
            ->firstOrFail();

        // Get property to get organization_id
        $property = Property::findOrFail($request->property_id);

        $viewingData = [
            'property_id' => $request->property_id,
            'unit_id' => $request->unit_id,
            'agent_id' => $user->id,
            'organization_id' => $property->organization_id,
            'schedule_at' => $request->schedule_at,
            'status' => $request->status,
            'note' => $request->note,
        ];

        if ($request->customer_type === 'tenant') {
            $viewingData['tenant_id'] = $request->tenant_id;
        } else {
            $viewingData['lead_id'] = $request->lead_id;
            $viewingData['lead_name'] = $request->lead_name;
            $viewingData['lead_phone'] = $request->lead_phone;
            $viewingData['lead_email'] = $request->lead_email;
        }

        Viewing::create($viewingData);

        return redirect()->route('agent.viewings.index')
            ->with('success', 'Lịch hẹn đã được tạo thành công!');
    }

    /**
     * Get units for a property (AJAX endpoint).
     */
    public function getUnits(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        $request->validate([
            'property_id' => 'required|exists:properties,id|in:' . $assignedPropertyIds->implode(',')
        ]);

        $units = Unit::where('property_id', $request->property_id)
            ->where('status', 'available')
            ->orderBy('code')
            ->get(['id', 'code']);

        return response()->json($units);
    }

    /**
     * Show the form for editing the specified viewing.
     */
    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get viewing
        $viewing = Viewing::whereIn('property_id', $assignedPropertyIds)
            ->with(['property', 'unit', 'agent', 'organization', 'tenant', 'lead'])
            ->findOrFail($id);

        // Get properties for dropdown
        $properties = Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        // Get user's organization
        $userOrganization = $user->organizations()->first();
        $organizationId = $userOrganization ? $userOrganization->id : null;

        // Get leads for dropdown
        $leads = Lead::where('organization_id', $organizationId)
            ->where('status', 'new')
            ->orderBy('name')
            ->get();

        // Get tenants (users with tenant role) for dropdown
        $tenants = User::whereHas('organizationUsers', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId)
                      ->whereHas('role', function($roleQuery) {
                          $roleQuery->where('key_code', 'tenant');
                      });
            })
            ->orderBy('full_name')
            ->get();

        return view('agent.viewings.edit', compact('viewing', 'properties', 'leads', 'tenants'));
    }

    /**
     * Update the specified viewing in storage.
     */
    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get viewing
        $viewing = Viewing::whereIn('property_id', $assignedPropertyIds)->findOrFail($id);
        
        $request->validate([
            'customer_type' => 'required|in:lead,tenant',
            'tenant_id' => 'required_if:customer_type,tenant|nullable|exists:users,id',
            'lead_id' => 'required_if:customer_type,lead|nullable|exists:leads,id',
            'lead_name' => 'required_if:customer_type,lead|nullable|string|max:255',
            'lead_phone' => 'required_if:customer_type,lead|nullable|string|max:255',
            'lead_email' => 'nullable|email|max:255',
            'property_id' => 'required|exists:properties,id|in:' . $assignedPropertyIds->implode(','),
            'unit_id' => 'required|exists:units,id',
            'schedule_at' => 'required|date',
            'status' => 'required|in:requested,confirmed,done,no_show,cancelled',
            'note' => 'nullable|string|max:1000',
        ]);

        // Verify unit belongs to selected property
        $unit = Unit::where('id', $request->unit_id)
            ->where('property_id', $request->property_id)
            ->firstOrFail();

        // Get property to get organization_id
        $property = Property::findOrFail($request->property_id);

        $viewingData = [
            'property_id' => $request->property_id,
            'unit_id' => $request->unit_id,
            'organization_id' => $property->organization_id,
            'schedule_at' => $request->schedule_at,
            'status' => $request->status,
            'note' => $request->note,
        ];

        if ($request->customer_type === 'tenant') {
            $viewingData['tenant_id'] = $request->tenant_id;
            $viewingData['lead_id'] = null;
            $viewingData['lead_name'] = null;
            $viewingData['lead_phone'] = null;
            $viewingData['lead_email'] = null;
        } else {
            $viewingData['lead_id'] = $request->lead_id;
            $viewingData['lead_name'] = $request->lead_name;
            $viewingData['lead_phone'] = $request->lead_phone;
            $viewingData['lead_email'] = $request->lead_email;
            $viewingData['tenant_id'] = null;
        }

        $viewing->update($viewingData);

        return redirect()->route('agent.viewings.show', $viewing->id)
            ->with('success', 'Lịch hẹn đã được cập nhật thành công!');
    }

    /**
     * Remove the specified viewing from storage.
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        // Get viewing
        $viewing = Viewing::whereIn('property_id', $assignedPropertyIds)->findOrFail($id);

        // Only allow deletion of requested or confirmed viewings
        if (!in_array($viewing->status, ['requested', 'confirmed'])) {
            return redirect()->back()
                ->with('error', 'Chỉ có thể xóa lịch hẹn có trạng thái "Chờ xác nhận" hoặc "Đã xác nhận"!');
        }

        $viewing->update([
            'deleted_by' => $user->id
        ]);
        
        $viewing->delete();

        return redirect()->route('agent.viewings.index')
            ->with('success', 'Lịch hẹn đã được xóa thành công!');
    }
}