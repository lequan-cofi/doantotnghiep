<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckOrganizationAccess
{
    /**
     * Handle an incoming request.
     * Kiểm tra user chỉ có thể truy cập dữ liệu thuộc organization của mình
     * Admin có quyền truy cập tất cả
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Admin có quyền truy cập tất cả
        $isAdmin = $user->userRoles()->where('key_code', 'admin')->exists();
        if ($isAdmin) {
            return $next($request);
        }

        // Lấy organization của user
        $userOrganization = \App\Models\OrganizationUser::where('user_id', $user->id)->first()?->organization;
        
        if (!$userOrganization) {
            return response()->json([
                'message' => 'Bạn chưa được gắn vào tổ chức nào. Vui lòng liên hệ quản trị viên.'
            ], 403);
        }

        // Lưu organization_id vào request để sử dụng trong controller
        $request->merge(['user_organization_id' => $userOrganization->id]);

        return $next($request);
    }
}