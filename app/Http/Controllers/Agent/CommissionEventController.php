<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\CommissionEvent;
use App\Models\CommissionPolicy;
// CommissionInvoiceService removed - no longer creating invoices for commission events
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionEventController extends Controller
{
    // CommissionInvoiceService removed - no longer creating invoices for commission events
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get user's organization_id from organization_users table
            $userOrganization = DB::table('organization_users')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
                
            $organizationId = $userOrganization ? $userOrganization->organization_id : null;
            
            // Debug: Log user information
            Log::info('CommissionEventController@index - User ID: ' . $user->id . ', Organization ID: ' . ($organizationId ?? 'NULL'));
            
            // Check if user has organization_id
            if (!$organizationId) {
                Log::warning('CommissionEventController@index - User has no organization_id');
                return view('agent.commission-events.index', [
                    'events' => collect([]),
                    'stats' => [
                        'total_events' => 0,
                        'total_commission' => 0,
                        'paid_commission' => 0,
                        'pending_commission' => 0,
                        'approved_commission' => 0,
                    ],
                    'statusStats' => [
                        'pending' => 0,
                        'approved' => 0,
                        'paid' => 0,
                        'reversed' => 0,
                        'cancelled' => 0,
                    ]
                ])->with('warning', 'Bạn chưa được gán vào tổ chức nào. Vui lòng liên hệ quản trị viên.');
            }
            
            // Lấy các sự kiện hoa hồng của agent
            $events = CommissionEvent::where('organization_id', $organizationId)
                ->where('agent_id', $user->id)
                ->whereNull('deleted_at') // Chỉ lấy các sự kiện chưa bị xóa mềm
                ->with(['policy', 'lease.unit.property', 'lease.tenant', 'listing', 'agent'])
                ->orderBy('occurred_at', 'desc')
                ->paginate(20);
                
            // Debug: Log events count
            Log::info('CommissionEventController@index - Events count: ' . $events->count());
            
            // Debug: Check raw database data
            $rawEvents = CommissionEvent::where('organization_id', $organizationId)
                ->where('agent_id', $user->id)
                ->get();
            Log::info('CommissionEventController@index - Raw events count: ' . $rawEvents->count());
            
            // Debug: Check all events in organization
            $allOrgEvents = CommissionEvent::where('organization_id', $organizationId)->get();
            Log::info('CommissionEventController@index - All org events count: ' . $allOrgEvents->count());

            // Thống kê
            $stats = [
                'total_events' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->count(),
                'total_commission' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->sum('commission_total'),
                'paid_commission' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'paid')
                    ->sum('commission_total'),
                'pending_commission' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'pending')
                    ->sum('commission_total'),
                'approved_commission' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'approved')
                    ->sum('commission_total'),
            ];

            // Thống kê theo trạng thái
            $statusStats = [
                'pending' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'pending')
                    ->count(),
                'approved' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'approved')
                    ->count(),
                'paid' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'paid')
                    ->count(),
                'reversed' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'reversed')
                    ->count(),
                'cancelled' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'cancelled')
                    ->count(),
            ];

            return view('agent.commission-events.index', compact('events', 'stats', 'statusStats'));
        } catch (\Exception $e) {
            Log::error('Error in CommissionEventController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải danh sách sự kiện hoa hồng.');
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            
            // Get user's organization_id from organization_users table
            $userOrganization = DB::table('organization_users')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
                
            $organizationId = $userOrganization ? $userOrganization->organization_id : null;
            
            if (!$organizationId) {
                return redirect()->route('agent.commission-events.index')
                    ->with('error', 'Bạn chưa được gán vào tổ chức nào. Vui lòng liên hệ quản trị viên.');
            }
            
            $event = CommissionEvent::where('id', $id)
                ->where('organization_id', $organizationId)
                ->where('agent_id', $user->id)
                ->whereNull('deleted_at') // Chỉ lấy sự kiện chưa bị xóa mềm
                ->with(['policy', 'lease.unit.property', 'lease.tenant', 'listing', 'agent'])
                ->firstOrFail();

            return view('agent.commission-events.show', compact('event'));
        } catch (\Exception $e) {
            Log::error('Error in CommissionEventController@show: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải chi tiết sự kiện hoa hồng.');
        }
    }

    public function create()
    {
        // Agent không thể tạo sự kiện hoa hồng thủ công
        return redirect()->route('agent.commission-events.index')
            ->with('error', 'Bạn không có quyền tạo sự kiện hoa hồng thủ công.');
    }

    public function store(Request $request)
    {
        // Agent không thể tạo sự kiện hoa hồng thủ công
        return redirect()->route('agent.commission-events.index')
            ->with('error', 'Bạn không có quyền tạo sự kiện hoa hồng thủ công.');
    }

    public function edit($id)
    {
        // Agent không thể chỉnh sửa sự kiện hoa hồng
        return redirect()->route('agent.commission-events.index')
            ->with('error', 'Bạn không có quyền chỉnh sửa sự kiện hoa hồng.');
    }

    public function update(Request $request, $id)
    {
        // Agent không thể cập nhật sự kiện hoa hồng
        return redirect()->route('agent.commission-events.index')
            ->with('error', 'Bạn không có quyền cập nhật sự kiện hoa hồng.');
    }

    public function destroy($id)
    {
        // Agent không thể xóa sự kiện hoa hồng
        return redirect()->route('agent.commission-events.index')
            ->with('error', 'Bạn không có quyền xóa sự kiện hoa hồng.');
    }
    
    // Invoice sync methods removed - commission events no longer create invoices

    // Test method removed - no longer needed

}
