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
        Schema::create('student_section', function (Blueprint $table) {
            $table->id('student_section_id');
            $table->foreignId('student_id')
                  ->constrained('students', 'student_id')
                  ->onDelete('cascade')
                  ->onUpdate('cascade'); 
            $table->foreignId('section_id')
                  ->constrained('sections', 'section_id')
                  ->onDelete('cascade')
                  ->onUpdate('cascade'); 
            
            $table->date('registration_date'); 
            
            // Unique constraint to prevent duplicate section registration for the same student
            $table->unique(['student_id', 'section_id'], 'unique_student_section');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_section');
    }
};
