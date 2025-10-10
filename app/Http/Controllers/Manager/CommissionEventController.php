<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CommissionEvent;
use App\Models\CommissionPolicy;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionEventController extends Controller
{
    public function index(Request $request)
    {
        $query = CommissionEvent::with(['policy', 'agent', 'lease.tenant', 'unit.property'])
            ->where('organization_id', Auth::user()->organizations()->first()?->id);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('agent', function($agentQuery) use ($search) {
                    $agentQuery->where('full_name', 'like', "%{$search}%");
                })
                ->orWhereHas('policy', function($policyQuery) use ($search) {
                    $policyQuery->where('title', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('trigger_event')) {
            $query->where('trigger_event', $request->trigger_event);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('policy_id')) {
            $query->where('policy_id', $request->policy_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        $events = $query->orderBy('occurred_at', 'desc')->paginate(15);

        // Get filter options
        $agents = User::whereHas('userRoles', function($q) {
            $q->whereIn('key_code', ['agent', 'manager']);
        })->get();

        $policies = CommissionPolicy::where('organization_id', Auth::user()->organizations()->first()?->id)
            ->where('active', true)
            ->get();

        $statuses = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'paid' => 'Đã thanh toán',
            'reversed' => 'Đã hoàn',
            'cancelled' => 'Đã hủy'
        ];

        $triggerEvents = [
            'deposit_paid' => 'Thanh toán cọc',
            'lease_signed' => 'Ký hợp đồng',
            'invoice_paid' => 'Thanh toán hóa đơn',
            'viewing_done' => 'Hoàn thành xem phòng',
            'listing_published' => 'Đăng tin'
        ];

        return view('manager.commission-events.index', compact('events', 'agents', 'policies', 'statuses', 'triggerEvents'));
    }

    public function create()
    {
        $organization = Auth::user()->organizations()->first();
        
        $agents = User::whereHas('organizations', function($q) use ($organization) {
            $q->where('organization_id', $organization->id);
        })->get();

        $policies = CommissionPolicy::where('organization_id', $organization->id)
            ->where('active', true)
            ->get();

        $leases = Lease::where('organization_id', $organization->id)
            ->with(['tenant', 'unit'])
            ->get();

        $units = Unit::whereHas('property', function($q) use ($organization) {
            $q->where('organization_id', $organization->id);
        })->with('property')->get();

        $triggerEvents = [
            'deposit_paid' => 'Thanh toán cọc',
            'lease_signed' => 'Ký hợp đồng',
            'invoice_paid' => 'Thanh toán hóa đơn',
            'viewing_done' => 'Hoàn thành xem phòng',
            'listing_published' => 'Đăng tin'
        ];

        $statuses = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'paid' => 'Đã thanh toán',
            'reversed' => 'Đã hoàn',
            'cancelled' => 'Đã hủy'
        ];

        return view('manager.commission-events.create', compact('agents', 'policies', 'leases', 'units', 'triggerEvents', 'statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:users,id',
            'policy_id' => 'required|exists:commission_policies,id',
            'trigger_event' => 'required|in:deposit_paid,lease_signed,invoice_paid,viewing_done,listing_published',
            'occurred_at' => 'required|date',
            'amount_base' => 'required|numeric|min:0',
            'commission_total' => 'nullable|numeric|min:0',
            'lease_id' => 'nullable|exists:leases,id',
            'unit_id' => 'nullable|exists:units,id',
            'status' => 'required|in:pending,approved,paid,reversed,cancelled'
        ]);

        try {
            DB::beginTransaction();

            $organization = Auth::user()->organizations()->first();
            $policy = CommissionPolicy::findOrFail($request->policy_id);

            // Calculate commission if not provided
            $commissionTotal = $request->commission_total;
            if (!$commissionTotal) {
                if ($policy->calc_type === 'percent') {
                    $commissionTotal = ($request->amount_base * $policy->percent_value) / 100;
                } elseif ($policy->calc_type === 'flat') {
                    $commissionTotal = $policy->flat_amount;
                }
            }

            $commissionEvent = CommissionEvent::create([
                'organization_id' => $organization->id,
                'agent_id' => $request->agent_id,
                'policy_id' => $request->policy_id,
                'trigger_event' => $request->trigger_event,
                'occurred_at' => $request->occurred_at,
                'amount_base' => $request->amount_base,
                'commission_total' => $commissionTotal,
                'lease_id' => $request->lease_id,
                'unit_id' => $request->unit_id,
                'status' => $request->status,
            ]);

            // Create commission splits based on policy
            if ($policy->splits->count() > 0) {
                foreach ($policy->splits as $split) {
                    $splitAmount = ($commissionTotal * $split->percent_share) / 100;
                    
                    \App\Models\CommissionEventSplit::create([
                        'event_id' => $commissionEvent->id,
                        'user_id' => $request->agent_id, // This should be determined by role
                        'role_key' => $split->role_key,
                        'percent_share' => $split->percent_share,
                        'amount' => $splitAmount
                    ]);
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sự kiện hoa hồng đã được tạo thành công!',
                    'data' => $commissionEvent
                ]);
            }

            return redirect()->route('manager.commission-events.index')
                ->with('success', 'Sự kiện hoa hồng đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating commission event: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo sự kiện hoa hồng'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo sự kiện hoa hồng');
        }
    }

    public function show(CommissionEvent $commissionEvent)
    {
        // Check if user belongs to the same organization
        if ($commissionEvent->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to commission event.');
        }
        
        $commissionEvent->load([
            'policy', 
            'agent', 
            'lease.tenant', 
            'unit.property', 
            'splits.user',
            'organization'
        ]);

        return view('manager.commission-events.show', compact('commissionEvent'));
    }

    public function edit(CommissionEvent $commissionEvent)
    {
        // Check if user belongs to the same organization
        if ($commissionEvent->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to commission event.');
        }

        $statuses = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'paid' => 'Đã thanh toán',
            'reversed' => 'Đã hoàn',
            'cancelled' => 'Đã hủy'
        ];

        $triggerEvents = [
            'deposit_paid' => 'Thanh toán cọc',
            'lease_signed' => 'Ký hợp đồng',
            'invoice_paid' => 'Thanh toán hóa đơn',
            'viewing_done' => 'Hoàn thành xem phòng',
            'listing_published' => 'Đăng tin'
        ];

        $statuses = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'paid' => 'Đã thanh toán',
            'reversed' => 'Đã hoàn',
            'cancelled' => 'Đã hủy'
        ];

        $policies = CommissionPolicy::where('organization_id', Auth::user()->organizations()->first()?->id)
            ->where('active', true)
            ->get();

        $agents = User::whereHas('userRoles', function($q) {
            $q->whereIn('key_code', ['agent', 'manager']);
        })->get();

        $leases = Lease::where('organization_id', Auth::user()->organizations()->first()?->id)
            ->with(['tenant', 'unit.property'])
            ->get();

        $units = Unit::whereHas('property', function($q) {
            $q->where('organization_id', Auth::user()->organizations()->first()?->id);
        })->with('property')->get();

        return view('manager.commission-events.edit', compact(
            'commissionEvent', 
            'statuses', 
            'triggerEvents', 
            'policies', 
            'agents', 
            'leases', 
            'units'
        ));
    }

    public function update(Request $request, CommissionEvent $commissionEvent)
    {
        // Check if user belongs to the same organization
        /** @var \App\Models\User $user */
        if ($commissionEvent->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to commission event.');
        }

        $request->validate([
            'status' => 'required|in:pending,approved,paid,reversed,cancelled',
            'amount_base' => 'required|numeric|min:0',
            'commission_total' => 'required|numeric|min:0',
            'occurred_at' => 'required|date',
            'agent_id' => 'nullable|exists:users,id',
            'policy_id' => 'required|exists:commission_policies,id',
            'lease_id' => 'nullable|exists:leases,id',
            'unit_id' => 'nullable|exists:units,id',
            'note' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $commissionEvent->update([
                'status' => $request->status,
                'amount_base' => $request->amount_base,
                'commission_total' => $request->commission_total,
                'occurred_at' => $request->occurred_at,
                'agent_id' => $request->agent_id,
                'policy_id' => $request->policy_id,
                'lease_id' => $request->lease_id,
                'unit_id' => $request->unit_id,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sự kiện hoa hồng đã được cập nhật thành công!',
                    'data' => $commissionEvent
                ]);
            }

            return redirect()->route('manager.commission-events.index')
                ->with('success', 'Sự kiện hoa hồng đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating commission event: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật sự kiện hoa hồng'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật sự kiện hoa hồng');
        }
    }

    public function destroy(CommissionEvent $commissionEvent)
    {
        // Check if user belongs to the same organization
        if ($commissionEvent->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to commission event.');
        }

        try {
            $commissionEvent->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sự kiện hoa hồng đã được xóa thành công!'
                ]);
            }

            return redirect()->route('manager.commission-events.index')
                ->with('success', 'Sự kiện hoa hồng đã được xóa thành công!');

        } catch (\Exception $e) {
            Log::error('Error deleting commission event: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa sự kiện hoa hồng'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi xóa sự kiện hoa hồng');
        }
    }

    public function approve(CommissionEvent $commissionEvent)
    {
        // Check if user belongs to the same organization
        if ($commissionEvent->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to commission event.');
        }

        try {
            $commissionEvent->update(['status' => 'approved']);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sự kiện hoa hồng đã được duyệt!'
                ]);
            }

            return back()->with('success', 'Sự kiện hoa hồng đã được duyệt!');

        } catch (\Exception $e) {
            Log::error('Error approving commission event: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi duyệt sự kiện hoa hồng'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi duyệt sự kiện hoa hồng');
        }
    }

    public function markAsPaid(CommissionEvent $commissionEvent)
    {
        // Check if user belongs to the same organization
        if ($commissionEvent->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to commission event.');
        }

        try {
            $commissionEvent->update(['status' => 'paid']);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sự kiện hoa hồng đã được đánh dấu là đã thanh toán!'
                ]);
            }

            return back()->with('success', 'Sự kiện hoa hồng đã được đánh dấu là đã thanh toán!');

        } catch (\Exception $e) {
            Log::error('Error marking commission event as paid: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi đánh dấu sự kiện hoa hồng'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi đánh dấu sự kiện hoa hồng');
        }
    }
}
