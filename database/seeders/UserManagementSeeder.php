<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Block1\Access;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Test data for Block 1: roles, their permissions, departments and user accounts.
 * Uses firstOrCreate everywhere, so it is safe to run more than once and next to the
 * other blocks' seeders (they create the same 'manager' / 'employee' roles and departments).
 * Every seeded account uses the password 12345678.
 */
class UserManagementSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles (config/permissions.php)
        $roles = [];
        foreach (config('permissions.roles') as $name => $description) {
            $roles[$name] = Role::firstOrCreate(['role_name' => $name], ['description' => $description]);
        }

        // 2. Default permissions of each role, stored as "<role>:<permission>"
        foreach (config('permissions.defaults') as $roleName => $permissionNames) {
            foreach ($permissionNames as $permission) {
                Permission::firstOrCreate(
                    ['permission_name' => Access::storedName($roleName, $permission)],
                    ['role_id' => $roles[$roleName]->role_id]
                );
            }
        }
        Access::forgetCache();

        // 3. Departments (same names as the other blocks' seeders)
        $departments = [];
        foreach (['Software Engineering', 'Information Systems', 'Artificial Intelligence', 'Networks and Communications'] as $name) {
            $departments[] = Department::firstOrCreate(['department_name' => $name]);
        }

        // 4. Users: one per spec actor + department managers / employees
        $users = [
            ['admin',        'System Administrator',   'admin',           0, 'active',   '1985-03-12'],
            ['manager',      'Dr. Department Manager', 'manager',         0, 'active',   '1980-06-01'],
            ['employee',     'Staff Employee',         'employee',        0, 'active',   '1995-09-20'],
            ['abdo_khoury',  'Dr. Abdo Khoury',        'employee',        0, 'active',   '1978-11-05'],
            ['sara_ali',     'Sara Ali',               'employee',        1, 'active',   '1997-01-14'],
            ['omar_hasan',   'Omar Hasan',             'employee',        2, 'inactive', '1993-04-30'],
            ['rana_saleh',   'Dr. Rana Saleh',         'instructor',      1, 'active',   '1982-07-22'],
            ['khaled_issa',  'Dr. Khaled Issa',        'instructor',      2, 'active',   '1975-12-02'],
            ['lina_haddad',  'Lina Haddad',            'student',         0, 'active',   '2004-02-18'],
            ['yousef_nour',  'Yousef Nour',            'student',         3, 'inactive', '2003-10-09'],
            ['huda_affairs', 'Huda Mansour',           'student_affairs', 1, 'active',   '1990-08-08'],
            ['fadi_exams',   'Fadi Karam',             'exam_affairs',    2, 'active',   '1988-05-27'],
        ];

        foreach ($users as $i => [$username, $fullName, $roleName, $departmentIndex, $status, $birthDate]) {
            User::firstOrCreate(
                ['username' => $username],
                [
                    'full_name'     => $fullName,
                    'email'         => $username . '@attendance.edu',
                    'password'      => Hash::make('12345678'),
                    'phone'         => '09' . str_pad((string) (31000000 + $i * 111111), 8, '0', STR_PAD_LEFT),
                    'role_id'       => $roles[$roleName]->role_id,
                    'department_id' => $departments[$departmentIndex]->department_id,
                    'birth_date'    => $birthDate,
                    'status'        => $status,
                ]
            );
        }
    }
}
