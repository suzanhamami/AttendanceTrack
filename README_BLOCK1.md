# Block 1 - Users, Roles and Permissions Management (AttendanceTrack)

Branch: `user-management`. Seeded accounts use the password `12345678`
(`admin`, `manager`, `employee`, `rana_saleh` (instructor), `lina_haddad` (student), ...).

## Run
```
composer install
php artisan migrate --seed
php artisan serve        # then open /login
```
Tests on MySQL (uses its own empty database `attendancetrack_block1_phpunit`, create it first):
```
php artisan test --configuration=phpunit.block1-mysql.xml
```

## How Block 1 plugs in (no shared file edited)
- `App\Providers\Block1ServiceProvider` (listed in `bootstrap/providers.php`) loads `routes/users.php`,
  adds the login checks of the spec (`Block1LoginGuard`: required fields, 5-attempt lock, a specific
  message for unknown user / wrong password / deactivated account) and stores
  `user_id`, `department_id`, `role` in the session on login.
- These work with the team's shared `AuthController`: its `/login` routes in `routes/web.php` replace
  Block 1's fallback login automatically.
- Permissions: table `permissions` (`permission_name` UNIQUE, stored as `<role>:<permission>`,
  e.g. `employee:users.view`), managed on the Roles & Permissions page.

## users table = spec table 1 (migration `2026_11_05_150000_apply_spec_constraints_to_users_table`)
`phone`, `department_id`, `birth_date` are **NOT NULL**, `CHECK (age >= 18)`, `created_at DEFAULT CURRENT_TIMESTAMP`,
and deleting a role / department that still has users is refused (RESTRICT).

### What the other blocks must change when merging (2 places)
Every user must now be created with `phone` (10 digits) and `birth_date` (18+), and a `department_id`:
1. `database/seeders/CourseSeeder.php` - the three `User::firstOrCreate(...)` calls (manager, employee,
   abdo_khoury): add e.g. `'phone' => '0930000001', 'birth_date' => '1985-01-01',`
2. `tests/Feature/ExamModuleTest.php` - `makeUser()`: add `'phone' => '0930000001', 'birth_date' => '1985-01-01',`

(Running `UserManagementSeeder` first also works for CourseSeeder: it creates those three accounts already.)

## Merge conflicts
Block 1's copies of the shared files are byte-identical to a team copy; the team's own copies differ:
- `app/Models/User.php`: take the **exam-management** copy. The course/student copies return an empty
  `department` (missing keys in `department()`).
- `app/Models/Role.php`, `app/Models/Department.php`: either copy works for Block 1.
