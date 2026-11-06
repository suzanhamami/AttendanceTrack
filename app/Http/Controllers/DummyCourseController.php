<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Student: Louay Arnaba Khordaji - 202310241
 */
class DummyCourseController extends Controller
{
    private static array $courses = [
        [
            'id' => 1,
            'course_name' => 'Web Programming 2',
            'course_code' => 'CS101',
            'credit_hours' => 3,
            'department' => 'Software Engineering',
            'instructor' => 'Dr. Abdo Khoury',
            'semester' => 'Semester 1',
            'academic_year' => '2026-2027',
            'description' => 'Advanced web development using PHP 8.2 and Laravel framework.',
            'prerequisite' => 'Web Programming 1',
            'status' => 'active'
        ],
        [
            'id' => 2,
            'course_name' => 'Database Systems 2',
            'course_code' => 'CS102',
            'credit_hours' => 3,
            'department' => 'Information Systems',
            'instructor' => 'Dr. Rami Ahmed',
            'semester' => 'Semester 1',
            'academic_year' => '2026-2027',
            'description' => 'Advanced relational database design, indexing, and query optimization.',
            'prerequisite' => 'Database Systems 1',
            'status' => 'active'
        ],
        [
            'id' => 3,
            'course_name' => 'Software Engineering 1',
            'course_code' => 'SE201',
            'credit_hours' => 4,
            'department' => 'Software Engineering',
            'instructor' => 'Dr. Hassan Youssef',
            'semester' => 'Semester 2',
            'academic_year' => '2026-2027',
            'description' => 'Software lifecycle methodologies, requirements analysis, and UML modeling.',
            'prerequisite' => 'Structured Programming',
            'status' => 'active'
        ],
        [
            'id' => 4,
            'course_name' => 'Artificial Intelligence',
            'course_code' => 'AI301',
            'credit_hours' => 3,
            'department' => 'Artificial Intelligence',
            'instructor' => 'Dr. Samer Khaled',
            'semester' => 'Semester 2',
            'academic_year' => '2026-2027',
            'description' => 'Heuristic search algorithms, knowledge representation, and machine learning basics.',
            'prerequisite' => 'Discrete Mathematics',
            'status' => 'inactive'
        ],
        [
            'id' => 5,
            'course_name' => 'Network Security',
            'course_code' => 'NET401',
            'credit_hours' => 2,
            'department' => 'Networks & Telecom',
            'instructor' => 'Dr. Layla Khalil',
            'semester' => 'Summer Semester',
            'academic_year' => '2026-2027',
            'description' => 'Cryptographic protocols, firewall configurations, and defensive cyber techniques.',
            'prerequisite' => 'Computer Networks',
            'status' => 'active'
        ]
    ];

    public function index()
    {
        $courses = self::$courses;
        return view('courses.dummy_index', compact('courses'));
    }

    public function create()
    {
        return view('courses.dummy_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string|min:3|max:100',
            'course_code' => 'required|string',
            'credit_hours' => 'required|integer|between:1,6',
        ]);

        return redirect()->route('dummy.courses.index')->with('success', 'Course simulated and added successfully to static memory.');
    }

    public function show(int $id)
    {
        $course = collect(self::$courses)->firstWhere('id', $id);
        abort_if(!$course, 404, 'Course not found.');
        return view('courses.dummy_show', compact('course'));
    }

    public function edit(int $id)
    {
        $course = collect(self::$courses)->firstWhere('id', $id);
        abort_if(!$course, 404, 'Course not found.');
        return view('courses.dummy_edit', compact('course'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'course_name' => 'required|string|min:3|max:100',
            'course_code' => 'required|string',
            'credit_hours' => 'required|integer|between:1,6',
        ]);

        return redirect()->route('dummy.courses.index')->with('success', 'Course simulated and updated successfully.');
    }

    public function destroy(int $id)
    {
        return redirect()->route('dummy.courses.index')->with('success', 'Course simulated and deleted successfully.');
    }
}