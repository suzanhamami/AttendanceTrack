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

        // 1. Try resolving via relationship
        $userRole = null;
        if ($user->role) {
            $userRole = strtolower($user->role->role_name);
        }

        // 2. Fallback: if role_id is 1, treat as manager
        if (!$userRole && $user->role_id == 1) {
            $userRole = 'manager';
        } elseif (!$userRole && $user->role_id == 2) {
            $userRole = 'employee';
        }

        // 3. Verify against allowed roles
        $allowedRoles = array_map('strtolower', $roles);

        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Unauthorized access: You do not have permission to perform this action.');
        }

        return $next($request);
    }
}