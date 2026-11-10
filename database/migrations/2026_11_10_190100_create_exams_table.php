<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Block 5 - Exam & Grades Management
 * Table 13: exams.
 *
 * Depends on the tables created by the other modules: sections and courses.
 * Foreign keys use RESTRICT on delete so that a course / section / exam type that
 * still has exams can never be removed by accident (project rule: no deleting
 * records that are linked to other records).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id('exam_id');

            $table->foreignId('exam_type_id')
                  ->constrained('exam_types', 'exam_type_id')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->date('exam_date')->index();          // index required by the spec (search speed)
            $table->time('exam_time');
            $table->unsignedInteger('duration');          // minutes
            $table->decimal('max_grade', 5, 2);
            $table->decimal('min_grade', 5, 2);

            $table->foreignId('section_id')
                  ->constrained('sections', 'section_id')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('course_id')
                  ->constrained('courses', 'course_id')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->string('room', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });

        // CHECK constraints from the spec (MySQL 8 / MariaDB 10.2+).
        // SQLite cannot add constraints after creation, there the rules are enforced
        // by the ExamRequest validation only.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE exams ADD CONSTRAINT chk_exams_duration CHECK (duration > 0)');
            DB::statement('ALTER TABLE exams ADD CONSTRAINT chk_exams_max_grade CHECK (max_grade BETWEEN 10 AND 100)');
            DB::statement('ALTER TABLE exams ADD CONSTRAINT chk_exams_min_grade CHECK (min_grade < max_grade)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
