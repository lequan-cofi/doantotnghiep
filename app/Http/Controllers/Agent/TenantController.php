<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Lead;
use App\Models\Lease;
use App\Models\LeaseResident;
use App\Models\Organization;
use App\Models\Role;
use App\Models\OrganizationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    /**
     * Display a listing of users in the same organization.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organizations->first()->id ?? null;
        
        if (!$organizationId) {
            return redirect()->back()->with('error', 'Bạn không thuộc tổ chức nào.');
        }

        $search = $request->get('search');
        $type = $request->get('type', 'all'); // all, with_leases, without_leases

        // Get all users in the same organization
        $userQuery = User::whereHas('organizations', function($query) use ($organizationId) {
            $query->where('organizations.id', $organizationId);
        });

        if ($search) {
            $userQuery->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get users with role_id = 5 in organization with their roles and leases
        $users = $userQuery->whereHas('organizationUsers', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId)
                  ->where('role_id', 5);
        })->with([
            'organizationRoles' => function($query) use ($organizationId) {
                $query->wherePivot('organization_id', $organizationId);
            },
            'leasesAsTenant' => function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId)->with('unit.property');
            }
        ])->get();

        // Filter based on type
        $usersWithLeases = $users->filter(function($user) use ($organizationId) {
            return $user->leasesAsTenant->where('organization_id', $organizationId)->count() > 0;
        });

        $usersWithoutLeases = $users->filter(function($user) use ($organizationId) {
            return $user->leasesAsTenant->where('organization_id', $organizationId)->count() == 0;
        });

        // Apply type filter
        if ($type === 'with_leases') {
            $users = $usersWithLeases;
        } elseif ($type === 'without_leases') {
            $users = $usersWithoutLeases;
        }
        // For 'all', $users already contains all users

        return view('agent.tenants.index', compact('users', 'usersWithLeases', 'usersWithoutLeases', 'search', 'type'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        $user = Auth::user();
        $organizationId = $user->organizations->first()->id ?? null;
        
        if (!$organizationId) {
            return redirect()->back()->with('error', 'Bạn không thuộc tổ chức nào.');
        }

        // Get available roles for user (only role_id = 5)
        $roles = Role::where('id', 5)->get();
        
        return view('agent.tenants.create', compact('roles'));
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organizations->first()->id ?? null;
        
        if (!$organizationId) {
            return redirect()->back()->with('error', 'Bạn không thuộc tổ chức nào.');
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id|in:5',
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $newUser = User::create([
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'status' => 1,
            ]);

            // Add user to organization with role
            OrganizationUser::create([
                'organization_id' => $organizationId,
                'user_id' => $newUser->id,
                'role_id' => $request->role_id,
                'status' => 1,
            ]);


            DB::commit();
            
            return redirect()->route('agent.tenants.index')
                ->with('success', 'Tạo khách hàng thành công.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo khách hàng: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = Auth::user();
        $organizationId = $user->organizations->first()->id ?? null;
        
        if (!$organizationId) {
            return redirect()->back()->with('error', 'Bạn không thuộc tổ chức nào.');
        }

        $tenant = User::whereHas('organizationUsers', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId)
                  ->where('role_id', 5);
        })->with([
            'leasesAsTenant' => function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId)
                      ->with(['unit.property', 'residents']);
            },
            'organizationRoles' => function($query) use ($organizationId) {
                $query->wherePivot('organization_id', $organizationId);
            }
        ])->findOrFail($id);

        return view('agent.tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $organizationId = $user->organizations->first()->id ?? null;
        
        if (!$organizationId) {
            return redirect()->back()->with('error', 'Bạn không thuộc tổ chức nào.');
        }

        $tenant = User::whereHas('organizationUsers', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId)
                  ->where('role_id', 5);
        })->with([
            'organizationRoles' => function($query) use ($organizationId) {
                $query->wherePivot('organization_id', $organizationId);
            }
        ])->findOrFail($id);

        $roles = Role::where('id', 5)->get();
        
        return view('agent.tenants.edit', compact('tenant', 'roles'));
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $organizationId = $user->organizations->first()->id ?? null;
        
        if (!$organizationId) {
            return redirect()->back()->with('error', 'Bạn không thuộc tổ chức nào.');
        }

        $tenant = User::whereHas('organizationUsers', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId)
                  ->where('role_id', 5);
        })->findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($tenant->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($tenant->id)],
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|exists:roles,id|in:5',
        ]);

        DB::beginTransaction();
        try {
            // Update user
            $updateData = [
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
            ];

            if ($request->password) {
                $updateData['password_hash'] = Hash::make($request->password);
            }

            $tenant->update($updateData);

            // Update organization role
            OrganizationUser::where('organization_id', $organizationId)
                ->where('user_id', $tenant->id)
                ->update(['role_id' => $request->role_id]);

            DB::commit();
            
            return redirect()->route('agent.tenants.index')
                ->with('success', 'Cập nhật thông tin khách hàng thành công.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified tenant.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $organizationId = $user->organizations->first()->id ?? null;
        
        if (!$organizationId) {
            return redirect()->back()->with('error', 'Bạn không thuộc tổ chức nào.');
        }

        $tenant = User::whereHas('organizationUsers', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId)
                  ->where('role_id', 5);
        })->findOrFail($id);

        // Check if tenant has active leases
        $activeLeases = $tenant->leasesAsTenant()
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->count();

        if ($activeLeases > 0) {
            return redirect()->back()
                ->with('error', 'Không thể xóa khách hàng đang có hợp đồng hoạt động.');
        }

        DB::beginTransaction();
        try {
            // Remove from organization
            OrganizationUser::where('organization_id', $organizationId)
                ->where('user_id', $tenant->id)
                ->delete();

            // Soft delete user
            $tenant->delete();

            DB::commit();
            
            return redirect()->route('agent.tenants.index')
                ->with('success', 'Xóa khách hàng thành công.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi xóa: ' . $e->getMessage());
        }
    }


    /**
     * Add resident to lease.
     */
    public function addResident(Request $request, $leaseId)
    {
        $user = Auth::user();
        $organizationId = $user->organizations->first()->id ?? null;
        
        if (!$organizationId) {
            return redirect()->back()->with('error', 'Bạn không thuộc tổ chức nào.');
        }

        $lease = Lease::where('organization_id', $organizationId)
            ->findOrFail($leaseId);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'id_number' => 'nullable|string|max:20',
            'note' => 'nullable|string',
            'create_user_account' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $residentData = [
                'lease_id' => $lease->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'id_number' => $request->id_number,
                'note' => $request->note,
            ];

            // Create user account if requested
            if ($request->create_user_account) {
                $newUser = User::create([
                    'full_name' => $request->name,
                    'phone' => $request->phone,
                    'password_hash' => Hash::make('123456'), // Default password
                    'status' => 1,
                ]);

                // Add to organization with tenant role
                $tenantRole = Role::where('key_code', 'tenant')->first();
                if ($tenantRole) {
                    OrganizationUser::create([
                        'organization_id' => $organizationId,
                        'user_id' => $newUser->id,
                        'role_id' => $tenantRole->id,
                        'status' => 1,
                    ]);
                }

                $residentData['user_id'] = $newUser->id;
            }

            LeaseResident::create($residentData);

            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Thêm người ở thành công.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
