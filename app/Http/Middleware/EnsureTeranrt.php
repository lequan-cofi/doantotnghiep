<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTeranrt
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $roleKey = (string) ($request->session()->get('auth_role_key') ?? '');
        if ($roleKey !== 'tenant') {
            abort(403);
        }

        return $next($request);
    }
}


