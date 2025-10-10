<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with(['organizations', 'userRoles']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by organization
        if ($request->filled('organization_id')) {
            $query->whereHas('organizations', function ($q) use ($request) {
                $q->where('organizations.id', $request->organization_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $organizations = Organization::where('status', true)->get();

        return view('superadmin.users.index', compact('users', 'organizations'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request)
    {
        $organizations = Organization::where('status', true)->get();
        $roles = Role::all();
        $selectedOrganizationId = $request->get('organization_id');
        
        return view('superadmin.users.create', compact('organizations', 'roles', 'selectedOrganizationId'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30|unique:users,phone',
            'password' => 'required|string|min:6',
            'status' => 'required|in:0,1',
            'organizations' => 'required|array|min:1',
            'organizations.*' => 'exists:organizations,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id'
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

            // Attach organizations with roles
            foreach ($request->organizations as $index => $organizationId) {
                $roleId = $request->roles[$index] ?? $request->roles[0];
                $user->organizations()->attach($organizationId, [
                    'role_id' => $roleId,
                    'status' => 'active'
                ]);
            }

            // Attach global roles
            $user->userRoles()->attach($request->roles);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Người dùng đã được tạo thành công!',
                'redirect' => route('superadmin.users.show', $user)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['organizations.roles', 'userRoles', 'commissionEvents', 'salaryContracts']);
        
        // Get user statistics
        $stats = $this->getUserStats($user);
        
        return view('superadmin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user)
    {
        $user->load(['organizations', 'userRoles']);
        $organizations = Organization::where('status', true)->get();
        $roles = Role::all();
        
        return view('superadmin.users.edit', compact('user', 'organizations', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:0,1',
            'organizations' => 'required|array|min:1',
            'organizations.*' => 'exists:organizations,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id'
        ]);

        DB::beginTransaction();
        try {
            // Update user
            $updateData = [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ];

            if ($request->filled('password')) {
                $updateData['password_hash'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Update organization relationships
            $user->organizations()->detach();
            foreach ($request->organizations as $index => $organizationId) {
                $roleId = $request->roles[$index] ?? $request->roles[0];
                $user->organizations()->attach($organizationId, [
                    'role_id' => $roleId,
                    'status' => 'active'
                ]);
            }

            // Update global roles
            $user->userRoles()->sync($request->roles);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Người dùng đã được cập nhật thành công!',
                'redirect' => route('superadmin.users.show', $user)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        try {
            // Soft delete user
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Người dùng đã được xóa thành công!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(User $user)
    {
        try {
            $user->update(['status' => !$user->status]);
            
            $status = $user->status ? 'kích hoạt' : 'tạm dừng';
            
            return response()->json([
                'success' => true,
                'message' => "Người dùng đã được {$status} thành công!",
                'status' => $user->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics.
     */
    private function getUserStats($user)
    {
        try {
            return [
                'total_organizations' => $user->organizations->count(),
                'total_commissions' => $user->commissionEvents->count(),
                'total_commission_amount' => $user->commissionEvents->sum('commission_total'),
                'active_salary_contracts' => $user->salaryContracts->where('status', 'active')->count(),
                'last_login' => $user->last_login_at,
                'created_at' => $user->created_at,
            ];
        } catch (\Exception $e) {
            return [
                'total_organizations' => 0,
                'total_commissions' => 0,
                'total_commission_amount' => 0,
                'active_salary_contracts' => 0,
                'last_login' => null,
                'created_at' => $user->created_at,
            ];
        }
    }
}
