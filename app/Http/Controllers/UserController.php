<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Support\Block1\Access;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Users management.
 *  - employee + manager : search (Ajax grid), add, edit, view details
 *  - manager only       : change status (active / inactive), delete
 * Routes are protected by the CheckPermission middleware; every action checks again here
 * (controller-level authorization, as required by the spec).
 */
class UserController extends Controller
{
    /** Folder (inside /public) where profile photos are stored. */
    private const PHOTO_DIR = 'uploads/users';

    /** Advanced search with filters. Ajax requests receive only the grid fragment. */
    public function index(Request $request)
    {
        $this->authorizePermission('users.view');

        $filters = $request->validate([
            'search'        => ['nullable', 'string', 'max:100'],
            'role_id'       => ['nullable', 'integer'],
            'status'        => ['nullable', 'in:active,inactive'],
            'department_id' => ['nullable', 'integer'],
        ]);

        $users = User::with('role')
            ->when($filters['search'] ?? null, function ($query, $search) {
                // Eloquent binds the value as a prepared-statement parameter (no SQL injection).
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['role_id'] ?? null, fn ($q, $roleId) => $q->where('role_id', $roleId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['department_id'] ?? null, fn ($q, $departmentId) => $q->where('department_id', $departmentId))
            ->orderByDesc('user_id')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('users._table', compact('users'));
        }

        return view('users.index', [
            'users'       => $users,
            'roles'       => Role::orderBy('role_name')->get(),
            'departments' => Department::orderBy('department_name')->get(),
        ]);
    }

    public function create()
    {
        $this->authorizePermission('users.create');

        return view('users.create', $this->formData());
    }

    public function store(UserRequest $request)
    {
        $data = $request->safe()->except(['photo', 'password', 'password_confirmation']);
        $data['password'] = Hash::make($request->input('password'));    // bcrypt
        // New accounts are active unless the manager chose another status.
        $data['status'] = $data['status'] ?? 'active';

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storePhoto($request->file('photo'));
        }

        $user = User::create($data);

        return redirect()->route('users.show', $user)->with('success', "User \"{$user->full_name}\" was added successfully.");
    }

    /** Full user details with his activity in the system. */
    public function show(User $user)
    {
        $this->authorizePermission('users.view');

        $user->load('role');

        return view('users.show', [
            'user'         => $user,
            'permissions'  => Access::permissionsOfRole($user->role_id),
            'linkedCounts' => Access::linkedRecordCounts($user),
            'linkedItems'  => $this->linkedItems($user),
            'lastActivity' => $this->lastActivity($user),
        ]);
    }

