<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Support\Block1;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Block 1 helpers kept outside the shared User / Role models, so the models stay identical to
 * the other blocks' copies (no merge conflicts).
 *
 * Permissions table (spec: permission_name UNIQUE + role_id FK):
 * a row is stored as "<role_name>:<permission>", e.g. "employee:users.view", which keeps every
 * permission_name unique while two roles can still share the same permission.
 */
class Access
{
    public const SEPARATOR = ':';

    /** Per-request cache: role_id => [permission, ...] */
    private static array $cache = [];

    /** Per-request cache: department_id => department_name */
    private static ?array $departments = null;

    /** Every permission name of the catalog in config/permissions.php. */
    public static function catalog(): array
    {
        return array_keys(array_merge(...array_values(config('permissions.groups'))));
    }

    /** Stored value of a permission row for a role. */
    public static function storedName(string $roleName, string $permission): string
    {
        return $roleName . self::SEPARATOR . $permission;
    }

    /** Permissions granted to a role, e.g. ['users.view', 'users.create']. */
    public static function permissionsOfRole(?int $roleId): array
    {
        if (!$roleId) {
            return [];
        }

        return self::$cache[$roleId] ??= Permission::where('role_id', $roleId)->pluck('permission_name')
            ->map(fn ($name) => str_contains($name, self::SEPARATOR) ? substr($name, strpos($name, self::SEPARATOR) + 1) : $name)
            ->values()->all();
    }

    public static function forgetCache(): void
    {
        self::$cache = [];
        self::$departments = null;
    }

    public static function has(?User $user, string $permission): bool
    {
        return $user !== null && in_array($permission, self::permissionsOfRole($user->role_id), true);
    }

    /** Privileged accounts (admin / manager) are the ones whose role may manage roles. */
    public static function isPrivileged(User $user): bool
    {
        return self::has($user, 'roles.manage');
    }

    public static function roleIsPrivileged(int $roleId): bool
    {
        return in_array('roles.manage', self::permissionsOfRole($roleId), true);
    }

    /** System roles cannot be renamed or deleted (other blocks check them by name). */
    public static function isProtectedRole(Role $role): bool
    {
        return in_array(strtolower($role->role_name), config('permissions.protected_roles', []), true);
    }

    /** Number of users per role_id. */
    public static function usersPerRole(): array
    {
        return User::query()->selectRaw('role_id, COUNT(*) as total')->groupBy('role_id')->pluck('total', 'role_id')->all();
    }

    /**
     * Department name of a user. Read by id instead of through User::department(), because some
     * copies of the shared User model guess the wrong foreign key for that relation.
     */
    public static function departmentName(User $user): string
    {
        self::$departments ??= Department::pluck('department_name', 'department_id')->all();

        return self::$departments[$user->department_id] ?? '-';
    }

    public static function photoUrl(User $user): ?string
    {
        return $user->photo ? asset($user->photo) : null;
    }

    public static function birthDate(User $user): ?Carbon
    {
        return $user->birth_date ? Carbon::parse($user->birth_date) : null;
    }

    /**
     * Records of other blocks that reference this user as instructor. Each table is only counted
     * when it exists in the current database (the tables belong to other blocks).
     *
     * @return array<string,int>
     */
    public static function linkedRecordCounts(User $user): array
    {
        $counts = [];

        foreach (['courses', 'sections', 'lectures'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'instructor_id')) {
                $counts[$table] = DB::table($table)->where('instructor_id', $user->user_id)->count();
            }
        }

        return $counts;
    }
}
