<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Grade;
use App\Models\Role;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\ExamSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Block 5 - Exam & Grades Management feature tests.
 *
 * These tests need the tables of the other modules (roles, departments, semesters, users, courses,
 * sections, students, student_section) plus the `role` middleware alias and the `login` route, i.e.
 * the project with the other modules merged. Run with:  php artisan test --configuration=phpunit.mysql.xml --filter=ExamModuleTest
 */
class ExamModuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SAFETY: RefreshDatabase calls this hook BEFORE it runs migrate:fresh. The tests only run on the
     * dedicated MySQL test database (see phpunit.mysql.xml); any other connection or database name
     * (for example your real project database) aborts the run before a single table is touched.
     */
    protected function beforeRefreshingDatabase(): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection !== 'mysql' || $database !== 'attendancetrack_block5_test') {
            throw new \RuntimeException(
                "ExamModuleTest only runs on the MySQL database 'attendancetrack_block5_test' "
                . "(run: php artisan test --configuration=phpunit.mysql.xml). "
                . "Current connection: '{$connection}', database: '{$database}'. Nothing was migrated."
            );
        }
    }

    private User $manager;
    private User $employee;
    private Section $section;
    private Course $course;
    private ExamType $midterm;
    /** @var \Illuminate\Support\Collection<int, Student> */
    private $students;

    protected function setUp(): void
    {
        parent::setUp();

        $managerRole = Role::create(['role_name' => 'manager', 'description' => 'Department Manager']);
        $employeeRole = Role::create(['role_name' => 'employee', 'description' => 'Department Staff']);
        $department = Department::create(['department_name' => 'Software Engineering']);
        $semester = Semester::create(['semester_name' => 'Fall 2026', 'start_date' => '2026-09-15', 'end_date' => '2027-01-20']);

        $this->manager = $this->makeUser('manager', $managerRole, $department);
        $this->employee = $this->makeUser('employee', $employeeRole, $department);

        $this->course = Course::create([
            'course_name' => 'Web Programming 2', 'course_code' => 'CS200', 'credit_hours' => 3,
            'department_id' => $department->department_id, 'instructor_id' => $this->employee->user_id,
            'semester_id' => $semester->semester_id, 'academic_year' => '2026-2027', 'status' => 'active',
        ]);

        $this->section = Section::create([
            'section_name' => 'Sec-1', 'course_id' => $this->course->course_id,
            'instructor_id' => $this->employee->user_id, 'semester_id' => $semester->semester_id, 'capacity' => 30,
        ]);

        $this->students = collect([['Ahmad Ali', '20260001', '01010101011'], ['Sara Omar', '20260002', '02020202022']])
            ->map(function ($row) use ($department) {
                $student = Student::create([
                    'full_name' => $row[0], 'university_id' => $row[1], 'national_id' => $row[2],
                    'birth_date' => '2004-05-12', 'phone' => '0911111111', 'email' => $row[1] . '@test.edu',
                    'department_id' => $department->department_id, 'academic_year' => '1', 'status' => 'active',
                ]);

                DB::table('student_section')->insert([
                    'student_id' => $student->student_id, 'section_id' => $this->section->section_id,
                    'registration_date' => '2026-09-20',
                ]);

                return $student;
            });

        $this->midterm = ExamType::create(['type_name' => 'Midterm']);
    }

    /* ------------------------------------------------------------------ helpers */

    private function makeUser(string $username, Role $role, Department $department): User
    {
        return User::create([
            'full_name' => ucfirst($username), 'username' => $username, 'email' => "$username@test.edu",
            'password' => Hash::make('12345678'), 'role_id' => $role->role_id,
            'department_id' => $department->department_id, 'status' => 'active',
        ]);
    }

    private function makeExam(array $overrides = []): Exam
    {
        return Exam::create($overrides + [
            'exam_type_id' => $this->midterm->exam_type_id, 'exam_date' => '2026-10-20', 'exam_time' => '09:00',
            'duration' => 60, 'max_grade' => 40, 'min_grade' => 20,
            'section_id' => $this->section->section_id, 'course_id' => $this->course->course_id, 'room' => 'Hall 1',
        ]);
    }

    private function validExamData(array $overrides = []): array
    {
        return $overrides + [
            'exam_type_id' => $this->midterm->exam_type_id, 'exam_date' => '2026-12-01', 'exam_time' => '10:30',
            'duration' => 90, 'max_grade' => 100, 'min_grade' => 50,
            'section_id' => $this->section->section_id, 'course_id' => $this->course->course_id, 'room' => 'Hall 2',
        ];
    }

    private function gradeFor(Exam $exam, Student $student, float $value): Grade
    {
        return Grade::create(['exam_id' => $exam->exam_id, 'student_id' => $student->student_id,
                              'grade' => $value, 'recorded_date' => '2026-10-21']);
    }

    /* ----------------------------------------------------- access / permissions */

    public function test_guests_are_redirected_to_login(): void
    {
        foreach (['/exams', '/exams/create', '/results', '/exam-types'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_access_is_decided_by_the_role_name_not_by_the_role_id(): void
    {
        // The manager's role keeps its id but is renamed: it must no longer be treated as a manager.
        $this->manager->role->update(['role_name' => 'auditor']);

        $this->actingAs($this->manager->fresh())->get(route('exams.index'))->assertForbidden();
        $this->actingAs($this->manager->fresh())->get(route('results.index'))->assertForbidden();
    }

    public function test_employee_can_search_add_and_edit_but_not_delete_or_issue_results(): void
    {
        $exam = $this->makeExam();

        $this->actingAs($this->employee);
        $this->get('/exams')->assertOk();
        $this->get('/exams/create')->assertOk();
        $this->get("/exams/{$exam->exam_id}")->assertOk();
        $this->get("/exams/{$exam->exam_id}/edit")->assertOk();
        $this->get("/exams/{$exam->exam_id}/grades")->assertOk();

        $this->delete("/exams/{$exam->exam_id}")->assertForbidden();
        $this->get('/results')->assertForbidden();
        $this->get("/results/{$this->students[0]->student_id}")->assertForbidden();
        $this->get('/exam-types')->assertForbidden();
        $this->post('/exam-types', ['type_name' => 'Quiz'])->assertForbidden();

        $this->assertDatabaseHas('exams', ['exam_id' => $exam->exam_id]);
    }

    public function test_delete_button_is_only_rendered_for_the_manager(): void
    {
        $exam = $this->makeExam();

        $this->actingAs($this->employee)->get('/exams')->assertDontSee('title="Delete exam"', false);
        $this->actingAs($this->manager)->get('/exams')->assertSee('title="Delete exam"', false);
    }

    public function test_login_lands_on_the_dashboard_with_buttons_for_the_role(): void
    {
        $this->makeExam(['exam_date' => now()->addDays(3)->format('Y-m-d')]);

        $this->post('/login', ['username' => $this->employee->username, 'password' => '12345678'])
            ->assertRedirect(route('exams.dashboard'));

        $this->get(route('exams.dashboard'))->assertOk()
            ->assertSee('Add Exam')->assertSee('Record Grades')
            ->assertDontSee('Manage Exam Types')->assertDontSee('Issue Results');

        $this->actingAs($this->manager)->get(route('exams.dashboard'))->assertOk()
            ->assertSee('Manage Exam Types')->assertSee('Issue Results')
            ->assertSee('Upcoming Exams')->assertDontSee('No upcoming exams.');
    }

    /* ----------------------------------------------------------- create / update */

    public function test_exam_can_be_created_with_valid_data(): void
    {
        $this->actingAs($this->employee)
            ->post('/exams', $this->validExamData())
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('exams', ['room' => 'Hall 2', 'max_grade' => 100, 'min_grade' => 50]);
    }

    public function test_exam_creation_validation_rules(): void
    {
        $this->actingAs($this->employee);

        $cases = [
            'missing everything'        => [[], ['exam_type_id', 'exam_date', 'exam_time', 'duration', 'max_grade', 'min_grade', 'section_id', 'course_id', 'room']],
            'max grade below 10'        => [['max_grade' => 5, 'min_grade' => 2], ['max_grade']],
            'max grade above 100'       => [['max_grade' => 101], ['max_grade']],
            'min grade equals max'      => [['min_grade' => 100], ['min_grade']],
            'min grade above max'       => [['min_grade' => 120], ['min_grade']],
            'min grade zero'            => [['min_grade' => 0], ['min_grade']],
            'negative duration'         => [['duration' => -30], ['duration']],
            'zero duration'             => [['duration' => 0], ['duration']],
            'invalid date'              => [['exam_date' => '2026-13-45'], ['exam_date']],
            'invalid time'              => [['exam_time' => '25:99'], ['exam_time']],
            'non numeric grade'         => [['max_grade' => 'abc'], ['max_grade']],
            'unknown exam type'         => [['exam_type_id' => 999], ['exam_type_id']],
            'unknown section'           => [['section_id' => 999], ['section_id']],
            'unknown course'            => [['course_id' => 999], ['course_id']],
            'room too long'             => [['room' => str_repeat('x', 51)], ['room']],
        ];

        foreach ($cases as $label => [$override, $expectedErrors]) {
            $data = $override === [] ? [] : $this->validExamData($override);
            try {
                $this->post('/exams', $data)->assertSessionHasErrors($expectedErrors);
            } catch (\PHPUnit\Framework\AssertionFailedError $e) {
                $this->fail("Validation case '$label' failed: " . $e->getMessage());
            }
        }

        $this->assertDatabaseCount('exams', 0);
    }

    public function test_section_must_belong_to_the_selected_course(): void
    {
        $otherCourse = Course::create([
            'course_name' => 'Databases', 'course_code' => 'CS300', 'credit_hours' => 3,
            'department_id' => $this->course->department_id, 'instructor_id' => $this->employee->user_id,
            'semester_id' => $this->course->semester_id, 'academic_year' => '2026-2027', 'status' => 'active',
        ]);

        $this->actingAs($this->employee)
            ->post('/exams', $this->validExamData(['course_id' => $otherCourse->course_id]))
            ->assertSessionHasErrors('section_id');
    }

    public function test_room_is_sanitised_against_html(): void
    {
        $this->actingAs($this->employee)
            ->post('/exams', $this->validExamData(['room' => '<b>Hall</b><script>alert(1)</script> 3']));

        $this->assertDatabaseMissing('exams', ['room' => '<b>Hall</b><script>alert(1)</script> 3']);
        $this->assertStringNotContainsString('<', Exam::first()->room);
    }

    public function test_exam_can_be_updated(): void
    {
        $exam = $this->makeExam();

        $this->actingAs($this->employee)
            ->put("/exams/{$exam->exam_id}", $this->validExamData(['room' => 'Updated Hall', 'duration' => 45]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('exams', ['exam_id' => $exam->exam_id, 'room' => 'Updated Hall', 'duration' => 45]);
    }

    public function test_exam_with_grades_keeps_its_section_and_a_consistent_max_grade(): void
    {
        $exam = $this->makeExam(['max_grade' => 40, 'min_grade' => 20]);
        $this->gradeFor($exam, $this->students[0], 35);

        $this->actingAs($this->employee);

        // lowering the maximum below an already recorded grade is rejected
        $this->put("/exams/{$exam->exam_id}", $this->validExamData(['max_grade' => 30, 'min_grade' => 10]))
            ->assertSessionHasErrors('max_grade');

        // changing the section / course of an exam that has grades is rejected
        $otherSection = Section::create([
            'section_name' => 'Sec-2', 'course_id' => $this->course->course_id,
            'instructor_id' => $this->employee->user_id, 'semester_id' => $this->course->semester_id, 'capacity' => 20,
        ]);
        $this->put("/exams/{$exam->exam_id}", $this->validExamData(['section_id' => $otherSection->section_id, 'max_grade' => 40, 'min_grade' => 20]))
            ->assertSessionHasErrors('section_id');

        // raising the maximum is fine
        $this->put("/exams/{$exam->exam_id}", $this->validExamData(['max_grade' => 50, 'min_grade' => 25]))
            ->assertSessionHasNoErrors();
    }

    /* --------------------------------------------------------------- delete exam */

    public function test_manager_can_delete_an_exam_without_grades(): void
    {
        $exam = $this->makeExam();

        $this->actingAs($this->manager)->delete("/exams/{$exam->exam_id}")->assertRedirect(route('exams.index'));

        $this->assertDatabaseMissing('exams', ['exam_id' => $exam->exam_id]);
    }

    public function test_exam_with_grades_cannot_be_deleted(): void
    {
        $exam = $this->makeExam();
        $this->gradeFor($exam, $this->students[0], 30);

        $this->actingAs($this->manager)->delete("/exams/{$exam->exam_id}")->assertSessionHas('error');

        $this->assertDatabaseHas('exams', ['exam_id' => $exam->exam_id]);
        $this->assertDatabaseCount('grades', 1);
    }

    /* ------------------------------------------------------------ record grades */

    public function test_grades_can_be_recorded_and_then_updated_without_duplicates(): void
    {
        $exam = $this->makeExam();
        [$a, $b] = [$this->students[0], $this->students[1]];
        $this->actingAs($this->employee);

        $this->put("/exams/{$exam->exam_id}/grades", [
            'recorded_date' => now()->format('Y-m-d'),
            'grades' => [$a->student_id => '35.5', $b->student_id => ''],   // the second one is left empty
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('grades', 1);
        $this->assertDatabaseHas('grades', ['exam_id' => $exam->exam_id, 'student_id' => $a->student_id, 'grade' => 35.5]);

        // edit the grade of the first student and add the second one
        $this->put("/exams/{$exam->exam_id}/grades", [
            'recorded_date' => now()->format('Y-m-d'),
            'grades' => [$a->student_id => '38', $b->student_id => '12'],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('grades', 2);
        $this->assertDatabaseHas('grades', ['student_id' => $a->student_id, 'grade' => 38]);
        $this->assertDatabaseHas('grades', ['student_id' => $b->student_id, 'grade' => 12]);
    }

    public function test_grade_validation_rules(): void
    {
        $exam = $this->makeExam(['max_grade' => 40]);
        $a = $this->students[0]->student_id;
        $today = now()->format('Y-m-d');
        $this->actingAs($this->employee);

        $this->put("/exams/{$exam->exam_id}/grades", ['recorded_date' => $today, 'grades' => [$a => '41']])
            ->assertSessionHasErrors("grades.$a");                                  // above max grade
        $this->put("/exams/{$exam->exam_id}/grades", ['recorded_date' => $today, 'grades' => [$a => '-1']])
            ->assertSessionHasErrors("grades.$a");                                  // negative
        $this->put("/exams/{$exam->exam_id}/grades", ['recorded_date' => $today, 'grades' => [$a => 'abc']])
            ->assertSessionHasErrors("grades.$a");                                  // not a number
        $this->put("/exams/{$exam->exam_id}/grades", ['recorded_date' => $today, 'grades' => [$a => '']])
            ->assertSessionHasErrors('grades');                                     // nothing entered
        $this->put("/exams/{$exam->exam_id}/grades", ['grades' => [$a => '20']])
            ->assertSessionHasErrors('recorded_date');                              // date missing
        $this->put("/exams/{$exam->exam_id}/grades", ['recorded_date' => now()->addDay()->format('Y-m-d'), 'grades' => [$a => '20']])
            ->assertSessionHasErrors('recorded_date');                              // future date
        $this->put("/exams/{$exam->exam_id}/grades", ['recorded_date' => 'not-a-date', 'grades' => [$a => '20']])
            ->assertSessionHasErrors('recorded_date');                              // invalid date

        $this->assertDatabaseCount('grades', 0);

        // boundaries 0 and max are accepted
        $this->put("/exams/{$exam->exam_id}/grades", ['recorded_date' => $today, 'grades' => [$a => '0', $this->students[1]->student_id => '40']])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('grades', 2);
    }

    public function test_only_students_registered_in_the_section_can_be_graded(): void
    {
        $exam = $this->makeExam();
        $outsider = Student::create([
            'full_name' => 'Outsider', 'university_id' => '20269999', 'national_id' => '09090909099',
            'birth_date' => '2004-01-01', 'phone' => '0900000000', 'department_id' => $this->course->department_id,
            'academic_year' => '1', 'status' => 'active',
        ]);

        $this->actingAs($this->employee)
            ->put("/exams/{$exam->exam_id}/grades", ['recorded_date' => now()->format('Y-m-d'), 'grades' => [$outsider->student_id => '10']])
            ->assertSessionHasErrors("grades.{$outsider->student_id}");

        $this->assertDatabaseCount('grades', 0);
    }

    public function test_database_rejects_a_duplicate_grade_for_the_same_exam_and_student(): void
    {
        $exam = $this->makeExam();
        $this->gradeFor($exam, $this->students[0], 10);

        $this->expectException(QueryException::class);
        $this->gradeFor($exam, $this->students[0], 20);
    }

    public function test_database_blocks_deleting_an_exam_that_has_grades(): void
    {
        $exam = $this->makeExam();
        $this->gradeFor($exam, $this->students[0], 10);

        $this->expectException(QueryException::class);
        DB::table('exams')->where('exam_id', $exam->exam_id)->delete();
    }

    /* ------------------------------------------------------- details + search */

    public function test_exam_details_page_shows_the_students_and_their_results(): void
    {
        $exam = $this->makeExam(['max_grade' => 40, 'min_grade' => 20]);
        $this->gradeFor($exam, $this->students[0], 35);   // passed
        $this->gradeFor($exam, $this->students[1], 10);   // failed

        $this->actingAs($this->employee)
            ->get("/exams/{$exam->exam_id}")
            ->assertOk()
            ->assertSee('Ahmad Ali')->assertSee('Sara Omar')
            ->assertSee('Passed')->assertSee('Failed')
            ->assertSee('87.5%');   // 35 / 40
    }

    public function test_search_filters_and_ajax_returns_only_the_table_fragment(): void
    {
        $quiz = ExamType::create(['type_name' => 'Quiz']);
        $this->makeExam(['room' => 'Alpha Hall']);
        $this->makeExam(['room' => 'Beta Lab', 'exam_type_id' => $quiz->exam_type_id, 'exam_date' => '2026-11-15']);

        $this->actingAs($this->employee);

        $this->get('/exams?search=Alpha')->assertSee('Alpha Hall')->assertDontSee('Beta Lab');
        $this->get('/exams?search=CS200')->assertSee('Alpha Hall')->assertSee('Beta Lab');          // by course code
        $this->get("/exams?exam_type_id={$quiz->exam_type_id}")->assertSee('Beta Lab')->assertDontSee('Alpha Hall');
        $this->get('/exams?exam_date=2026-10-20')->assertSee('Alpha Hall')->assertDontSee('Beta Lab');
        $this->get("/exams?section_id={$this->section->section_id}&course_id={$this->course->course_id}")->assertSee('Alpha Hall');
        $this->get('/exams?search=nothing-matches-this')->assertSee('No exams found');

        $ajax = $this->get('/exams?search=Alpha', ['X-Requested-With' => 'XMLHttpRequest']);
        $ajax->assertOk()->assertSee('Alpha Hall')->assertDontSee('<html', false)->assertDontSee('Add New Exam');
    }

    /* ------------------------------------------------------------------ results */

    public function test_manager_issues_a_statement_with_correct_calculations(): void
    {
        $midterm = $this->makeExam(['max_grade' => 40, 'min_grade' => 20, 'exam_date' => '2026-10-01']);
        $final = $this->makeExam(['exam_type_id' => ExamType::create(['type_name' => 'Final'])->exam_type_id,
                                  'max_grade' => 100, 'min_grade' => 50, 'exam_date' => '2026-12-01']);
        $student = $this->students[0];
        $this->gradeFor($midterm, $student, 30);   // passed
        $this->gradeFor($final, $student, 40);     // failed (< 50)

        $this->actingAs($this->manager)
            ->get("/results/{$student->student_id}")
            ->assertOk()
            ->assertSee('Final Grades Statement')
            ->assertSee('Ahmad Ali')
            ->assertSee('70 / 140')       // course total: 30 + 40 out of 40 + 100
            ->assertSee('50%')            // 70 / 140
            ->assertSee('Passed')->assertSee('Failed');

        $this->actingAs($this->manager)->get('/results?search=Ahmad')->assertSee('Ahmad Ali')->assertDontSee('Sara Omar');
        $this->actingAs($this->manager)->get('/results?search=Ahmad', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertSee('Issue statement')->assertDontSee('<html', false);
    }

    public function test_statement_of_a_student_without_grades_shows_a_message(): void
    {
        $this->actingAs($this->manager)
            ->get("/results/{$this->students[1]->student_id}")
            ->assertOk()->assertSee('no recorded grades');
    }

    /* --------------------------------------------------------------- exam types */

    public function test_manager_manages_exam_types(): void
    {
        $this->actingAs($this->manager);

        $this->post('/exam-types', ['type_name' => 'Quiz'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('exam_types', ['type_name' => 'Quiz']);

        $this->post('/exam-types', ['type_name' => 'Quiz'])->assertSessionHasErrors('type_name');   // duplicate
        $this->post('/exam-types', ['type_name' => ''])->assertSessionHasErrors('type_name');       // empty

        $quiz = ExamType::where('type_name', 'Quiz')->first();
        $this->put("/exam-types/{$quiz->exam_type_id}", ['type_name' => 'Pop Quiz'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('exam_types', ['type_name' => 'Pop Quiz']);

        // a type used by an exam cannot be deleted, an unused one can
        $this->makeExam();
        $this->delete("/exam-types/{$this->midterm->exam_type_id}")->assertSessionHas('error');
        $this->assertDatabaseHas('exam_types', ['type_name' => 'Midterm']);

        $this->delete("/exam-types/{$quiz->exam_type_id}")->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('exam_types', ['type_name' => 'Pop Quiz']);
    }

    /* ------------------------------------------------------------------- seeder */

    public function test_seeder_creates_types_exams_and_grades_and_is_repeatable(): void
    {
        $this->seed(ExamSeeder::class);
        $this->seed(ExamSeeder::class);   // twice: must not duplicate

        $this->assertDatabaseHas('exam_types', ['type_name' => 'Final']);
        $this->assertSame(3, Exam::where('section_id', $this->section->section_id)->count());
        $this->assertSame(4, Grade::count());   // 2 students x 2 past exams
    }
}
