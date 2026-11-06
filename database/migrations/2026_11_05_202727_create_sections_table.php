<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id('section_id');
            $table->string('section_name', 50);
            $table->foreignId('course_id')
                  ->constrained('courses', 'course_id')
                  ->onDelete('cascade')
                  ->onUpdate('cascade'); 
            $table->foreignId('instructor_id')
                    ->constrained('users', 'user_id')
                    ->onDelete('cascade')
                    ->onUpdate('cascade'); 
            $table->foreignId('semester_id')
                  ->constrained('semesters', 'semester_id')
                  ->onDelete('cascade')
                  ->onUpdate('cascade'); 
            $table->integer('capacity');
            $table->timestamp('created_at')->useCurrent();
        });
        DB::statement('ALTER TABLE sections ADD CONSTRAINT check_capacity_positive CHECK (capacity > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
