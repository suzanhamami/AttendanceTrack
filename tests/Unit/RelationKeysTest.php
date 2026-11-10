<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace Tests\Unit;

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
use Tests\TestCase;

/**
 * Checks the foreign / local / owner keys of the relations used by the project. Every table has a custom primary
 * key (`<table>_id`), so Laravel's default key guessing (e.g. `role_role_id`) would be wrong.
 *
 * No query is executed: relation objects are only built, so no database content is read or written.
 */
class RelationKeysTest extends TestCase
{
    public function test_has_many_relations_use_the_custom_primary_keys(): void
    {
        $this->assertSame(['role_id', 'role_id'], $this->keys((new Role)->users()));
        $this->assertSame(['department_id', 'department_id'], $this->keys((new Department)->courses()));
        $this->assertSame(['semester_id', 'semester_id'], $this->keys((new Semester)->courses()));
        $this->assertSame(['instructor_id', 'user_id'], $this->keys((new User)->courses()));

        $this->assertSame(['exam_type_id', 'exam_type_id'], $this->keys((new ExamType)->exams()));
        $this->assertSame(['exam_id', 'exam_id'], $this->keys((new Exam)->grades()));
    }

    public function test_belongs_to_relations_use_the_custom_primary_keys(): void
    {
        $this->assertSame(['department_id', 'department_id'], $this->ownerKeys((new User)->department()));
        $this->assertSame(['role_id', 'role_id'], $this->ownerKeys((new User)->role()));
        $this->assertSame(['department_id', 'department_id'], $this->ownerKeys((new Student)->department()));
        $this->assertSame(['course_id', 'course_id'], $this->ownerKeys((new Section)->course()));
        $this->assertSame(['instructor_id', 'user_id'], $this->ownerKeys((new Section)->instructor()));
        $this->assertSame(['department_id', 'department_id'], $this->ownerKeys((new Course)->department()));
        $this->assertSame(['instructor_id', 'user_id'], $this->ownerKeys((new Course)->instructor()));
        $this->assertSame(['semester_id', 'semester_id'], $this->ownerKeys((new Course)->semester()));

        $this->assertSame(['exam_type_id', 'exam_type_id'], $this->ownerKeys((new Exam)->type()));
        $this->assertSame(['section_id', 'section_id'], $this->ownerKeys((new Exam)->section()));
        $this->assertSame(['course_id', 'course_id'], $this->ownerKeys((new Exam)->course()));
        $this->assertSame(['exam_id', 'exam_id'], $this->ownerKeys((new Grade)->exam()));
        $this->assertSame(['student_id', 'student_id'], $this->ownerKeys((new Grade)->student()));
    }

    public function test_student_section_pivot_only_asks_for_the_columns_that_exist(): void
    {
        // student_section has created_at but no updated_at: a null column name must never be selected.
        $columns = (new Section)->students()->getPivotColumns();

        $this->assertSame(['registration_date', 'student_section_id', 'created_at'], $columns);
        $this->assertNotContains(null, $columns);

        $this->assertSame(['registration_date', 'student_section_id'], (new Student)->sections()->getPivotColumns());
    }

    /** @return array{0: string, 1: string} [foreign key, local key] of a HasMany relation. */
    private function keys($relation): array
    {
        return [$relation->getForeignKeyName(), $relation->getLocalKeyName()];
    }

    /** @return array{0: string, 1: string} [foreign key, owner key] of a BelongsTo relation. */
    private function ownerKeys($relation): array
    {
        return [$relation->getForeignKeyName(), $relation->getOwnerKeyName()];
    }
}
