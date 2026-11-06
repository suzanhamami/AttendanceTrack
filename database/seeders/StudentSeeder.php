<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Semester;
use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\Section;
use App\Models\StudentSection;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fetch existing entities seeded by CourseSeeder
        $depSE = Department::where('department_name', 'Software Engineering')->first();
        
        $sem1 = Semester::where('semester_name', 'Fall Semester 2026')->first();
        $sem2 = Semester::where('semester_name', 'Spring Semester 2027')->first();
        
        $instructor = User::where('username', 'abdo_khoury')->first();
        
        $courseWeb1 = Course::where('course_code', 'CS100')->first();
        $courseWeb2 = Course::where('course_code', 'CS101')->first();

        // 2. Seed Students (Table 7: students)
        $student1 = Student::firstOrCreate(
            ['university_id' => '20260001'],
            [
                'full_name'     => 'Ahmad Ali',
                'national_id'   => '01010101011',
                'birth_date'    => '2004-05-12',
                'phone'         => '+963911111111',
                'email'         => 'ahmad.ali@attendance.edu',
                'department_id' => $depSE->getKey(),
                'academic_year' => '1',
                'status'        => 'active',
            ]
        );

        $student2 = Student::firstOrCreate(
            ['university_id' => '20260002'],
            [
                'full_name'     => 'Sara Omar',
                'national_id'   => '02020202022',
                'birth_date'    => '2004-08-20',
                'phone'         => '+963922222222',
                'email'         => 'sara.omar@attendance.edu',
                'department_id' => $depSE->getKey(),
                'academic_year' => '1',
                'status'        => 'active',
            ]
        );

        // 3. Seed Sections (Table 8: sections)
        $section1 = Section::firstOrCreate(
            ['section_name' => 'Sec-1 (Web 1)'],
            [
                'course_id'     => $courseWeb1->getKey(),
                'instructor_id' => $instructor->getKey(),
                'semester_id'   => $sem1->getKey(),
                'capacity'      => 30,
            ]
        );

        $section2 = Section::firstOrCreate(
            ['section_name' => 'Sec-1 (Web 2)'],
            [
                'course_id'     => $courseWeb2->getKey(),
                'instructor_id' => $instructor->getKey(),
                'semester_id'   => $sem2->getKey(),
                'capacity'      => 30,
            ]
        );

        // 4. Seed Student-Section Pivot (Table 9: student_section)
        StudentSection::firstOrCreate(
            [
                'student_id' => $student1->getKey(),
                'section_id' => $section1->getKey(),
            ],
            [
                'registration_date' => now(),
            ]
        );

        StudentSection::firstOrCreate(
            [
                'student_id' => $student2->getKey(),
                'section_id' => $section1->getKey(),
            ],
            [
                'registration_date' => now(),
            ]
        );

        StudentSection::firstOrCreate(
            [
                'student_id' => $student1->getKey(),
                'section_id' => $section2->getKey(),
            ],
            [
                'registration_date' => now(),
            ]
        );
    }
}