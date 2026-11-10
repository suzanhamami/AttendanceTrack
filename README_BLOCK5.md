# Block 5 - Exam & Grades Management (AttendanceTrack)

Status: committed on `exam-management` (not pushed). 32 tests pass on MySQL/MariaDB (XAMPP).
Users of this block land on the Block 5 dashboard (`/exams/dashboard`) after login.
Branch: `exam-management` only. This branch is Block 5-only: the student CRUD (StudentController, student views and
routes) is NOT copied here; only what Block 5 needs from the other modules is.

## Safety rules for this module
- Your real `.env` and your real database are never edited or used for Block 5 testing.
- Never run `migrate`, `migrate:fresh` or `db:seed` without `--env=testing` (manual runs) or the test configuration (tests).
- `.env.testing` contains your MySQL credentials: never `git add` or commit it.

## One-time setup
1. In phpMyAdmin create an EMPTY database named `attendancetrack_block5_test` (utf8mb4_unicode_ci).
2. Create a NEW file named `.env.testing` in the project root (same folder as `.env`) with the content below, and fill
   in the host, port, username and password of YOUR MySQL (check them in XAMPP / phpMyAdmin; do not guess).
   Leave `DB_DATABASE=attendancetrack_block5_test`. (Tools could not create `.env*` files for you, so you create it.)

```
# Copy this file to  .env.testing  (same folder) and fill in YOUR MySQL settings. Do NOT commit .env.testing.
# It is used (1) by the automated tests and (2) by manual runs with  --env=testing.
# Your real .env is never read or changed while .env.testing exists.

APP_NAME=AttendanceTrack
APP_ENV=testing
APP_KEY=base64:kyZ/sy078g0n5hWY0TR6zNCig6mR8DSn+w0KXJg6h6M=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
# Verify these in XAMPP / phpMyAdmin - do not assume them:
DB_HOST=PUT_THE_MYSQL_HOST_HERE
DB_PORT=PUT_THE_MYSQL_PORT_HERE
DB_DATABASE=attendancetrack_block5_test
DB_USERNAME=PUT_YOUR_MYSQL_USERNAME_HERE
DB_PASSWORD=PUT_YOUR_MYSQL_PASSWORD_HERE

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```
3. `composer install` (if `vendor` does not exist yet).
4. The old skeleton migration `0001_01_01_000000_create_users_table.php` is deleted on this branch, like on
   student-management and course-management (see "Users migration conflict" below).

## Users migration conflict (fresh database vs existing team database)
Laravel runs migrations in file-name order. `0001_01_01_000000_create_users_table.php` (skeleton: `users(id, name...)`,
`password_reset_tokens`, `sessions`) runs BEFORE the team's `2026_11_05_140908_create_users_table.php`
(`users(user_id, full_name, username, role_id...)`) and `2026_11_05_144809_create_sessions_table.php` (`sessions`).
With both files present on an empty database the second `create users` fails ("table already exists").
The student-management and course-management branches already delete the skeleton file (the sessions migration is its
rename); user-management and attendance-management still contain it, so expect the same conflict when those merge.
- Fresh / empty database (also the test database): delete the skeleton file before `migrate`.
  Nothing else is needed. (`password_reset_tokens` is no longer created; the app has no password-reset feature.)
- Existing team database that was built from the team's migrations (its `migrations` table lists
  `2026_11_05_*` and has no `0001_01_01_000000_create_users_table` row): deleting the file changes nothing; `migrate`
  only adds the three Block 5 migrations (`2026_11_10_*`, they sort after all `2026_11_05_*`).
- Existing database that still has the skeleton `users(id, name)` table: it does not match the team's schema.
  Do NOT run `migrate:fresh` on a shared database; create a new empty database (or ask the team) instead.
- A leftover `migrations` row for a deleted file is harmless (`migrate` ignores it; `migrate:rollback` only prints
  "Migration not found" for it).
- Read-only check (phpMyAdmin or `php artisan migrate:status --env=testing`): look at the `migrations` table and at
  whether `users` has a `user_id` column.
On the PC this was checked in (phpMyAdmin): no AttendanceTrack database exists yet, so nothing has been applied.

## Check the target database BEFORE any migration (read-only)
    php artisan db:show --env=testing
The output must say database `attendancetrack_block5_test`. If it shows anything else, STOP and fix `.env.testing`.

## Automated tests (isolated, forced to the test database)
    php artisan test --configuration=phpunit.mysql.xml
