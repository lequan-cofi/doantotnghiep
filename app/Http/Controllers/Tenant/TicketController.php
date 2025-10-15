<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Lease;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TicketController extends Controller
{
    /**
     * Display a listing of the tenant's tickets
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get all tickets for the authenticated tenant with optimized query
        $query = Ticket::select([
            'tickets.*',
            'users_assigned.full_name as assigned_to_name',
            'units.code as unit_name',
            'properties.name as property_name',
            \DB::raw("CONCAT_WS(', ', locations.street, locations.ward, locations.district, locations.city) as location_address"),
            \DB::raw("CONCAT_WS(', ', locations2025.street, locations2025.ward, locations2025.city) as location2025_address")
        ])
        ->leftJoin('users as users_assigned', 'tickets.assigned_to', '=', 'users_assigned.id')
        ->leftJoin('units', 'tickets.unit_id', '=', 'units.id')
        ->leftJoin('properties', 'units.property_id', '=', 'properties.id')
        ->leftJoin('locations', 'properties.location_id', '=', 'locations.id')
        ->leftJoin('locations as locations2025', 'properties.location_id_2025', '=', 'locations2025.id')
        ->whereHas('lease', function($q) use ($user) {
            $q->where('tenant_id', $user->id);
        })
        ->whereNull('tickets.deleted_at');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            $query->where('status', $status);
        }

        // Apply priority filter
        if ($request->filled('priority') && $request->priority !== 'all') {
            $priority = $request->priority;
            $query->where('priority', $priority);
        }

        $tickets = $query->latest('created_at')->paginate(10);

        // Calculate statistics
        $stats = $this->calculateTicketStats($user->id);

        return view('tenant.ticket.index', compact('tickets', 'stats'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get active leases for the tenant to select unit
        $leases = Lease::with([
            'unit.property'
        ])
        ->where('tenant_id', $user->id)
        ->where('status', 'active')
        ->whereNull('deleted_at')
        ->get();

        return view('tenant.ticket.create', compact('leases'));
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate input
            $validated = $request->validate([
                'title' => 'required|string|min:5|max:255',
                'description' => 'required|string|min:10',
                'priority' => 'required|in:low,medium,high,urgent',
                'unit_id' => 'required|exists:units,id',
                'lease_id' => 'required|exists:leases,id'
            ], [
                'title.required' => 'Vui lòng nhập tiêu đề ticket',
                'title.min' => 'Tiêu đề phải có ít nhất 5 ký tự',
                'description.required' => 'Vui lòng nhập mô tả',
                'description.min' => 'Mô tả phải có ít nhất 10 ký tự',
                'priority.required' => 'Vui lòng chọn độ ưu tiên',
                'unit_id.required' => 'Vui lòng chọn hợp đồng (unit_id)',
                'lease_id.required' => 'Vui lòng chọn hợp đồng',
            ]);

            // Verify that the lease belongs to the tenant
            $lease = Lease::with(['unit.property'])
                ->where('id', $validated['lease_id'])
                ->where('tenant_id', $user->id)
                ->where('unit_id', $validated['unit_id'])
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->firstOrFail();

            // Get property and auto-assign manager
            $property = $lease->unit->property;
            $manager = $property ? $property->getPrimaryManager() : null;
            $organizationId = $property && $property->organization_id 
                ? $property->organization_id 
                : $user->organization_id;

            // Create ticket
            $ticket = Ticket::create([
                'organization_id' => $organizationId,
                'unit_id' => $validated['unit_id'],
                'lease_id' => $validated['lease_id'],
                'created_by' => $user->id,
                'assigned_to' => $manager ? $manager->id : null,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => 'open'
            ]);

            Log::info('Ticket created:', [
                'ticket_id' => $ticket->id,
                'assigned_to' => $manager ? $manager->id : null,
                'property' => $property ? $property->name : null
            ]);

            return redirect()->route('tenant.tickets.show', $ticket->id)
                ->with('success', 'Ticket đã được tạo thành công!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Ticket creation error:', [
                'message' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified ticket
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // Use raw query to get all needed data in one go
        $ticket = Ticket::select([
            'tickets.*',
            'users_created.full_name as created_by_name',
            'users_assigned.full_name as assigned_to_name',
            'units.code as unit_name',
            'properties.name as property_name',
            \DB::raw("CONCAT_WS(', ', locations.street, locations.ward, locations.district, locations.city) as location_address"),
            \DB::raw("CONCAT_WS(', ', locations2025.street, locations2025.ward, locations2025.city) as location2025_address"),
            'leases.contract_no as lease_contract_number'
        ])
        ->leftJoin('users as users_created', 'tickets.created_by', '=', 'users_created.id')
        ->leftJoin('users as users_assigned', 'tickets.assigned_to', '=', 'users_assigned.id')
        ->leftJoin('units', 'tickets.unit_id', '=', 'units.id')
        ->leftJoin('properties', 'units.property_id', '=', 'properties.id')
        ->leftJoin('locations', 'properties.location_id', '=', 'locations.id')
        ->leftJoin('locations as locations2025', 'properties.location_id_2025', '=', 'locations2025.id')
        ->leftJoin('leases', 'tickets.lease_id', '=', 'leases.id')
        ->where('tickets.id', $id)
        ->whereHas('lease', function($q) use ($user) {
            $q->where('tenant_id', $user->id);
        })
        ->whereNull('tickets.deleted_at')
        ->firstOrFail();

        // Load logs separately to avoid N+1
        $ticket->logs = \App\Models\TicketLog::select([
            'ticket_logs.*',
            'users.full_name as actor_name'
        ])
        ->leftJoin('users', 'ticket_logs.actor_id', '=', 'users.id')
        ->where('ticket_logs.ticket_id', $ticket->id)
        ->orderBy('ticket_logs.created_at', 'desc')
        ->get();

        return view('tenant.ticket.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified ticket
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        $ticket = Ticket::with([
            'unit.property',
            'lease.unit.property',
            'createdBy',
            'assignedTo'
        ])
        ->where('id', $id)
        ->whereHas('lease', function($q) use ($user) {
            $q->where('tenant_id', $user->id);
        })
        ->whereNull('deleted_at')
        ->firstOrFail();

        // Only allow editing open tickets (but still show form with warning)
        return view('tenant.ticket.edit', compact('ticket'));
    }

    /**
     * Update the specified ticket
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            // Get ticket and verify ownership + status
            $ticket = Ticket::where('id', $id)
                ->whereHas('lease', function($q) use ($user) {
                    $q->where('tenant_id', $user->id);
                })
                ->whereNull('deleted_at')
                ->firstOrFail();

            // Only allow updating open tickets
            if ($ticket->status !== 'open') {
                return redirect()->back()
                    ->with('error', 'Chỉ có thể chỉnh sửa ticket đang ở trạng thái "Đang mở".')
                    ->withInput();
            }

            // Validate input
            $validated = $request->validate([
                'title' => 'required|string|min:5|max:255',
                'description' => 'required|string|min:10',
                'priority' => 'required|in:low,medium,high,urgent'
            ], [
                'title.required' => 'Vui lòng nhập tiêu đề ticket',
                'title.min' => 'Tiêu đề phải có ít nhất 5 ký tự',
                'description.required' => 'Vui lòng nhập mô tả',
                'description.min' => 'Mô tả phải có ít nhất 10 ký tự',
                'priority.required' => 'Vui lòng chọn độ ưu tiên',
            ]);

            // Update ticket (lease and unit cannot be changed)
            $ticket->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority']
            ]);

            Log::info('Ticket updated:', [
                'ticket_id' => $ticket->id,
                'updated_by' => $user->id
            ]);

            return redirect()->route('tenant.tickets.show', $ticket->id)
                ->with('success', 'Ticket đã được cập nhật thành công!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Ticket update error:', [
                'message' => $e->getMessage(),
                'ticket_id' => $id
            ]);
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Cancel the specified ticket (change status to cancelled instead of delete)
     */
    public function destroy($id)
    {
        Log::info('Destroy/Cancel method called', ['ticket_id' => $id]);
        
        $user = Auth::user();
        Log::info('User authenticated', ['user_id' => $user->id]);
        
        $ticket = Ticket::where('id', $id)
            ->whereHas('lease', function($q) use ($user) {
                $q->where('tenant_id', $user->id);
            })
            ->whereIn('status', ['open', 'in_progress']) // Only allow cancelling open or in_progress tickets
            ->whereNull('deleted_at')
            ->first();

        if (!$ticket) {
            Log::error('Ticket not found or not allowed to cancel', [
                'ticket_id' => $id,
                'user_id' => $user->id
            ]);
            
            return redirect()->route('tenant.tickets.index')
                ->with('error', 'Không tìm thấy ticket hoặc bạn không có quyền hủy ticket này! Chỉ có thể hủy ticket đang mở hoặc đang xử lý.');
        }

        Log::info('Ticket found, attempting to cancel', [
            'ticket_id' => $ticket->id,
            'old_status' => $ticket->status
        ]);

        // Update status to cancelled
        $ticket->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
            'cancelled_by' => $user->id,
            'updated_at' => Carbon::now()
        ]);

        Log::info('Ticket cancelled successfully', [
            'ticket_id' => $ticket->id,
            'new_status' => 'cancelled'
        ]);

        return redirect()->route('tenant.tickets.index')
            ->with('success', 'Ticket đã được hủy thành công!');
    }

    /**
     * Get units for a specific lease (AJAX)
     */
    public function getUnitsByLease($leaseId)
    {
        $user = Auth::user();
        
        $lease = Lease::with('unit.property')
            ->where('id', $leaseId)
            ->where('tenant_id', $user->id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->firstOrFail();

        return response()->json([
            'unit' => [
                'id' => $lease->unit->id,
                'name' => $lease->unit->name,
                'property_name' => $lease->unit->property->name,
                'address' => $lease->unit->property->address
            ]
        ]);
    }

    /**
     * Calculate ticket statistics for the tenant
     */
    private function calculateTicketStats($tenantId)
    {
        $now = Carbon::now();

        $open = Ticket::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->where('status', 'open')
        ->whereNull('deleted_at')
        ->count();

        $inProgress = Ticket::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->where('status', 'in_progress')
        ->whereNull('deleted_at')
        ->count();

        $resolved = Ticket::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->where('status', 'resolved')
        ->whereNull('deleted_at')
        ->count();

        $closed = Ticket::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->where('status', 'closed')
        ->whereNull('deleted_at')
        ->count();

        $total = Ticket::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->whereNull('deleted_at')
        ->count();

        return [
            'open' => $open,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
            'closed' => $closed,
            'total' => $total
        ];
    }
}
