<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs on every web request but only acts on the login form submission (POST /login).
 * Implements the spec's login flow checks, whatever controller then logs the user in:
 *   - username and password are required
 *   - more than 5 failed attempts (username + IP) -> locked for 60 seconds
 *   - account not found / wrong password / account deactivated -> a specific error message each
 * When every check passes, the request continues to the login controller.
 */
class Block1LoginGuard
{
    public const MAX_ATTEMPTS = 5;
    public const LOCK_SECONDS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethod('post') || $request->path() !== 'login') {
            return $next($request);
        }

        $username = trim((string) $request->input('username'));
        $password = (string) $request->input('password');

        if ($username === '' || $password === '') {
            return $this->fail($request, array_filter([
                'username' => $username === '' ? 'The username field is required.' : null,
                'password' => $password === '' ? 'The password field is required.' : null,
            ]));
        }

        $key = 'block1-login|' . Str::lower($username) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->fail($request, ['username' => "Too many login attempts. Please try again in {$seconds} seconds."]);
        }

        $user = User::where('username', $username)->first();

        if (!$user) {
            RateLimiter::hit($key, self::LOCK_SECONDS);
            return $this->fail($request, ['username' => 'No account was found with this username.']);
        }

        if (!Hash::check($password, $user->password)) {
            RateLimiter::hit($key, self::LOCK_SECONDS);
            return $this->fail($request, ['password' => 'The password is incorrect.']);
        }

        if ($user->status !== 'active') {
            return $this->fail($request, ['username' => 'This account is deactivated. Please contact the administrator.']);
        }

        RateLimiter::clear($key);

        return $next($request);
    }

    private function fail(Request $request, array $errors): Response
    {
        return redirect()->back()->withErrors($errors)->withInput($request->only('username'));
    }
}