`phpunit.xml` is unchanged (in-memory SQLite). The Block 5 feature tests refuse to run on any database other than
`attendancetrack_block5_test`, and they check this before RefreshDatabase migrates anything.

## Seeders: independent classes, fixed order
`DatabaseSeeder` and `UserFactory` are intentionally NOT changed (still Laravel's stock ones: they use a `name` column
that does not exist in this schema, so plain `db:seed` / `migrate --seed` fail). Run the seeders one by one with
`--class=`, in this order:

| # | Seeder | Needs | Creates |
|---|--------|-------|---------|
| 1 | CourseSeeder  | migrated tables | roles (manager, employee), departments, semesters, users (manager, employee, abdo_khoury), courses |
| 2 | StudentSeeder | CourseSeeder (stops with a clear message listing what is missing) | students, sections, student_section rows |
| 3 | ExamSeeder    | StudentSeeder for sample exams/grades (with no sections it only creates the exam types and warns) | exam types, exams, grades |

All three use `firstOrCreate`, so they can be repeated without duplicates. `ExamSeeder` does not need users or roles.

## Manual try-out on the test database

    php artisan migrate --env=testing
    php artisan db:seed --class=CourseSeeder --env=testing
    php artisan db:seed --class=StudentSeeder --env=testing
    php artisan db:seed --class=ExamSeeder --env=testing
    php artisan serve --env=testing
Open http://127.0.0.1:8000/login (manager / employee, password 12345678), then use "Exams" in the navbar.
Check that the app only shows the seeded sample data (this confirms it uses the test database).

## What to try
Employee: Exams grid -> filters (type, course, section, date, text; updates without reload) -> Add New Exam
(invalid values: max 5, min >= max, duration 0, section of another course) -> details -> Record Grades
(999, -1, abc, empty form, future date; edit a grade) -> edit exam. No delete button; 403 on /results and /exam-types.
Manager: also delete an exam (blocked when it has grades), Exam Types (add/rename/delete, blocked when used),
Results (search a student -> Issue statement -> Print / Save as PDF).

## Environment notes
- Required PHP: `^8.2` (composer.json / composer.lock: laravel/framework v12.71.0; the lock file contains no package that
  needs PHP 8.3+). XAMPP on this PC ships PHP 8.2.12 (ZTS build, per `C:\xampp\php\news.txt` / `snapshot.txt`).
  Run `php -v` and `where php` yourself: the listing of `C:\xampp\php` did not contain `php.exe`, so check that the
  `php` on your PATH is the one you expect. `composer --version` must work as well.
- Time zone: the app uses UTC unless `APP_TIMEZONE` is set. For Damascus use `APP_TIMEZONE=Asia/Damascus`
  (add it to `.env.testing` / `.env`); otherwise "recording date cannot be in the future" and the default date of the grade
  form follow UTC, which is up to 3 hours behind.
- `run-block5.bat` (an old local helper, not committed): do NOT run it, delete it. It switches git branches
  (`git checkout -b exam-test origin/student-management`), appends to `routes\web.php`, copies `.env.example` to `.env`
  (SQLite), and runs `php artisan migrate:fresh` on whatever database `.env` points to, which would wipe it; SQLite also
  cannot run the sections migration (`ALTER TABLE ... ADD CONSTRAINT ... CHECK`).

## What was integrated from other modules (copied unchanged from student-management)
Models Role, Department, Semester, User, Course, Section, Student, StudentSection; their migrations; CheckRole;
auth/login view; CourseSeeder and StudentSeeder (sample data). Small edits for navigation: bootstrap/app.php (role alias),
routes/web.php (login routes + `require exams.php`), AuthController and layouts/app (redirects/links point to Exams).

## Small fixes made in the copied teammate files (tell the owners; they are not Block 5 code)
- `CheckRole.php`: no `role_id` 1/2 fallback; the role comes only from the `role` relation (`role_name`).
- `StudentSeeder.php`: stops with a clear message when CourseSeeder has not run.
- `Role::users()`, `Department::courses()`, `Semester::courses()`, `User::department()`, `User::courses()`: explicit foreign
  and local keys. With custom primary keys Laravel's default guess is wrong (`role_role_id`, `department_department_id`...).
- `Section::students()`: `withTimestamps()` replaced by `withPivot(..., 'created_at')`, because `student_section` has no
  `updated_at` column (and `Section::UPDATED_AT` is null).
- `tests/Unit/RelationKeysTest.php` checks all of these keys without running any query.
