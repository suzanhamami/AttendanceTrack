<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Fallback login used only while the project has no `login` route of its own
 * (i.e. when Block 1 runs alone). After the blocks are merged, the shared AuthController
 * handles login; the spec checks still run through Block1LoginGuard either way.
 */
class Block1LoginController extends Controller
{
    public function show()
    {
        return Auth::check() ? redirect()->route('users.dashboard') : view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string',
        ]);

        // Existence, password and active status were already checked by Block1LoginGuard.
        if (!Auth::attempt($credentials + ['status' => 'active'])) {
            return back()->withErrors(['username' => 'Invalid credentials.'])->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('users.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
