<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Get current manager's organization
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations->first();
        
        if (!$managerOrganization) {
            abort(403, 'Bạn chưa được gán vào tổ chức nào. Vui lòng liên hệ Admin để được hỗ trợ.');
        }

        $query = User::with(['userRoles', 'assignedProperties' => function($q) {
            // Temporarily disable organization scope for assigned properties
            $q->withoutGlobalScope('organization');
        }])
        // Only show users from the same organization as the manager
        ->whereHas('organizations', function($q) use ($managerOrganization) {
            $q->where('organizations.id', $managerOrganization->id);
        });

        // Exclude admin users for manager role
        $query->whereDoesntHave('userRoles', function($q) {
            $q->where('key_code', 'admin');
        });

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role_id')) {
            $query->whereHas('userRoles', function($q) use ($request) {
                $q->where('role_id', $request->role_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Note: Users don't have location data, so location filters are removed

        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get only roles that are actually used by users
        $roles = Role::where('key_code', '!=', 'admin')
            ->whereHas('users')
            ->get();
        
        // Get all geo data for dropdowns (since users don't have location data)
        $provinces = \App\Models\GeoProvince::all();
        $districts = \App\Models\GeoDistrict::all();
        $provinces2025 = \App\Models\GeoProvince2025::all();
        
        // Get wards2025 based on selected province_2025
        $wards2025 = collect();
        if ($request->filled('province_2025')) {
            $wards2025 = \App\Models\GeoWard2025::where('province_code', $request->province_2025)->get();
        }

        return view('manager.users.index', compact('users', 'roles', 'provinces', 'districts', 'provinces2025', 'wards2025'));
    }

    public function create()
    {
        $roles = Role::where('key_code', '!=', 'admin')->get();
        return view('manager.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        try {
            // Get current manager's organization
            $currentUser = Auth::user();
            $managerOrganization = $currentUser->organizations->first();
            
            if (!$managerOrganization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa được gán vào tổ chức nào. Vui lòng liên hệ Admin để được hỗ trợ.'
                ], 403);
            }

            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20|unique:users,phone',
                'password' => 'required|string|min:6',
                'status' => 'nullable|integer|in:0,1',
                'role_id' => 'required|exists:roles,id',
            ]);

            // Check if trying to assign admin role - manager cannot create admin users
            $role = Role::find($validated['role_id']);
            if ($role && $role->key_code === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền tạo tài khoản với vai trò Quản trị hệ thống. Vui lòng chọn vai trò khác.'
                ], 403);
            }

            DB::beginTransaction();
            try {
                $user = User::create([
                    'full_name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password_hash' => Hash::make($validated['password']),
                    'status' => $validated['status'] ?? 1,
                ]);

                // Assign user to manager's organization with role
                $user->organizations()->attach($managerOrganization->id, [
                    'role_id' => $validated['role_id'],
                    'status' => 'active'
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Người dùng đã được tạo thành công!',
                    'user_id' => $user->id
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin không hợp lệ: ' . implode(', ', $e->validator->errors()->all()) . '. Vui lòng kiểm tra lại và thử lại.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage() . '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.'
            ], 500);
        }
    }

    public function show($id)
    {
        // Get current manager's organization
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations->first();
        
        if (!$managerOrganization) {
            abort(403, 'Bạn chưa được gán vào tổ chức nào. Vui lòng liên hệ Admin để được hỗ trợ.');
        }

        $user = User::with(['userRoles', 'userProfile'])
            ->whereHas('organizations', function($q) use ($managerOrganization) {
                $q->where('organizations.id', $managerOrganization->id);
            })
            ->findOrFail($id);
        
        // Check if user is admin - manager cannot view admin users
        if ($user->userRoles->where('key_code', 'admin')->count() > 0) {
            abort(403, 'Bạn không có quyền xem thông tin tài khoản Quản trị hệ thống. Vui lòng liên hệ Admin để được hỗ trợ.');
        }
        
        return view('manager.users.show', compact('user'));
    }

    public function edit($id)
    {
        // Get current manager's organization
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations->first();
        
        if (!$managerOrganization) {
            abort(403, 'Bạn chưa được gán vào tổ chức nào. Vui lòng liên hệ Admin để được hỗ trợ.');
        }

        $user = User::with(['userRoles'])
            ->whereHas('organizations', function($q) use ($managerOrganization) {
                $q->where('organizations.id', $managerOrganization->id);
            })
            ->findOrFail($id);
        
        // Check if user is admin - manager cannot edit admin users
        if ($user->userRoles->where('key_code', 'admin')->count() > 0) {
            abort(403, 'Bạn không có quyền chỉnh sửa tài khoản Quản trị hệ thống. Vui lòng liên hệ Admin để được hỗ trợ.');
        }
        
        $roles = Role::where('key_code', '!=', 'admin')->get();
        return view('manager.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        // Get current manager's organization
        $currentUser = Auth::user();
        $managerOrganization = $currentUser->organizations->first();
        
        if (!$managerOrganization) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa được gán vào tổ chức nào. Vui lòng liên hệ Admin để được hỗ trợ.'
            ], 403);
        }

        $user = User::with(['userRoles'])
            ->whereHas('organizations', function($q) use ($managerOrganization) {
                $q->where('organizations.id', $managerOrganization->id);
            })
            ->findOrFail($id);
        
        // Check if user is admin - manager cannot update admin users
        if ($user->userRoles->where('key_code', 'admin')->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật tài khoản Quản trị hệ thống. Vui lòng liên hệ Admin để được hỗ trợ.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
                'password' => 'nullable|string|min:6',
                'status' => 'nullable|integer|in:0,1',
                'role_id' => 'required|exists:roles,id',
            ]);

            // Check if trying to assign admin role - manager cannot assign admin role
            $role = Role::find($validated['role_id']);
            if ($role && $role->key_code === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền gán vai trò Quản trị hệ thống. Vui lòng chọn vai trò khác.'
                ], 403);
            }

            DB::beginTransaction();
            try {
                $updateData = [
                    'full_name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'status' => $validated['status'] ?? 1,
                ];

                // Update password if provided
                if (!empty($validated['password'])) {
                    $updateData['password_hash'] = Hash::make($validated['password']);
                }

                $user->update($updateData);

                // Update role
                $user->userRoles()->sync([$validated['role_id']]);
                
                // Update organization role
                $user->organizations()->updateExistingPivot($managerOrganization->id, [
                    'role_id' => $validated['role_id']
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Người dùng đã được cập nhật thành công!'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin không hợp lệ: ' . implode(', ', $e->validator->errors()->all()) . '. Vui lòng kiểm tra lại và thử lại.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage() . '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Get current manager's organization
            $currentUser = Auth::user();
            $managerOrganization = $currentUser->organizations->first();
            
            if (!$managerOrganization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa được gán vào tổ chức nào. Vui lòng liên hệ Admin để được hỗ trợ.'
                ], 403);
            }

            $user = User::with(['userRoles'])
                ->whereHas('organizations', function($q) use ($managerOrganization) {
                    $q->where('organizations.id', $managerOrganization->id);
                })
                ->findOrFail($id);
            
            // Check if user is admin - manager cannot delete admin users
            if ($user->userRoles->where('key_code', 'admin')->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xóa tài khoản Quản trị hệ thống. Vui lòng liên hệ Admin để được hỗ trợ.'
                ], 403);
            }
            
            // Don't allow deleting the current user
            $currentUserId = Auth::check() ? Auth::user()->id : null;
            if ($user->id === $currentUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không thể xóa tài khoản của chính mình. Vui lòng liên hệ Admin để được hỗ trợ.'
                ], 422);
            }
            
            // Soft delete the user
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Người dùng đã được xóa thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}
