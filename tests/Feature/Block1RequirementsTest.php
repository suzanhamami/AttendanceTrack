<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\UserManagementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Acceptance tests that follow the project PDF, Block 1 section by section:
 *   A. Login / logout / sessions / page protection (shared requirements)
 *   B. Dashboard buttons by role (manager vs employee)
 *   C. Block 1 functions (add, search, edit, details, status, delete, roles)
 *   D. Block 1 validations
 *   E. Data protection (hashing, XSS, SQL injection, CSRF, uploaded files)
 * (UserManagementTest covers the permission edge cases.)
 */
class Block1RequirementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserManagementSeeder::class);
    }

    private function user(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    private function roleId(string $name): int
    {
        return Role::where('role_name', $name)->value('role_id');
    }

    private function departmentId(string $name): int
    {
        return DB::table('departments')->where('department_name', $name)->value('department_id');
    }

    private function newUserData(array $overrides = []): array
    {
        return array_merge([
            'full_name'             => 'Maya Khalil',
            'username'              => 'maya_k',
            'email'                 => 'maya@attendance.edu',
            'password'              => 'pass1234',
            'password_confirmation' => 'pass1234',
            'phone'                 => '0944556677',
            'role_id'               => $this->roleId('instructor'),
            'department_id'         => $this->departmentId('Information Systems'),
            'birth_date'            => '1992-03-15',
            'status'                => 'active',
        ], $overrides);
    }

    private function tinyPng(string $name = 'me.png'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    /* =====================================================================
     | A. Login, sessions, protection (PDF: آلية عمل تسجيل الدخول + الجلسات)
     * ===================================================================== */

    public function test_A1_login_page_has_only_username_and_password(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('name="username"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertSame(2, substr_count($html, '<input type="') - substr_count($html, 'type="hidden"'));
    }

    public function test_A2_username_and_password_are_required(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);
    }

    public function test_A3_session_stores_user_id_department_and_role(): void
    {
        $manager = $this->user('manager');

        // (the redirect target belongs to the shared login controller, so it is not asserted here)
        $this->post('/login', ['username' => 'manager', 'password' => '12345678'])
            ->assertRedirect()
            ->assertSessionHas('user_id', $manager->user_id)
            ->assertSessionHas('department_id', $manager->department_id)
            ->assertSessionHas('role', 'manager');
    }

    public function test_A4_session_expires_after_30_minutes_of_inactivity(): void
    {
        $this->assertSame(30, (int) config('session.lifetime'));
    }

    public function test_A5_logout_destroys_the_session_and_protects_pages_again(): void
    {
        $this->post('/login', ['username' => 'employee', 'password' => '12345678']);
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/users/dashboard')->assertRedirect('/login');
    }

    public function test_A6_every_block1_page_is_protected_without_login(): void
    {
        foreach (['/users/dashboard', '/users', '/users/create', '/users/1', '/users/1/edit', '/roles', '/roles/1/edit'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_A7_user_of_another_block_cannot_open_block1_pages(): void
    {
        $instructor = $this->user('rana_saleh');      // instructor role: no Block 1 permissions

        $this->actingAs($instructor)->get('/users')->assertForbidden();
        $this->actingAs($instructor)->get('/users/dashboard')->assertForbidden();
    }

    public function test_A8_deactivated_user_with_an_open_session_is_logged_out(): void
    {
        $employee = $this->user('employee');
        $this->actingAs($employee);
        $employee->update(['status' => 'inactive']);

        $this->get('/users')->assertRedirect('/login');
        $this->assertGuest();
    }

    /* =====================================================================
     | B. Dashboard by role (PDF: الصفحة الرئيسية ولوحة التحكم)
     * ===================================================================== */

    public function test_B1_manager_dashboard_shows_all_buttons(): void
    {
        $this->actingAs($this->user('manager'))->get('/users/dashboard')->assertOk()
            ->assertSee('Add User')->assertSee('Search &amp; View Users', false)
            ->assertSee('Change Account Status')->assertSee('Roles &amp; Permissions', false);
    }

    public function test_B2_employee_dashboard_has_no_status_delete_or_roles_buttons(): void
    {
        $this->actingAs($this->user('employee'))->get('/users/dashboard')->assertOk()
            ->assertSee('Add User')->assertSee('Search &amp; View Users', false)
            ->assertDontSee('Change Account Status')->assertDontSee('Roles &amp; Permissions', false);

        $grid = $this->actingAs($this->user('employee'))->get('/users')->getContent();
        $this->assertStringNotContainsString('title="Delete"', $grid);
        $this->assertStringNotContainsString('js-toggle-status"', $grid);
    }

    /* =====================================================================
     | C. Block 1 functions (PDF: جدول الكتلة الأولى)
     * ===================================================================== */

    public function test_C1_employee_adds_a_user_with_all_spec_fields_and_photo(): void
    {
        $this->actingAs($this->user('employee'))
            ->post('/users', $this->newUserData(['photo' => $this->tinyPng()]))
            ->assertSessionHasNoErrors()->assertRedirect();

        $maya = $this->user('maya_k');
        $this->assertSame('Maya Khalil', $maya->full_name);
        $this->assertSame('maya@attendance.edu', $maya->email);
        $this->assertSame('0944556677', $maya->phone);
        $this->assertSame($this->roleId('instructor'), (int) $maya->role_id);
        $this->assertSame('active', $maya->status);
        $this->assertNotNull($maya->photo);
        $this->assertFileExists(public_path($maya->photo));
        @unlink(public_path($maya->photo));
    }

    public function test_C2_advanced_search_filters_by_role_status_and_department(): void
    {
        $employee = $this->user('employee');
        $ajax = ['X-Requested-With' => 'XMLHttpRequest'];

        $byRole = $this->actingAs($employee)->get('/users?role_id=' . $this->roleId('instructor'), $ajax)->getContent();
        $this->assertStringContainsString('Dr. Rana Saleh', $byRole);
        $this->assertStringContainsString('Dr. Khaled Issa', $byRole);
        $this->assertStringNotContainsString('Lina Haddad', $byRole);

        $byStatus = $this->actingAs($employee)->get('/users?status=inactive', $ajax)->getContent();
        $this->assertStringContainsString('Omar Hasan', $byStatus);
        $this->assertStringNotContainsString('Sara Ali', $byStatus);

        $byDepartment = $this->actingAs($employee)->get('/users?department_id=' . $this->departmentId('Information Systems'), $ajax)->getContent();
        $this->assertStringContainsString('Sara Ali', $byDepartment);
        $this->assertStringNotContainsString('Omar Hasan', $byDepartment);

        $combined = $this->actingAs($employee)->get('/users?status=active&role_id=' . $this->roleId('student'), $ajax)->getContent();
        $this->assertStringContainsString('Lina Haddad', $combined);
        $this->assertStringNotContainsString('Yousef Nour', $combined);   // student but inactive
    }

    public function test_C3_search_results_are_a_grid_fragment_for_ajax(): void
    {
        $html = $this->actingAs($this->user('employee'))
            ->get('/users?search=sara', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->getContent();

        $this->assertStringContainsString('<table', $html);
        $this->assertStringNotContainsString('<html', $html);
        $this->assertStringContainsString('Sara Ali', $html);
    }

    public function test_C4_employee_edits_name_email_phone_photo_and_role(): void
    {
        $sara = $this->user('sara_ali');
        $oldHash = $sara->password;

        $this->actingAs($this->user('employee'))->put("/users/{$sara->user_id}", $this->newUserData([
            'full_name' => 'Sara Ali Hasan', 'username' => 'sara_ali', 'email' => 'sara.new@attendance.edu',
            'phone' => '0999888777', 'role_id' => $this->roleId('student'), 'photo' => $this->tinyPng(),
            'password' => '', 'password_confirmation' => '',
        ]))->assertSessionHasNoErrors();

        $sara->refresh();
        $this->assertSame('Sara Ali Hasan', $sara->full_name);
        $this->assertSame('sara.new@attendance.edu', $sara->email);
        $this->assertSame('0999888777', $sara->phone);
        $this->assertSame($this->roleId('student'), (int) $sara->role_id);
        $this->assertSame($oldHash, $sara->password);          // empty password keeps the old one
        $this->assertFileExists(public_path($sara->photo));
        @unlink(public_path($sara->photo));
    }

    public function test_C5_details_page_shows_full_data_and_activity(): void
    {
        $khaled = $this->user('khaled_issa');

        $this->actingAs($this->user('employee'))->get("/users/{$khaled->user_id}")->assertOk()
            ->assertSee('Dr. Khaled Issa')->assertSee('khaled_issa@attendance.edu')->assertSee($khaled->phone)
            ->assertSee('Artificial Intelligence')->assertSee('1975-12-02')
            ->assertSee('Activity in the system')->assertSee('Account created');
    }

    public function test_C6_manager_changes_status_both_ways(): void
    {
        $omar = $this->user('omar_hasan');                       // seeded as inactive
        $manager = $this->user('manager');

        $this->actingAs($manager)->patch("/users/{$omar->user_id}/toggle-status");
        $this->assertSame('active', $omar->fresh()->status);

        $this->actingAs($manager)->patch("/users/{$omar->user_id}/toggle-status");
        $this->assertSame('inactive', $omar->fresh()->status);
    }

    public function test_C7_manager_deletes_only_unlinked_users(): void
    {
        // Linked deletion is covered in UserManagementTest (lectures table). Here: a free user is deleted.
        $yousef = $this->user('yousef_nour');

        $this->actingAs($this->user('manager'))->delete("/users/{$yousef->user_id}")->assertRedirect();
        $this->assertDatabaseMissing('users', ['user_id' => $yousef->user_id]);
    }

    public function test_C8_manager_manages_the_five_system_roles(): void
    {
        $page = $this->actingAs($this->user('manager'))->get('/roles')->assertOk();

        foreach (['admin', 'manager', 'employee', 'instructor', 'student'] as $role) {
            $page->assertSee($role);
        }

        // Grant a permission to the instructor role and check it is stored in the permissions table.
        $instructor = Role::where('role_name', 'instructor')->first();
        $this->actingAs($this->user('manager'))->put("/roles/{$instructor->role_id}", [
            'role_name' => 'instructor', 'description' => 'Instructor', 'permissions' => ['users.view'],
        ])->assertRedirect('/roles');

        $this->assertDatabaseHas('permissions', ['role_id' => $instructor->role_id, 'permission_name' => 'instructor:users.view']);
        $this->actingAs($this->user('rana_saleh'))->get('/users')->assertOk();
    }

    /* =====================================================================
     | D. Block 1 validations (PDF: قيود التحقق - الكتلة الأولى)
     * ===================================================================== */

    public function test_D1_all_required_fields(): void
    {
        $this->actingAs($this->user('employee'))->post('/users', [])
            ->assertSessionHasErrors(['full_name', 'username', 'email', 'password', 'phone', 'role_id', 'department_id', 'birth_date'])
            ->assertSessionDoesntHaveErrors('photo');                  // photo is optional
    }

    public function test_D2_name_is_text_between_3_and_100_characters(): void
    {
        $employee = $this->user('employee');
        $this->actingAs($employee)->post('/users', $this->newUserData(['full_name' => 'Ab']))->assertSessionHasErrors('full_name');
        $this->actingAs($employee)->post('/users', $this->newUserData(['full_name' => str_repeat('a', 101)]))->assertSessionHasErrors('full_name');
        $this->actingAs($employee)->post('/users', $this->newUserData(['full_name' => 'Maya 123']))->assertSessionHasErrors('full_name');
        $this->actingAs($employee)->post('/users', $this->newUserData(['full_name' => 'مايا خليل']))->assertSessionDoesntHaveErrors('full_name');
    }

    public function test_D3_email_format_and_unique(): void
    {
        $employee = $this->user('employee');
        $this->actingAs($employee)->post('/users', $this->newUserData(['email' => 'maya-at-mail']))->assertSessionHasErrors('email');
        $this->actingAs($employee)->post('/users', $this->newUserData(['email' => 'manager@attendance.edu']))->assertSessionHasErrors('email');
    }

    public function test_D4_password_strength_and_confirmation(): void
    {
        $employee = $this->user('employee');
        foreach (['abc12', 'abcdefgh', '12345678'] as $weak) {
            $this->actingAs($employee)->post('/users', $this->newUserData(['password' => $weak, 'password_confirmation' => $weak]))
                ->assertSessionHasErrors('password');
        }
        $this->actingAs($employee)->post('/users', $this->newUserData(['password_confirmation' => 'different1']))
            ->assertSessionHasErrors('password');
    }

    public function test_D5_phone_is_exactly_10_digits(): void
    {
        $employee = $this->user('employee');
        foreach (['093456789', '09345678901', '09345abc78'] as $bad) {
            $this->actingAs($employee)->post('/users', $this->newUserData(['phone' => $bad]))->assertSessionHasErrors('phone');
        }
    }

    public function test_D6_role_and_department_must_come_from_the_lists(): void
    {
        $this->actingAs($this->user('employee'))->post('/users', $this->newUserData(['role_id' => 9999, 'department_id' => 9999]))
            ->assertSessionHasErrors(['role_id', 'department_id']);
    }

    public function test_D7_photo_must_be_png_or_jpg_and_small(): void
    {
        $employee = $this->user('employee');
        $this->actingAs($employee)->post('/users', $this->newUserData(['photo' => UploadedFile::fake()->create('a.pdf', 50, 'application/pdf')]))
            ->assertSessionHasErrors('photo');
        $this->actingAs($employee)->post('/users', $this->newUserData(['photo' => UploadedFile::fake()->create('a.gif', 50, 'image/gif')]))
            ->assertSessionHasErrors('photo');
        $this->actingAs($employee)->post('/users', $this->newUserData(['photo' => UploadedFile::fake()->create('big.png', 2049, 'image/png')]))
            ->assertSessionHasErrors('photo');
        // A .png name with non-image content is rejected too (content check, not only the extension).
        // (a real temp file: Laravel's fake files report their type from the name only)
        $path = tempnam(sys_get_temp_dir(), 'php');
        file_put_contents($path, '<?php echo "hacked"; ?>');
        $this->actingAs($employee)->post('/users', $this->newUserData(['photo' => new UploadedFile($path, 'fake.png', 'image/png', null, true)]))
            ->assertSessionHasErrors('photo');
    }

    public function test_D8_user_must_be_at_least_18(): void
    {
        $employee = $this->user('employee');
        $this->actingAs($employee)->post('/users', $this->newUserData(['birth_date' => now()->subYears(17)->toDateString()]))
            ->assertSessionHasErrors('birth_date');
        $this->actingAs($employee)->post('/users', $this->newUserData(['birth_date' => 'not-a-date']))
            ->assertSessionHasErrors('birth_date');
        $this->actingAs($employee)->post('/users', $this->newUserData(['birth_date' => now()->subYears(18)->toDateString()]))
            ->assertSessionDoesntHaveErrors('birth_date');
    }

    public function test_D9_username_is_unique(): void
    {
        $this->actingAs($this->user('employee'))->post('/users', $this->newUserData(['username' => 'manager']))
            ->assertSessionHasErrors('username');
    }

    public function test_D10_status_only_takes_active_or_inactive(): void
    {
        // Status is not a form field: new accounts are active, and only the manager toggles it.
        $this->actingAs($this->user('employee'))->post('/users', $this->newUserData(['status' => 'inactive']));
        $this->assertSame('active', $this->user('maya_k')->status);

        $statuses = User::pluck('status')->unique()->values()->all();
        $this->assertEmpty(array_diff($statuses, ['active', 'inactive']));
    }

    public function test_D11_client_side_validation_attributes_exist_in_the_form(): void
    {
        $html = $this->actingAs($this->user('employee'))->get('/users/create')->getContent();

        foreach (['minlength="3"', 'maxlength="100"', 'type="email"', 'pattern="[0-9]{10}"', 'minlength="8"',
                  'accept=".jpg,.jpeg,.png', 'type="date"', 'max="' . now()->subYears(18)->toDateString() . '"', 'required'] as $attribute) {
            $this->assertStringContainsString($attribute, $html, "Missing client-side rule: {$attribute}");
        }
    }

    public function test_D12_manager_status_field_is_required_and_from_the_list(): void
    {
        $manager = $this->user('manager');
        $this->actingAs($manager)->post('/users', $this->newUserData(['status' => '']))->assertSessionHasErrors('status');
        $this->actingAs($manager)->post('/users', $this->newUserData(['status' => 'deleted']))->assertSessionHasErrors('status');
        $this->actingAs($manager)->post('/users', $this->newUserData(['status' => 'inactive']))->assertSessionHasNoErrors();
        $this->assertSame('inactive', $this->user('maya_k')->status);

        $this->assertStringContainsString('name="status"', $this->actingAs($manager)->get('/users/create')->getContent());
        $this->assertStringNotContainsString('name="status"', $this->actingAs($this->user('employee'))->get('/users/create')->getContent());
    }

    public function test_D13_instructor_age_is_between_18_and_60(): void
    {
        $employee = $this->user('employee');
        $this->actingAs($employee)->post('/users', $this->newUserData(['birth_date' => now()->subYears(65)->toDateString()]))
            ->assertSessionHasErrors('birth_date');
        $this->actingAs($employee)->post('/users', $this->newUserData(['birth_date' => now()->subYears(60)->toDateString()]))
            ->assertSessionHasNoErrors();
        // other roles have no upper limit
        $this->actingAs($employee)->post('/users', $this->newUserData([
            'username' => 'old_student', 'email' => 'old@attendance.edu',
            'role_id' => $this->roleId('student'), 'birth_date' => now()->subYears(65)->toDateString(),
        ]))->assertSessionHasNoErrors();
    }

    /* =====================================================================
     | F. Database tables of Block 1 (PDF: جداول الكتلة الأولى)
     * ===================================================================== */

    public function test_F1_the_five_spec_actors_exist_as_roles(): void
    {
        foreach (['admin', 'student_affairs', 'instructor', 'exam_affairs', 'student', 'manager', 'employee'] as $role) {
            $this->assertDatabaseHas('roles', ['role_name' => $role]);
        }
    }

    public function test_F2_permission_name_is_unique_and_linked_to_a_role(): void
    {
        $this->assertDatabaseHas('permissions', ['permission_name' => 'employee:users.view', 'role_id' => $this->roleId('employee')]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('permissions')->insert(['permission_name' => 'employee:users.view', 'role_id' => $this->roleId('manager')]);
    }

    public function test_F3_users_table_has_the_spec_columns_and_unique_keys(): void
    {
        foreach (['user_id', 'full_name', 'username', 'email', 'password', 'phone', 'role_id', 'department_id', 'photo', 'birth_date', 'status', 'created_at', 'updated_at'] as $column) {
            $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('users', $column), "users.{$column} is missing");
        }

        // UNIQUE username and email
        $this->assertInsertFails($this->rawUser(['username' => 'manager']), 'duplicate username');
        $this->assertInsertFails($this->rawUser(['email' => 'manager@attendance.edu']), 'duplicate email');
    }

    /** A complete, valid users row for direct database inserts. */
    private function rawUser(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Raw User', 'username' => 'raw_user', 'email' => 'raw@attendance.edu', 'password' => Hash::make('x1234567'),
            'phone' => '0911111111', 'role_id' => $this->roleId('employee'),
            'department_id' => $this->departmentId('Software Engineering'), 'birth_date' => '1990-01-01',
            'status' => 'active', 'created_at' => now(),
        ], $overrides);
    }

    private function assertInsertFails(array $row, string $why): void
    {
        try {
            DB::table('users')->insert($row);
        } catch (\Illuminate\Database\QueryException) {
            $this->assertTrue(true);
            return;
        }
        $this->fail("The database accepted a row with {$why}.");
    }

    public function test_F4_phone_department_and_birth_date_are_not_null_in_the_database(): void
    {
        DB::table('users')->insert($this->rawUser());               // the complete row is accepted
        $this->assertDatabaseHas('users', ['username' => 'raw_user']);

        foreach (['phone', 'department_id', 'birth_date'] as $column) {
            $this->assertInsertFails($this->rawUser(['username' => "no_{$column}", 'email' => "no_{$column}@x.edu", $column => null]), "{$column} = NULL");
        }
    }

    public function test_F5_created_at_defaults_to_current_timestamp(): void
    {
        $row = $this->rawUser();
        unset($row['created_at']);
        DB::table('users')->insert($row);

        $this->assertNotNull(DB::table('users')->where('username', 'raw_user')->value('created_at'));
    }

    public function test_F6_database_check_rejects_users_younger_than_18(): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('CHECK constraint is created on MySQL / MariaDB only.');
        }

        $this->assertInsertFails($this->rawUser(['birth_date' => now()->subYears(10)->toDateString()]), 'age 10');
        DB::table('users')->insert($this->rawUser(['birth_date' => now()->subYears(18)->toDateString()]));
        $this->assertDatabaseHas('users', ['username' => 'raw_user']);
    }

    public function test_F7_database_does_not_delete_users_when_their_role_or_department_is_deleted(): void
    {
        $this->assertInsertFailsOnDelete('roles', 'role_id', $this->roleId('instructor'));
        $this->assertInsertFailsOnDelete('departments', 'department_id', $this->departmentId('Information Systems'));
        $this->assertDatabaseHas('users', ['username' => 'rana_saleh']);
    }

    private function assertInsertFailsOnDelete(string $table, string $key, int $id): void
    {
        try {
            DB::table($table)->where($key, $id)->delete();
        } catch (\Illuminate\Database\QueryException) {
            $this->assertTrue(true);
            return;
        }
        $this->fail("Deleting {$table} #{$id} that still has users was allowed.");
    }

    /* =====================================================================
     | E. Data protection (PDF: حماية البيانات + قيود الأمان)
     * ===================================================================== */

    public function test_E1_passwords_are_hashed_with_bcrypt(): void
    {
        $this->actingAs($this->user('employee'))->post('/users', $this->newUserData());
        $stored = DB::table('users')->where('username', 'maya_k')->value('password');

        $this->assertNotSame('pass1234', $stored);
        $this->assertStringStartsWith('$2y$', $stored);
        $this->assertTrue(Hash::check('pass1234', $stored));
    }

    public function test_E2_html_in_user_data_is_escaped_xss(): void
    {
        DB::table('users')->where('username', 'sara_ali')->update(['full_name' => '<script>alert(1)</script>']);

        $html = $this->actingAs($this->user('manager'))->get('/users?search=script')->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_E3_search_is_safe_from_sql_injection(): void
    {
        $html = $this->actingAs($this->user('employee'))
            ->get('/users?search=' . urlencode("' OR '1'='1"), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->getContent();

        $this->assertStringContainsString('No users match', $html);
        $this->assertSame(12, User::count());
    }

    public function test_E4_every_post_form_has_a_csrf_token(): void
    {
        $views = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));

        foreach ($views as $file) {
            if (!str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            preg_match_all('/<form\b[^>]*>(.*?)<\/form>/is', file_get_contents($file->getPathname()), $forms, PREG_SET_ORDER);
            foreach ($forms as $form) {
                if (preg_match('/method="GET"/i', $form[0])) {
                    continue;
                }
                $this->assertStringContainsString('@csrf', $form[0], 'Form without @csrf in ' . $file->getFilename());
            }
        }
    }

    public function test_E5_csrf_protection_rejects_a_post_without_token(): void
    {
        // Laravel skips CSRF checks in unit tests; turn the check back on for this request.
        $this->app->instance('env', 'local');
        $this->app['config']->set('app.env', 'local');
        $middleware = new class($this->app, $this->app['encrypter']) extends \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken {
            protected function runningUnitTests() { return false; }
        };
        $this->app->instance(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, $middleware);

        $this->post('/login', ['username' => 'manager', 'password' => '12345678'])->assertStatus(419);
        $this->assertGuest();
    }

    public function test_E6_permissions_are_checked_in_the_controller_too(): void
    {
        // Call the controller action directly, bypassing the route middleware.
        $this->actingAs($this->user('employee'));
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        app(\App\Http\Controllers\UserController::class)->destroy(request(), $this->user('sara_ali'));
    }
}
