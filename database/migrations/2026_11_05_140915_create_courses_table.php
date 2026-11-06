<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            // Explicit Primary Key: course_id
            $table->id('course_id'); 
            $table->string('course_name', 100);
            $table->string('course_code', 20)->unique();
            $table->integer('credit_hours');

            // Foreign Key 1: department_id -> references departments(department_id)
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('department_id')->on('departments')->onDelete('cascade')->onUpdate('cascade');

            // Foreign Key 2: instructor_id -> references users(user_id)
            $table->unsignedBigInteger('instructor_id');
            $table->foreign('instructor_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            // Foreign Key 3: semester_id -> references semesters(semester_id)
            $table->unsignedBigInteger('semester_id');
            $table->foreign('semester_id')->references('semester_id')->on('semesters')->onDelete('cascade')->onUpdate('cascade');

            // Foreign Key 4: prerequisite_id -> references courses(course_id)
            $table->unsignedBigInteger('prerequisite_id')->nullable();
            $table->foreign('prerequisite_id')->references('course_id')->on('courses')->nullOnDelete();

            $table->string('academic_year', 20);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};