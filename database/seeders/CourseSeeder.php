<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Department;
use App\Models\Semester;
use App\Models\User;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $managerRole = Role::firstOrCreate(
            ['role_name' => 'manager'],
            ['description' => 'Department Manager']
        );

        $employeeRole = Role::firstOrCreate(
            ['role_name' => 'employee'],
            ['description' => 'Department Staff']
        );

        // 2. Departments
        $depSE = Department::firstOrCreate(['department_name' => 'Software Engineering']);
        $depIS = Department::firstOrCreate(['department_name' => 'Information Systems']);
        $depAI = Department::firstOrCreate(['department_name' => 'Artificial Intelligence']);

        // 3. Semesters
        $sem1 = Semester::firstOrCreate(
            ['semester_name' => 'Fall Semester 2026'],
            ['start_date' => '2026-09-15', 'end_date' => '2027-01-20']
        );

        $sem2 = Semester::firstOrCreate(
            ['semester_name' => 'Spring Semester 2027'],
            ['start_date' => '2027-02-10', 'end_date' => '2027-06-15']
        );

        // 4. Users (Manager, Employee, Instructor)
        User::firstOrCreate(
            ['username' => 'manager'],
            [
                'full_name' => 'Dr. Department Manager',
                'email' => 'manager@attendance.edu',
                'password' => Hash::make('12345678'),
                'role_id' => $managerRole->getKey(),             
                'department_id' => $depSE->getKey(),            
                'status' => 'active'
            ]
        );

        User::firstOrCreate(
            ['username' => 'employee'],
            [
                'full_name' => 'Staff Employee',
                'email' => 'employee@attendance.edu',
                'password' => Hash::make('12345678'),
                'role_id' => $employeeRole->getKey(),
                'department_id' => $depSE->getKey(),
                'status' => 'active'
            ]
        );

        $instructor = User::firstOrCreate(
            ['username' => 'abdo_khoury'],
            [
                'full_name' => 'Dr. Abdo Khoury',
                'email' => 'abdo.khoury@attendance.edu',
                'password' => Hash::make('12345678'),
                'role_id' => $employeeRole->getKey(),
                'department_id' => $depSE->getKey(),
                'status' => 'active'
            ]
        );

        // 5. Courses 
        $courseWeb1 = Course::firstOrCreate(
            ['course_code' => 'CS100'],
            [
                'course_name' => 'Web Programming 1',
                'credit_hours' => 3,
                'department_id' => $depSE->getKey(),
                'instructor_id' => $instructor->getKey(),
                'semester_id' => $sem1->getKey(),
                'academic_year' => '2026-2027',
                'description' => 'Fundamental web principles including HTML, CSS, JavaScript, and PHP.',
                'prerequisite_id' => null,
                'status' => 'active',
            ]
        );

        Course::firstOrCreate(
            ['course_code' => 'CS101'],
            [
                'course_name' => 'Web Programming 2',
                'credit_hours' => 3,
                'department_id' => $depSE->getKey(),
                'instructor_id' => $instructor->getKey(),
                'semester_id' => $sem2->getKey(),
                'academic_year' => '2026-2027',
                'description' => 'Advanced web development using PHP 8.2 and Laravel framework.',
                'prerequisite_id' => $courseWeb1->getKey(),
                'status' => 'active',
            ]
        );

        Course::firstOrCreate(
            ['course_code' => 'CS102'],
            [
                'course_name' => 'Database Systems 2',
                'credit_hours' => 3,
                'department_id' => $depIS->getKey(),
                'instructor_id' => $instructor->getKey(),
                'semester_id' => $sem1->getKey(),
                'academic_year' => '2026-2027',
                'description' => 'Relational database architecture, queries, and optimization.',
                'prerequisite_id' => null,
                'status' => 'active',
            ]
        );

        Course::firstOrCreate(
            ['course_code' => 'AI301'],
            [
                'course_name' => 'Artificial Intelligence',
                'credit_hours' => 3,
                'department_id' => $depAI->getKey(),
                'instructor_id' => $instructor->getKey(),
                'semester_id' => $sem2->getKey(),
                'academic_year' => '2026-2027',
                'description' => 'Search algorithms, problem solving, and intro to machine learning.',
                'prerequisite_id' => null,
                'status' => 'inactive',
            ]
        );

        Course::firstOrCreate(
            ['course_code' => 'SE201'],
            [
                'course_name' => 'Software Engineering 1',
                'credit_hours' => 4,
                'department_id' => $depSE->getKey(),
                'instructor_id' => $instructor->getKey(),
                'semester_id' => $sem1->getKey(),
                'academic_year' => '2026-2027',
                'description' => 'Software development lifecycle, requirements engineering, and design patterns.',
                'prerequisite_id' => null,
                'status' => 'active',
            ]
        );
    }
}