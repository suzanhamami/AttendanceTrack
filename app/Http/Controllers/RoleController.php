<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Block1\Access;
use Illuminate\Support\Facades\DB;

/**
 * Manager only: manage the system roles (admin, student affairs, instructor, exam affairs, student,
 * manager, employee, ...) and the permissions granted to each role.
 */
class RoleController extends Controller
{
    public function index()
    {
        $this->authorizeManage();

        $roles = Role::orderBy('role_id')->get();

        return view('roles.index', [
            'roles'       => $roles,
            'permissions' => $roles->mapWithKeys(fn (Role $r) => [$r->role_id => Access::permissionsOfRole($r->role_id)]),
            'userCounts'  => Access::usersPerRole(),
            'groups'      => config('permissions.groups'),
        ]);
    }

    public function store(RoleRequest $request)
    {
        DB::transaction(function () use ($request) {
            $role = Role::create($request->only('role_name', 'description'));
            $this->syncPermissions($role, $request->input('permissions', []));
        });

        return redirect()->route('roles.index')->with('success', "Role \"{$request->role_name}\" was added.");
    }

    public function edit(Role $role)
    {
        $this->authorizeManage();

        return view('roles.edit', [
            'role'        => $role,
            'permissions' => Access::permissionsOfRole($role->role_id),
            'groups'      => config('permissions.groups'),
        ]);
    }

    public function update(RoleRequest $request, Role $role)
    {
        $permissions = $request->input('permissions', []);

        // Never let the manager lock himself out of this page.
        if ((int) $role->role_id === (int) $request->user()->role_id && !in_array('roles.manage', $permissions, true)) {
            return back()->withInput()->with('error', 'You cannot remove "roles.manage" from your own role.');
        }

        DB::transaction(function () use ($request, $role, $permissions) {
            $data = ['description' => $request->input('description')];
            // System roles keep their name: the blocks check them by name (e.g. role:manager,employee).
            if (!Access::isProtectedRole($role)) {
                $data['role_name'] = $request->input('role_name');
            }
            $role->update($data);
            $this->syncPermissions($role, $permissions);     // also rewrites the "<role>:" prefix after a rename
        });

        return redirect()->route('roles.index')->with('success', "Role \"{$role->role_name}\" was updated.");
    }

    public function destroy(Role $role)
    {
        $this->authorizeManage();

        if (Access::isProtectedRole($role)) {
            return back()->with('error', "The \"{$role->role_name}\" role is used by the system and cannot be deleted.");
        }

        // A role that still has users must never be deleted.
        if (User::where('role_id', $role->role_id)->exists()) {
            return back()->with('error', "The \"{$role->role_name}\" role still has users. Move them to another role first.");
        }

        $role->delete();     // its permission rows are removed by the foreign key (cascade)

        return redirect()->route('roles.index')->with('success', "Role \"{$role->role_name}\" was deleted.");
    }

    private function authorizeManage(): void
    {
        abort_unless(Access::has(auth()->user(), 'roles.manage'), 403, 'Unauthorized access: You do not have permission to perform this action.');
    }

    /** Replaces the role's permissions with the given list. */
    private function syncPermissions(Role $role, array $permissions): void
    {
        Permission::where('role_id', $role->role_id)->delete();

        foreach (array_unique($permissions) as $permission) {
            Permission::create([
                'role_id'         => $role->role_id,
                'permission_name' => Access::storedName($role->role_name, $permission),
            ]);
        }

        Access::forgetCache();
    }
}
