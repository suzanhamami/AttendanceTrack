<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['username' => 'Please login first.']);
        }

        $user = Auth::user();

        // 1. The role relationship and its role_name are the only source of truth
        //    (no hard-coded role ids: ids are not guaranteed to be the same in every database).
        $userRole = $user->role ? strtolower($user->role->role_name) : null;

        // 2. Verify against allowed roles (a user without a role is always denied)
        $allowedRoles = array_map('strtolower', $roles);

        if ($userRole === null || !in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Unauthorized access: You do not have permission to perform this action.');
        }

        return $next($request);
    }
}