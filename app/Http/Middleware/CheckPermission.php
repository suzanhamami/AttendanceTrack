<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Http\Middleware;

use App\Support\Block1\Access;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes: ->middleware(CheckPermission::class . ':users.delete')
 * Several permissions may be listed (comma separated); the user needs at least one of them.
 * Permissions come from the `permissions` table (managed on the Roles & Permissions page).
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['username' => 'Please login first.']);
        }

        $user = Auth::user();

        // A deactivated account loses access immediately, even with an open session.
        if ($user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['username' => 'Your account has been deactivated.']);
        }

        foreach ($permissions as $permission) {
            if (Access::has($user, $permission)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized access: You do not have permission to perform this action.');
    }
}
