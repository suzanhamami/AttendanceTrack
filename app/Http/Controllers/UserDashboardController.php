<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Support\Block1\Access;

/** Home page of Block 1: statistics + dashboard buttons that depend on the user's permissions. */
class UserDashboardController extends Controller
{
    public function index()
    {
        abort_unless(Access::has(auth()->user(), 'users.view'), 403);

        return view('users.dashboard', [
            'totalUsers'    => User::count(),
            'activeUsers'   => User::where('status', 'active')->count(),
            'inactiveUsers' => User::where('status', 'inactive')->count(),
            'roles'         => Role::orderBy('role_id')->get(),
            'userCounts'    => Access::usersPerRole(),
            'latestUsers'   => User::with('role')->latest('created_at')->limit(5)->get(),
        ]);
    }
}
