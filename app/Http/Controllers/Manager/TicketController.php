<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with([
            'unit.property',
            'lease',
            'createdBy',
            'assignedTo',
            'logs'
        ]);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('lease_id')) {
            $query->where('lease_id', $request->lease_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter options
        $units = Unit::with('property')->whereHas('property')->get();
        $leases = Lease::with(['unit.property', 'tenant'])
            ->whereHas('unit.property')
            ->whereHas('tenant')
            ->get();
        $users = User::where('status', 1)->get();

        return view('manager.tickets.index', compact(
            'tickets',
            'units',
            'leases',
            'users'
        ));
    }

    public function create()
    {
        $units = Unit::with('property')->whereHas('property')->get();
        $leases = Lease::with(['unit.property', 'tenant'])
            ->whereHas('unit.property')
            ->whereHas('tenant')
            ->get();
        $users = User::where('status', 1)->get();

        return view('manager.tickets.create', compact(
            'units',
            'leases',
            'users'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'unit_id' => 'nullable|exists:units,id',
            'lease_id' => 'nullable|exists:leases,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $ticket = Ticket::create([
                'organization_id' => Auth::user()->organization_id ?? 1,
                'unit_id' => $request->unit_id,
                'lease_id' => $request->lease_id,
                'created_by' => Auth::id(),
                'assigned_to' => $request->assigned_to,
                'title' => $request->title,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => 'open',
            ]);

            // Create initial log
            $log = new TicketLog([
                'ticket_id' => $ticket->id,
                'actor_id' => Auth::id(),
                'action' => 'created',
                'detail' => 'Ticket được tạo mới',
            ]);
            $log->created_at = now();
            $log->save();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket đã được tạo thành công!',
                    'redirect' => route('manager.tickets.show', $ticket->id)
                ]);
            }

            return redirect()->route('manager.tickets.show', $ticket->id)
                ->with('success', 'Ticket đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo ticket: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi tạo ticket: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $ticket = Ticket::with([
            'unit.property',
            'lease.tenant',
            'createdBy',
            'assignedTo',
            'logs.actor',
            'logs.linkedInvoice'
        ])->findOrFail($id);

        return view('manager.tickets.show', compact('ticket'));
    }

    public function edit($id)
    {
        $ticket = Ticket::findOrFail($id);
        $units = Unit::with('property')->whereHas('property')->get();
        $leases = Lease::with(['unit.property', 'tenant'])
            ->whereHas('unit.property')
            ->whereHas('tenant')
            ->get();
        $users = User::where('status', 1)->get();

        return view('manager.tickets.edit', compact(
            'ticket',
            'units',
            'leases',
            'users'
        ));
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:open,in_progress,resolved,closed,cancelled',
            'unit_id' => 'nullable|exists:units,id',
            'lease_id' => 'nullable|exists:leases,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $ticket->status;
            $oldAssignedTo = $ticket->assigned_to;

            $ticket->update([
                'title' => $request->title,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => $request->status,
                'unit_id' => $request->unit_id,
                'lease_id' => $request->lease_id,
                'assigned_to' => $request->assigned_to,
            ]);

            // Log changes
            $changes = [];
            if ($oldStatus !== $request->status) {
                $changes[] = "Trạng thái: {$oldStatus} → {$request->status}";
            }
            if ($oldAssignedTo != $request->assigned_to) {
                $oldUser = $oldAssignedTo ? User::find($oldAssignedTo)->full_name : 'Chưa giao';
                $newUser = $request->assigned_to ? User::find($request->assigned_to)->full_name : 'Chưa giao';
                $changes[] = "Người phụ trách: {$oldUser} → {$newUser}";
            }

            if (!empty($changes)) {
                $log = new TicketLog([
                    'ticket_id' => $ticket->id,
                    'actor_id' => Auth::id(),
                    'action' => 'updated',
                    'detail' => 'Cập nhật: ' . implode(', ', $changes),
                ]);
                $log->created_at = now();
                $log->save();
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket đã được cập nhật thành công!'
                ]);
            }

            return redirect()->route('manager.tickets.show', $ticket->id)
                ->with('success', 'Ticket đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật ticket: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi cập nhật ticket: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            
            // Soft delete the ticket (trait sẽ tự động set deleted_by)
            $ticket->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket đã được xóa thành công!'
                ]);
            }

            return redirect()->route('manager.tickets.index')
                ->with('success', 'Ticket đã được xóa thành công!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting ticket: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa ticket: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi xóa ticket: ' . $e->getMessage());
        }
    }

    // Add log to ticket
    public function addLog(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'action' => 'required|string|max:100',
            'detail' => 'nullable|string',
            'cost_amount' => 'nullable|numeric|min:0',
            'cost_note' => 'nullable|string|max:255',
            'charge_to' => 'required|in:none,tenant_deposit,tenant_invoice,landlord',
            'linked_invoice_id' => 'nullable|exists:invoices,id',
        ]);

        try {
            DB::beginTransaction();

            $log = new TicketLog([
                'ticket_id' => $ticket->id,
                'actor_id' => Auth::id(),
                'action' => $request->action,
                'detail' => $request->detail,
                'cost_amount' => $request->cost_amount ?? 0,
                'cost_note' => $request->cost_note,
                'charge_to' => $request->charge_to,
                'linked_invoice_id' => $request->linked_invoice_id,
            ]);
            $log->created_at = now();
            $log->save();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nhật ký đã được thêm thành công!',
                    'log' => $log->load('actor')
                ]);
            }

            return back()->with('success', 'Nhật ký đã được thêm thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thêm nhật ký: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi thêm nhật ký: ' . $e->getMessage());
        }
    }

    // API method to get units for a property
    public function getUnits($propertyId)
    {
        $units = Unit::where('property_id', $propertyId)->get();
        return response()->json($units);
    }

    // API method to get leases for a unit
    public function getLeases($unitId)
    {
        $leases = Lease::where('unit_id', $unitId)
            ->with(['tenant'])
            ->get();
        return response()->json($leases);
    }
}
