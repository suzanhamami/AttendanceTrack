<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Block 5 - Exam & Grades Management
 * Table 14: grades (one grade per student per exam).
 *
 * Both foreign keys use RESTRICT: an exam that has grades, or a student that has
 * grades, cannot be deleted (spec validation rules).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id('grade_id');

            $table->foreignId('exam_id')
                  ->constrained('exams', 'exam_id')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('student_id')
                  ->constrained('students', 'student_id')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->decimal('grade', 5, 2);
            $table->date('recorded_date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // A student can only have one grade per exam (prevents duplicate grades).
            $table->unique(['exam_id', 'student_id'], 'unique_exam_student_grade');

            // Indexes required by the spec (fast search / transcripts).
            $table->index('exam_id', 'grades_exam_id_index');
            $table->index('student_id', 'grades_student_id_index');
        });

        // grade >= 0 (the upper bound "grade <= exams.max_grade" spans two tables, so it is
        // enforced in GradeBatchRequest instead of a CHECK constraint).
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE grades ADD CONSTRAINT chk_grades_not_negative CHECK (grade >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
