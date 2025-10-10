<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\User_role;

class EmailAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Only allow active users to login
        $attempt = Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'status' => 1,
        ], (bool) $request->boolean('remember'));

        if (! $attempt) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Thông tin đăng nhập không đúng hoặc tài khoản bị khóa.']);
        }

        $request->session()->regenerate();

        // Resolve role and store in session
        $user = Auth::user();
        $role = $this->resolvePrimaryRole($user);
        if ($role) {
            $request->session()->put('auth_role_id', $role['id']);
            $request->session()->put('auth_role_key', $role['key_code']);
        }

        // Store organization information in session for access control
        try {
            /** @var \App\Models\User $user */
            $organization = $user->organizations()->first();
            if ($organization) {
                $request->session()->put('auth_organization_id', $organization->id);
                $request->session()->put('auth_organization_name', $organization->name);
            }
        } catch (\Exception $e) {
            // User might not have organizations method or no organizations
        }

        // Redirect per role key to specific dashboards
        $roleKey = $role['key_code'] ?? null;
        $routeByRole = [
            'admin' => 'superadmin.dashboard', // Redirect admin to Super Admin dashboard
            'manager' => 'manager.dashboard',
            'agent' => 'agent.dashboard',
            'landlord' => 'landlord.dashboard',
            'tenant' => 'tenant.dashboard',
        ];
        $target = $routeByRole[$roleKey] ?? 'dashboard';

        return redirect()->route($target);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = new User();
        $user->full_name = $data['full_name'];
        $user->email = $data['email'];
        $user->password_hash = Hash::make($data['password']);
        $user->status = 1;
        $user->save();

        // Attach default tenant role
        $tenantRoleId = DB::table('roles')->where('key_code', 'tenant')->value('id');
        if ($tenantRoleId) {
            User_role::updateOrCreate(
                ['user_id' => $user->id, 'role_id' => $tenantRoleId],
                []
            );
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Resolve role and store in session (should be tenant for new users)
        $role = $this->resolvePrimaryRole($user);
        if ($role) {
            $request->session()->put('auth_role_id', $role['id']);
            $request->session()->put('auth_role_key', $role['key_code']);
        }

        // Store organization information in session for access control
        try {
            /** @var \App\Models\User $user */
            $organization = $user->organizations()->first();
            if ($organization) {
                $request->session()->put('auth_organization_id', $organization->id);
                $request->session()->put('auth_organization_name', $organization->name);
            }
        } catch (\Exception $e) {
            // User might not have organizations method or no organizations
        }

        // Redirect per role key to specific dashboards
        $roleKey = $role['key_code'] ?? null;
        $routeByRole = [
            'admin' => 'superadmin.dashboard', // Redirect admin to Super Admin dashboard
            'manager' => 'manager.dashboard',
            'agent' => 'agent.dashboard',
            'landlord' => 'landlord.dashboard',
            'tenant' => 'tenant.dashboard',
        ];
        $target = $routeByRole[$roleKey] ?? 'dashboard';

        return redirect()->route($target);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Clear organization information from session
        $request->session()->forget(['auth_organization_id', 'auth_organization_name']);
        
        return redirect()->route('home');
    }

    private function resolvePrimaryRole(User $user): ?array
    {
        $record = DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $user->id)
            ->orderBy('roles.id')
            ->select('roles.id', 'roles.key_code')
            ->first();

        if (! $record) {
            return null;
        }

        return ['id' => (int) $record->id, 'key_code' => (string) $record->key_code];
    }
}