    public function edit(User $user)
    {
        $this->authorizePermission('users.edit');
        $this->authorizeTarget($user);

        return view('users.edit', $this->formData() + ['user' => $user]);
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorizeTarget($user);

        $data = $request->safe()->except(['photo', 'password', 'password_confirmation']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        // The manager cannot deactivate his own account.
        if ($user->is($request->user())) {
            unset($data['status']);
        }

        if ($request->hasFile('photo')) {
            $this->deletePhoto($user->photo);
            $data['photo'] = $this->storePhoto($request->file('photo'));
        } elseif ($request->boolean('remove_photo')) {
            $this->deletePhoto($user->photo);
            $data['photo'] = null;
        }

        $user->update($data);

        if (($data['status'] ?? null) === 'inactive') {
            $this->endSessions($user);
        }

        return redirect()->route('users.show', $user)->with('success', 'User information was updated successfully.');
    }

    /** Manager only: activate / deactivate an account. */
    public function toggleStatus(Request $request, User $user)
    {
        $this->authorizePermission('users.status');

        if ($user->is($request->user())) {
            return $this->respond($request, false, 'You cannot deactivate your own account.');
        }

        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        if ($user->status === 'inactive') {
            $this->endSessions($user);
        }

        $label = $user->status === 'active' ? 'activated' : 'deactivated';

        return $this->respond($request, true, "The account of \"{$user->full_name}\" was {$label}.", ['status' => $user->status]);
    }

    /** Manager only: delete a user that is not linked to any lecture, course or section. */
    public function destroy(Request $request, User $user)
    {
        $this->authorizePermission('users.delete');

        if ($user->is($request->user())) {
            return $this->respond($request, false, 'You cannot delete your own account.');
        }

        $linked = array_filter(Access::linkedRecordCounts($user));
        if ($linked) {
            $details = collect($linked)->map(fn ($n, $t) => "{$n} {$t}")->implode(', ');
            return $this->respond($request, false, "\"{$user->full_name}\" cannot be deleted because he is linked to: {$details}.");
        }

        $this->deletePhoto($user->photo);
        $user->delete();

        return $this->respond($request, true, "User \"{$user->full_name}\" was deleted.");
    }

    /* ------------------------------------------------------------------ helpers */

    private function authorizePermission(string $permission): void
    {
        abort_unless(Access::has(auth()->user(), $permission), 403, 'Unauthorized access: You do not have permission to perform this action.');
    }

    /** Non-privileged staff (employee) cannot edit admin / manager accounts. */
    private function authorizeTarget(User $target): void
    {
        abort_if(Access::isPrivileged($target) && !Access::isPrivileged(auth()->user()), 403, 'You are not allowed to edit this account.');
    }

    private function formData(): array
    {
        $actor = auth()->user();

        // Employees only see roles they are allowed to assign.
        $roles = Role::orderBy('role_name')->get()
            ->filter(fn (Role $role) => Access::isPrivileged($actor) || !Access::roleIsPrivileged($role->role_id))
            ->values();

        return [
            'roles'       => $roles,
            'departments' => Department::orderBy('department_name')->get(),
            'canStatus'   => Access::has($actor, 'users.status'),
        ];
    }

    /** Stores the uploaded photo under public/uploads/users with a random name and returns its relative path. */
    private function storePhoto(UploadedFile $file): string
    {
        $name = Str::uuid() . '.' . $file->extension();       // extension guessed from the real content
        $file->move(public_path(self::PHOTO_DIR), $name);

        return self::PHOTO_DIR . '/' . $name;
    }

    private function deletePhoto(?string $path): void
    {
        if ($path && str_starts_with($path, self::PHOTO_DIR . '/')) {
            File::delete(public_path($path));
        }
    }

    /** Removes the stored sessions of a user, so a deactivated account is logged out at once. */
    private function endSessions(User $user): void
    {
        if (config('session.driver') === 'database' && Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->user_id)->delete();
        }
    }

    /** Last few courses / sections / lectures the user teaches (tables from other blocks, when present). */
    private function linkedItems(User $user): array
    {
        $items = [];
        $sources = [
            'courses'  => ['course_name', 'course_code'],
            'sections' => ['section_name', null],
            'lectures' => ['title', 'lecture_date'],
        ];

        foreach ($sources as $table => [$titleColumn, $extraColumn]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'instructor_id') || !Schema::hasColumn($table, $titleColumn)) {
                continue;
            }
            // The second column is optional: skip it when that block's table does not have it.
            $hasExtra = $extraColumn && Schema::hasColumn($table, $extraColumn);
            $columns = array_filter([$titleColumn . ' as title', $hasExtra ? $extraColumn . ' as extra' : null]);
            $items[$table] = DB::table($table)->where('instructor_id', $user->user_id)->limit(5)->get($columns);
        }

        return $items;
    }

    /** Time of the user's last request, read from the database session store. */
    private function lastActivity(User $user): ?Carbon
    {
        if (config('session.driver') !== 'database' || !Schema::hasTable('sessions')) {
            return null;
        }

        $timestamp = DB::table('sessions')->where('user_id', $user->user_id)->max('last_activity');

        return $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
    }

    /** JSON for Ajax buttons, redirect with a flash message otherwise. */
    private function respond(Request $request, bool $ok, string $message, array $extra = [])
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message] + $extra, $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'error', $message);
    }
}
