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
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'full_name'             => 'New Person',
            'username'              => 'new_person',
            'email'                 => 'new.person@attendance.edu',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'phone'                 => '0912345678',
            'role_id'               => Role::where('role_name', 'instructor')->value('role_id'),
            'department_id'         => DB::table('departments')->value('department_id'),
            'birth_date'            => '1990-05-05',
            'status'                => 'active',
        ], $overrides);
    }

    /* ---------------- login ---------------- */

    public function test_login_redirects_block1_users_to_dashboard_and_stores_session_data(): void
    {
        $this->post('/login', ['username' => 'manager', 'password' => '12345678'])
            ->assertRedirect()
            ->assertSessionHas('role', 'manager');
        $this->assertAuthenticated();
        // Block 1 alone: the fallback login lands on the Block 1 dashboard
        if (str_contains((string) app('router')->getRoutes()->getByName('login.post')?->getActionName(), 'Block1LoginController')) {
            $this->get('/login')->assertRedirect(route('users.dashboard'));
        }
    }

    public function test_login_gives_specific_errors(): void
    {
        $this->post('/login', ['username' => 'nobody', 'password' => 'x'])->assertSessionHasErrors(['username' => 'No account was found with this username.']);
        $this->post('/login', ['username' => 'manager', 'password' => 'wrong'])->assertSessionHasErrors(['password' => 'The password is incorrect.']);
        $this->post('/login', ['username' => 'omar_hasan', 'password' => '12345678'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_is_locked_after_five_failed_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['username' => 'manager', 'password' => 'wrong']);
        }
        $this->post('/login', ['username' => 'manager', 'password' => '12345678']);
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }

    /* ---------------- employee ---------------- */

    public function test_employee_can_search_add_and_edit_but_not_delete_or_change_status(): void
    {
        $employee = $this->user('employee');
        $target = $this->user('rana_saleh');

        $this->actingAs($employee)->get('/users?search=rana')->assertOk()->assertSee('Dr. Rana Saleh')->assertDontSee('title="Delete"', false);
        $this->actingAs($employee)->get('/users/create')->assertOk();
        $this->actingAs($employee)->post('/users', $this->validData())->assertRedirect();
        $this->assertDatabaseHas('users', ['username' => 'new_person', 'status' => 'active']);
        $this->assertTrue(Hash::check('secret123', $this->user('new_person')->password));

        $this->actingAs($employee)->patch("/users/{$target->user_id}/toggle-status")->assertForbidden();
        $this->actingAs($employee)->delete("/users/{$target->user_id}")->assertForbidden();
        $this->actingAs($employee)->get('/roles')->assertForbidden();
    }

    public function test_employee_cannot_assign_manager_role_or_edit_manager(): void
    {
        $employee = $this->user('employee');
        $managerRole = Role::where('role_name', 'manager')->value('role_id');

        $this->actingAs($employee)->post('/users', $this->validData(['role_id' => $managerRole]))->assertSessionHasErrors('role_id');
        $this->actingAs($employee)->get('/users/' . $this->user('manager')->user_id . '/edit')->assertForbidden();
    }

    public function test_ajax_search_returns_only_the_grid(): void
    {
        $response = $this->actingAs($this->user('employee'))
            ->get('/users?status=inactive', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();

        $response->assertSee('Omar Hasan')->assertDontSee('Staff Employee')->assertDontSee('<html', false);
    }

    /* ---------------- validation ---------------- */

    public function test_server_side_validation_rules(): void
    {
        $this->actingAs($this->user('manager'))->post('/users', $this->validData([
            'full_name' => 'Ab', 'email' => 'not-an-email', 'password' => 'onlyletters', 'password_confirmation' => 'x',
            'phone' => '12345', 'birth_date' => now()->subYears(10)->toDateString(), 'username' => 'manager',
        ]))->assertSessionHasErrors(['full_name', 'email', 'password', 'phone', 'birth_date', 'username']);
    }

    public function test_photo_must_be_a_small_png_or_jpg(): void
    {
        $manager = $this->user('manager');

        $this->actingAs($manager)->post('/users', $this->validData(['photo' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')]))
            ->assertSessionHasErrors('photo');
        $this->actingAs($manager)->post('/users', $this->validData(['photo' => UploadedFile::fake()->create('big.jpg', 3000, 'image/jpeg')]))
            ->assertSessionHasErrors('photo');

        $this->actingAs($manager)->post('/users', $this->validData(['photo' => $this->tinyPng()]))
            ->assertSessionHasNoErrors();
        $photo = $this->user('new_person')->photo;
        $this->assertFileExists(public_path($photo));
        @unlink(public_path($photo));
    }

    /** A real 1x1 PNG (the GD extension is not needed). */
    private function tinyPng(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

        return new UploadedFile($path, 'me.png', 'image/png', null, true);
    }

    /* ---------------- manager ---------------- */

    public function test_manager_toggles_status_and_deactivated_user_cannot_log_in(): void
    {
        $manager = $this->user('manager');
        $target = $this->user('sara_ali');

        $this->actingAs($manager)->patchJson("/users/{$target->user_id}/toggle-status")->assertOk()->assertJson(['status' => 'inactive']);
        auth()->logout();
        $this->post('/login', ['username' => 'sara_ali', 'password' => '12345678'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_manager_cannot_deactivate_or_delete_himself(): void
    {
        $manager = $this->user('manager');
        $this->actingAs($manager)->patchJson("/users/{$manager->user_id}/toggle-status")->assertStatus(422);
        $this->actingAs($manager)->deleteJson("/users/{$manager->user_id}")->assertStatus(422);
        $this->assertDatabaseHas('users', ['user_id' => $manager->user_id, 'status' => 'active']);
    }

    public function test_manager_deletes_unlinked_user_but_not_linked_one(): void
    {
        $manager = $this->user('manager');
        $free = $this->user('lina_haddad');
        $this->actingAs($manager)->deleteJson("/users/{$free->user_id}")->assertOk();
        $this->assertDatabaseMissing('users', ['user_id' => $free->user_id]);

        // Simulate Block 4's lectures table to check the "linked to lectures" rule.
        Schema::create('lectures', function ($table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('instructor_id');
        });
        $instructor = $this->user('rana_saleh');
        DB::table('lectures')->insert(['title' => 'Intro', 'instructor_id' => $instructor->user_id]);

        $this->actingAs($manager)->deleteJson("/users/{$instructor->user_id}")->assertStatus(422);
        $this->assertDatabaseHas('users', ['user_id' => $instructor->user_id]);
        $this->actingAs($manager)->get("/users/{$instructor->user_id}")->assertOk()->assertSee('Intro');
    }

    /* ---------------- roles & permissions ---------------- */

    public function test_manager_manages_roles_and_permissions(): void
    {
        $manager = $this->user('manager');

        $this->actingAs($manager)->post('/roles', ['role_name' => 'library_staff', 'description' => 'Exams office', 'permissions' => ['users.view']])
            ->assertRedirect('/roles');
        $role = Role::where('role_name', 'library_staff')->firstOrFail();
        $this->assertSame(['users.view'], \App\Support\Block1\Access::permissionsOfRole($role->role_id));

        // Removing users.edit from employee takes effect immediately.
        $employeeRole = Role::where('role_name', 'employee')->first();
        $this->actingAs($manager)->put("/roles/{$employeeRole->role_id}", ['role_name' => 'renamed', 'permissions' => ['users.view']]);
        $this->assertDatabaseHas('roles', ['role_id' => $employeeRole->role_id, 'role_name' => 'employee']);   // protected name kept
        $this->actingAs($this->user('employee')->fresh())->get('/users/create')->assertForbidden();

        $this->actingAs($manager)->delete("/roles/{$role->role_id}")->assertRedirect('/roles');
        $this->assertDatabaseMissing('roles', ['role_name' => 'library_staff']);
    }

    public function test_role_with_users_or_protected_role_cannot_be_deleted_and_manager_cannot_lock_himself_out(): void
    {
        $manager = $this->user('manager');
        $instructorRole = Role::where('role_name', 'instructor')->first();
        $managerRole = Role::where('role_name', 'manager')->first();

        $this->actingAs($manager)->delete("/roles/{$instructorRole->role_id}");
        $this->assertDatabaseHas('roles', ['role_id' => $instructorRole->role_id]);
        $this->assertDatabaseHas('users', ['username' => 'rana_saleh']);

        $this->actingAs($manager)->delete("/roles/{$managerRole->role_id}");
        $this->assertDatabaseHas('roles', ['role_id' => $managerRole->role_id]);

        $this->actingAs($manager)->put("/roles/{$managerRole->role_id}", ['role_name' => 'manager', 'permissions' => ['users.view']])
            ->assertSessionHas('error');
        \App\Support\Block1\Access::forgetCache();
        $this->assertContains('roles.manage', \App\Support\Block1\Access::permissionsOfRole($managerRole->role_id));
    }
}
