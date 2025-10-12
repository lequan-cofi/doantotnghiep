<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\CommissionPolicy;
use App\Models\CommissionEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommissionPolicyController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get user's organization_id from organization_users table
            $userOrganization = \DB::table('organization_users')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
                
            $organizationId = $userOrganization ? $userOrganization->organization_id : null;
            
            // Debug: Log user information
            Log::info('CommissionPolicyController@index - User ID: ' . $user->id . ', Organization ID: ' . ($organizationId ?? 'NULL'));
            
            // Check if user has organization_id
            if (!$organizationId) {
                Log::warning('CommissionPolicyController@index - User has no organization_id');
                return view('agent.commission-policies.index', [
                    'policies' => collect([]),
                    'stats' => [
                        'total_policies' => 0,
                        'active_policies' => 0,
                        'total_events' => 0,
                        'total_commission' => 0,
                    ]
                ])->with('warning', 'Bạn chưa được gán vào tổ chức nào. Vui lòng liên hệ quản trị viên.');
            }
            
            // Lấy các chính sách hoa hồng của tổ chức
            $policies = CommissionPolicy::where('organization_id', $organizationId)
                ->whereNull('deleted_at') // Chỉ lấy các chính sách chưa bị xóa mềm
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Debug: Log policies count
            Log::info('CommissionPolicyController@index - Policies count: ' . $policies->count());
            
            // Debug: Check raw database data
            $rawPolicies = CommissionPolicy::where('organization_id', $organizationId)->get();
            Log::info('CommissionPolicyController@index - Raw policies count: ' . $rawPolicies->count());
            
            // Debug: Check all policies in organization
            $allOrgPolicies = CommissionPolicy::where('organization_id', $organizationId)->get();
            Log::info('CommissionPolicyController@index - All org policies count: ' . $allOrgPolicies->count());

            // Thống kê
            $stats = [
                'total_policies' => $policies->count(),
                'active_policies' => $policies->where('active', true)->count(),
                'total_events' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->count(),
                'total_commission' => CommissionEvent::where('organization_id', $organizationId)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->sum('commission_total'),
            ];

            return view('agent.commission-policies.index', compact('policies', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error in CommissionPolicyController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải danh sách chính sách hoa hồng.');
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            
            // Get user's organization_id from organization_users table
            $userOrganization = \DB::table('organization_users')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
                
            $organizationId = $userOrganization ? $userOrganization->organization_id : null;
            
            if (!$organizationId) {
                return redirect()->route('agent.commission-policies.index')
                    ->with('error', 'Bạn chưa được gán vào tổ chức nào. Vui lòng liên hệ quản trị viên.');
            }
            
            $policy = CommissionPolicy::where('id', $id)
                ->where('organization_id', $organizationId)
                ->whereNull('deleted_at') // Chỉ lấy chính sách chưa bị xóa mềm
                ->firstOrFail();

            // Lấy các sự kiện hoa hồng của agent này theo chính sách này
            $events = CommissionEvent::where('policy_id', $id)
                ->where('agent_id', $user->id)
                ->whereNull('deleted_at') // Chỉ lấy các sự kiện chưa bị xóa mềm
                ->with(['lease.unit.property', 'lease.tenant', 'agent'])
                ->orderBy('occurred_at', 'desc')
                ->paginate(20);

            // Thống kê cho chính sách này
            $policyStats = [
                'total_events' => CommissionEvent::where('policy_id', $id)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->count(),
                'total_commission' => CommissionEvent::where('policy_id', $id)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->sum('commission_total'),
                'paid_commission' => CommissionEvent::where('policy_id', $id)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'paid')
                    ->sum('commission_total'),
                'pending_commission' => CommissionEvent::where('policy_id', $id)
                    ->where('agent_id', $user->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'pending')
                    ->sum('commission_total'),
            ];

            return view('agent.commission-policies.show', compact('policy', 'events', 'policyStats'));
        } catch (\Exception $e) {
            Log::error('Error in CommissionPolicyController@show: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải chi tiết chính sách hoa hồng.');
        }
    }
    
    public function test()
    {
        try {
            $user = Auth::user();
            
            // Get user's organization_id from organization_users table
            $userOrganization = \DB::table('organization_users')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
                
            $organizationId = $userOrganization ? $userOrganization->organization_id : null;
            
            $allPolicies = CommissionPolicy::all();
            $userPolicies = CommissionPolicy::where('organization_id', $organizationId)->get();
            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'organization_id' => $organizationId
                ],
                'database' => [
                    'total_policies' => $allPolicies->count(),
                    'user_policies' => $userPolicies->count()
                ],
                'sample_policies' => $userPolicies->take(10)->map(function($policy) {
                    return [
                        'id' => $policy->id,
                        'organization_id' => $policy->organization_id,
                        'title' => $policy->title,
                        'active' => $policy->active,
                        'deleted_at' => $policy->deleted_at,
                        'created_at' => $policy->created_at
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

}
