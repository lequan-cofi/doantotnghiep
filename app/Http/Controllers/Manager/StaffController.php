<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Property;
use App\Models\CommissionPolicy;
use App\Models\Organization;
use App\Models\SalaryContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany organizations()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany organizationUsers()
 */
class StaffController extends Controller
{
    /**
     * Display a listing of staff members.
     */
    public function index(Request $request)
    {
        // Lấy tổ chức của manager hiện tại
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations()->first();
        
        if (!$managerOrganization) {
            return view('manager.staff.index', [
                'staff' => collect([]),
                'roles' => collect([])
            ])->with('error', 'Manager chưa được gắn vào tổ chức nào!');
        }

        $query = User::with(['organizationRoles', 'salaryContracts', 'assignedProperties'])
            ->whereHas('organizationRoles', function($q) use ($managerOrganization) {
                $q->where('organization_id', $managerOrganization->id)
                  ->whereIn('key_code', ['agent', 'manager']);
            });

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role_id')) {
            $query->whereHas('organizationRoles', function($q) use ($request, $managerOrganization) {
                $q->where('organization_id', $managerOrganization->id)
                  ->where('role_id', $request->role_id);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $staff = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get roles for filter (agent and manager only)
        $roles = Role::whereIn('key_code', ['agent', 'manager'])->get();

        return view('manager.staff.index', compact('staff', 'roles'));
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create()
    {
        $roles = Role::whereIn('key_code', ['agent', 'manager'])->get();
        
        // Lấy tổ chức của manager hiện tại
        /** @var User $manager */
        $manager = Auth::user();
        $managerOrganization = $manager->organizations()->first();
        
        // Chỉ lấy properties thuộc tổ chức của manager
        $properties = Property::where('status', 1)
            ->where('organization_id', $managerOrganization?->id)
            ->get();
            
        $commissionPolicies = CommissionPolicy::where('active', 1)
            ->where('organization_id', $managerOrganization?->id)
            ->get();

        return view('manager.staff.create', compact('roles', 'managerOrganization', 'properties', 'commissionPolicies'));
    }

    /**
     * Store a newly created staff member in storage.
     */
    public function store(Request $request)
    {
        // Lấy tổ chức của manager hiện tại
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations()->first();
        
        if (!$managerOrganization) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager chưa được gắn vào tổ chức nào!'
                ], 400);
            }
            return back()->with('error', 'Manager chưa được gắn vào tổ chức nào!');
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|boolean',
            'properties' => 'nullable|array',
            'properties.*' => 'exists:properties,id',
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password_hash' => Hash::make($request->password),
                'status' => $request->status,
            ]);

            // Assign role through organization_users (already handled above)

            // Assign to organization (tự động gắn tổ chức của manager)
            DB::table('organization_users')->insert([
                'organization_id' => $managerOrganization->id,
                'user_id' => $user->id,
                'role_id' => $request->role_id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            // Assign properties
            if ($request->filled('properties')) {
                foreach ($request->properties as $propertyId) {
                    DB::table('properties_user')->insert([
                        'property_id' => $propertyId,
                        'user_id' => $user->id,
                        'role_key' => 'agent',
                        'assigned_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nhân viên đã được tạo thành công!',
                    'redirect' => route('manager.staff.index')
                ]);
            }

            return redirect()->route('manager.staff.index')
                ->with('success', 'Nhân viên đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể tạo nhân viên: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Không thể tạo nhân viên: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified staff member.
     */
    public function show($id)
    {
        // Lấy tổ chức của manager hiện tại
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations()->first();
        
        if (!$managerOrganization) {
            return redirect()->route('manager.staff.index')
                ->with('error', 'Manager chưa được gắn vào tổ chức nào!');
        }

        $staff = User::with([
            'organizationRoles',
            'salaryContracts.organization',
            'assignedProperties.propertyType',
            'assignedProperties.location',
            'commissionEvents',
            'organizationUsers.organization'
        ])->findOrFail($id);

        // Kiểm tra nhân viên có thuộc tổ chức của manager không
        $staffOrganization = $staff->organizationUsers()->where('organization_id', $managerOrganization->id)->first();
        if (!$staffOrganization) {
            return redirect()->route('manager.staff.index')
                ->with('error', 'Bạn không có quyền xem thông tin nhân viên này!');
        }

        // Get commission statistics
        $commissionStats = DB::table('commission_events')
            ->where('user_id', $id)
            ->selectRaw('
                status,
                COUNT(*) as count,
                SUM(commission_total) as total_amount
            ')
            ->groupBy('status')
            ->get();

        // Get recent salary history
        $salaryHistory = DB::table('payroll_payslips')
            ->join('payroll_cycles', 'payroll_payslips.payroll_cycle_id', '=', 'payroll_cycles.id')
            ->where('payroll_payslips.user_id', $id)
            ->select('payroll_payslips.*', 'payroll_cycles.period_month')
            ->orderBy('payroll_cycles.period_month', 'desc')
            ->limit(12)
            ->get();

        return view('manager.staff.show', compact('staff', 'commissionStats', 'salaryHistory'));
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit($id)
    {
        // Lấy tổ chức của manager hiện tại
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations()->first();
        
        if (!$managerOrganization) {
            return redirect()->route('manager.staff.index')
                ->with('error', 'Manager chưa được gắn vào tổ chức nào!');
        }

        $staff = User::with(['organizationRoles', 'salaryContracts', 'assignedProperties'])->findOrFail($id);

        // Kiểm tra nhân viên có thuộc tổ chức của manager không
        $staffOrganization = $staff->organizationUsers()->where('organization_id', $managerOrganization->id)->first();
        if (!$staffOrganization) {
            return redirect()->route('manager.staff.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa nhân viên này!');
        }
        $roles = Role::whereIn('key_code', ['agent', 'manager'])->get();
        
        // Chỉ lấy properties thuộc tổ chức của manager
        $properties = Property::where('status', 1)
            ->where('organization_id', $managerOrganization?->id)
            ->get();
            
        $commissionPolicies = CommissionPolicy::where('active', 1)
            ->where('organization_id', $managerOrganization?->id)
            ->get();

        // Get assigned property IDs
        $assignedPropertyIds = $staff->assignedProperties->pluck('id')->toArray();

        return view('manager.staff.edit', compact('staff', 'roles', 'managerOrganization', 'properties', 'commissionPolicies', 'assignedPropertyIds'));
    }

    /**
     * Update the specified staff member in storage.
     */
    public function update(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        // Kiểm tra nhân viên có thuộc tổ chức của manager không
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations()->first();
        
        if (!$managerOrganization) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager chưa được gắn vào tổ chức nào!'
                ], 400);
            }
            return back()->with('error', 'Manager chưa được gắn vào tổ chức nào!');
        }

        $staffOrganization = $staff->organizationUsers()->where('organization_id', $managerOrganization->id)->first();
        if (!$staffOrganization) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền cập nhật nhân viên này!'
                ], 403);
            }
            return back()->with('error', 'Bạn không có quyền cập nhật nhân viên này!');
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|unique:users,phone,' . $id,
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|boolean',
            'properties' => 'nullable|array',
            'properties.*' => 'exists:properties,id',
        ]);

        DB::beginTransaction();
        try {
            // Update user
            $staff->update([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $staff->update(['password_hash' => Hash::make($request->password)]);
            }

            // Update role through organization_users (handled below)

            // Update organization role (chỉ cập nhật role, không xóa tất cả)
            DB::table('organization_users')
                ->where('user_id', $id)
                ->where('organization_id', $managerOrganization->id)
                ->update([
                    'role_id' => $request->role_id,
                    'updated_at' => now(),
                ]);


            // Update properties assignment
            // Xóa các properties cũ không còn được chọn
            $currentPropertyIds = $staff->assignedProperties->pluck('id')->toArray();
            $newPropertyIds = $request->properties ?? [];
            $propertiesToDelete = array_diff($currentPropertyIds, $newPropertyIds);
            $propertiesToAdd = array_diff($newPropertyIds, $currentPropertyIds);

            // Xóa properties không còn được chọn
            if (!empty($propertiesToDelete)) {
                DB::table('properties_user')
                    ->where('user_id', $id)
                    ->whereIn('property_id', $propertiesToDelete)
                    ->delete();
            }

            // Thêm properties mới
            if (!empty($propertiesToAdd)) {
                foreach ($propertiesToAdd as $propertyId) {
                    DB::table('properties_user')->insert([
                        'property_id' => $propertyId,
                        'user_id' => $staff->id,
                        'role_key' => 'agent',
                        'assigned_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thông tin nhân viên đã được cập nhật!',
                    'redirect' => route('manager.staff.show', $staff->id)
                ]);
            }

            return redirect()->route('manager.staff.show', $staff->id)
                ->with('success', 'Thông tin nhân viên đã được cập nhật!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log error for debugging
            Log::error('Staff update error: ' . $e->getMessage(), [
                'user_id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể cập nhật nhân viên: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Không thể cập nhật nhân viên: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified staff member from storage.
     */
    public function destroy($id)
    {
        try {
            $staff = User::findOrFail($id);

            // Kiểm tra nhân viên có thuộc tổ chức của manager không
            /** @var User $currentUser */
            $currentUser = Auth::user();
            $managerOrganization = $currentUser->organizations()->first();
            
            if (!$managerOrganization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager chưa được gắn vào tổ chức nào!'
                ], 400);
            }

            $staffOrganization = $staff->organizationUsers()->where('organization_id', $managerOrganization->id)->first();
            if (!$staffOrganization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xóa nhân viên này!'
                ], 403);
            }
            
            // Soft delete the user
            $staff->deleted_by = Auth::id();
            $staff->save();
            $staff->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nhân viên đã được xóa thành công!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa nhân viên: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get staff salary contracts
     */
    public function getSalaryContracts($id)
    {
        $contracts = SalaryContract::where('user_id', $id)
            ->with('organization')
            ->orderBy('effective_from', 'desc')
            ->get();

        return response()->json($contracts);
    }

    /**
     * Get staff commission events
     */
    public function getCommissionEvents($id)
    {
        $events = DB::table('commission_events')
            ->join('commission_policies', 'commission_events.policy_id', '=', 'commission_policies.id')
            ->where('commission_events.user_id', $id)
            ->select(
                'commission_events.*',
                'commission_events.occurred_at',
                'commission_events.amount_base',
                'commission_policies.title as policy_title'
            )
            ->orderBy('commission_events.occurred_at', 'desc')
            ->get();

        return response()->json($events);
    }

    /**
     * Assign properties to staff
     */
    public function assignProperties(Request $request, $id)
    {
        $request->validate([
            'properties' => 'required|array',
            'properties.*' => 'exists:properties,id',
        ]);

        try {
            $staff = User::findOrFail($id);

            // Remove existing assignments
            DB::table('properties_user')->where('user_id', $id)->delete();

            // Add new assignments
            foreach ($request->properties as $propertyId) {
                DB::table('properties_user')->insert([
                    'property_id' => $propertyId,
                    'user_id' => $staff->id,
                    'role_key' => 'agent',
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'updated_by' => Auth::id(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã gắn bất động sản cho nhân viên thành công!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gắn bất động sản: ' . $e->getMessage()
            ], 500);
        }
    }
}


