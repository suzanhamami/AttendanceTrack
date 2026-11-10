<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Block 5 sample data: exam types, exams and grades.
 *
 * Run it AFTER the seeders of the other modules (CourseSeeder, StudentSeeder), because the exams
 * are attached to the existing sections and the grades to the students registered in them:
 *
 *   php artisan db:seed --class=ExamSeeder
 *
 * It uses firstOrCreate, so it can be run several times without duplicating anything.
 */
class ExamSeeder extends Seeder
{
    public function run(): void
    {
        // Exam types (the list required by the spec: midterm, final, practical, oral)
        $types = [];
        foreach (['Midterm', 'Final', 'Practical', 'Oral'] as $name) {
            $types[$name] = ExamType::firstOrCreate(['type_name' => $name]);
        }

        $sections = Section::all();

        if ($sections->isEmpty()) {
            $this->command?->warn('ExamSeeder: no sections found. Run CourseSeeder and StudentSeeder first to get exams and grades.');

            return;
        }

        // Share of the maximum grade given to the 1st, 2nd, 3rd... student (some are below the passing mark).
        $fractions = [0.90, 0.72, 0.58, 0.46, 0.34];

        foreach ($sections as $section) {
            // Students registered in this section (student_section table), in a stable order.
            $students = Student::whereIn(
                'student_id',
                DB::table('student_section')->select('student_id')->where('section_id', $section->section_id)
            )->orderBy('student_id')->get();

            // A midterm and a practical exam that already took place (with grades) ...
            $past = [
                ['Midterm',   now()->subDays(20), '09:00', 60, 40, 20, 'Hall A-101'],
                ['Practical', now()->subDays(10), '11:00', 90, 30, 15, 'Lab L-3'],
            ];

            foreach ($past as [$type, $date, $time, $duration, $max, $min, $room]) {
                $exam = Exam::firstOrCreate(
                    ['exam_type_id' => $types[$type]->exam_type_id, 'section_id' => $section->section_id],
                    [
                        'course_id'  => $section->course_id,
                        'exam_date'  => $date->toDateString(),
                        'exam_time'  => $time,
                        'duration'   => $duration,
                        'max_grade'  => $max,
                        'min_grade'  => $min,
                        'room'       => $room,
                    ]
                );

                foreach ($students as $i => $student) {
                    Grade::firstOrCreate(
                        ['exam_id' => $exam->exam_id, 'student_id' => $student->student_id],
                        [
                            'grade'         => round($max * $fractions[$i % count($fractions)], 2),
                            'recorded_date' => $date->copy()->addDay()->toDateString(),
                        ]
                    );
                }
            }

            // ... and a final exam in the future, without grades yet.
            Exam::firstOrCreate(
                ['exam_type_id' => $types['Final']->exam_type_id, 'section_id' => $section->section_id],
                [
                    'course_id' => $section->course_id,
                    'exam_date' => now()->addDays(30)->toDateString(),
                    'exam_time' => '10:00',
                    'duration'  => 120,
                    'max_grade' => 100,
                    'min_grade' => 50,
                    'room'      => 'Hall B-202',
                ]
            );
        }
    }
}
