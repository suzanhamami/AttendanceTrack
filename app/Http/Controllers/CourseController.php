<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['department', 'instructor', 'semester', 'prerequisite']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }
        if ($request->filled('academic_year')) {
            $query->where('academic_year', 'like', '%' . $request->academic_year . '%');
        }

        $courses = $query->paginate(10);
        $departments = Department::all();
        $semesters = Semester::all();

        return view('courses.index', compact('courses', 'departments', 'semesters'));
    }

   public function create()
    {
        $departments = Department::all();
        $semesters = Semester::all();
        $instructors = User::where('status', 'active')->get();
        $prerequisites = Course::where('status', 'active')->get();

        return view('courses.create', compact('departments', 'semesters', 'instructors', 'prerequisites'));
    }

   public function store(Request $request)
    {
        $validated = $request->validate([
            'course_name' => 'required|string|min:3|max:100|unique:courses,course_name',
            'course_code' => 'required|string|max:20|unique:courses,course_code',
            'credit_hours' => 'required|integer|between:1,6',
            'department_id' => 'required|exists:departments,department_id',
            'instructor_id' => 'required|exists:users,user_id',
            'semester_id' => 'required|exists:semesters,semester_id',
            'academic_year' => 'required|string|max:20',
            'description' => 'nullable|string|max:500',
            'prerequisite_id' => 'nullable|exists:courses,course_id',
        ]);

        $validated['status'] = 'active';
        Course::create($validated);

        return redirect()->route('courses.index')->with('success', 'Course added successfully.');
    }

    public function show(Course $course)
    {
        $course->load(['department', 'instructor', 'semester', 'prerequisite']);
        return view('courses.show', compact('course'));
    }

   public function edit(Course $course)
    {
        $departments = Department::all();
        $semesters = Semester::all();
        $instructors = User::where('status', 'active')->get();
        $prerequisites = Course::where('course_id', '!=', $course->course_id)->where('status', 'active')->get();

        return view('courses.edit', compact('course', 'departments', 'semesters', 'instructors', 'prerequisites'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'course_name' => 'required|string|min:3|max:100|unique:courses,course_name,' . $course->course_id . ',course_id',
            'course_code' => 'required|string|max:20|unique:courses,course_code,' . $course->course_id . ',course_id',
            'credit_hours' => 'required|integer|between:1,6',
            'department_id' => 'required|exists:departments,department_id',
            'instructor_id' => 'required|exists:users,user_id',
            'semester_id' => 'required|exists:semesters,semester_id',
            'academic_year' => 'required|string|max:20',
            'description' => 'nullable|string|max:500',
            'prerequisite_id' => 'nullable|different:course_id|exists:courses,course_id',
        ]);

        $course->update($validated);

        return redirect()->route('courses.index')->with('success', 'Course updated successfully.');
    }

    public function toggleStatus(Course $course)
    {
        $course->status = ($course->status === 'active') ? 'inactive' : 'active';
        $course->save();

        return redirect()->route('courses.index')->with('success', 'Course status updated successfully.');
    }

   public function destroy(Course $course)
    {
        if (Course::where('prerequisite_id', $course->course_id)->exists()) {
            return back()->with('error', 'Cannot delete course: it is a prerequisite for another course.');
        }

        $course->delete();
        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}