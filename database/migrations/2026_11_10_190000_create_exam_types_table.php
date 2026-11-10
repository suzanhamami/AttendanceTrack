<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Block 5 - Exam & Grades Management
 * Table 15: exam_types (Midterm, Final, Practical, Oral, ...).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_types', function (Blueprint $table) {
            // Explicit primary key name, same convention as the other modules (role_id, course_id...)
            $table->id('exam_type_id');
            $table->string('type_name', 50)->unique();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_types');
    }
};
