<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 100);
            $table->string('university_id', 20)->unique();
            $table->string('national_id', 20)->unique();
            $table->date('birth_date');
            $table->string('phone', 15);
            $table->string('email', 100)->unique()->nullable();
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->enum('academic_year', ['1', '2', '3', '4']);
            $table->string('photo', 255)->nullable();
            $table->enum('status', ['active', 'suspended', 'graduated'])->default('active');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE students ADD CONSTRAINT check_student_age CHECK (DATEDIFF(CURRENT_DATE, birth_date) >= (17 * 365.25))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
