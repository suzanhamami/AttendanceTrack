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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_name', 50);
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->onDelete('cascade')
                  ->onUpdate('cascade'); 
            $table->foreignId('instructor_id')
                    ->constrained('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade'); 
            $table->foreignId('semester_id')
                  ->constrained('semesters')
                  ->onDelete('cascade')
                  ->onUpdate('cascade'); 
            $table->integer('capacity');
            $table->timestamps();
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
