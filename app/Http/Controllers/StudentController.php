<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Department;
use Illuminate\Http\Request;

class StudentController extends Controller
{
      /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::with(['department', 'sections']);

        // Filters based on project requirements (e.g., department, academic year, status)
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('university_id', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(10);
        $departments = Department::all();

        return view('students.index', compact('students', 'departments'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('students.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'     => 'required|string|min:3|max:100',
            'university_id' => 'required|string|unique:students,university_id',
            'national_id'   => 'required|string|digits:11|unique:students,national_id',
            'birth_date'    => 'required|date|before_or_equal:' . now()->subYears(17)->format('Y-m-d'),
            'phone'         => 'required|string|max:15',
            'email'         => 'nullable|email|max:100|unique:students,email',
            'department_id' => 'required|exists:departments,department_id',
            'academic_year' => 'required|in:1,2,3,4',
            'photo'         => 'nullable|image|mimes:jpeg,png|max:2048', 
            'status'        => 'required|in:active,suspended,graduated',
        ]);

        // Default status to active if not provided
        $validated['status'] = $validated['status'] ?? 'active';

        Student::create($validated);

        return redirect()->route('students.index')->with('success', 'Student added successfully.');
    }

    public function show(Student $student)
    {
        // Load department and enrolled sections/grades
        $student->load(['department', 'sections']);
        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $departments = Department::all();
        return view('students.edit', compact('student', 'departments'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'full_name'     => 'required|string|min:3|max:100',
            'university_id' => 'required|string|unique:students,university_id,' . $student->student_id . ',student_id',
            'national_id'   => 'required|string|digits:11|unique:students,national_id,' . $student->student_id . ',student_id',
            'birth_date'    => 'required|date|before_or_equal:' . now()->subYears(17)->format('Y-m-d'),
            'phone'         => 'required|string|max:15',
            'email'         => 'nullable|email|max:100|unique:students,email,' . $student->student_id . ',student_id',
            'department_id' => 'required|exists:departments,department_id',
            'academic_year' => 'required|in:1,2,3,4',
            'photo'         => 'nullable|image|mimes:jpeg,png|max:2048', 
            'status'        => 'required|in:active,suspended,graduated',
        ]);

        $student->update($validated);

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    public function toggleStatus(Student $student)
    {
        $student->status = ($student->status === 'active') ? 'inactive' : 'active';
        $student->save();

        return redirect()->route('students.index')->with('success', 'Student status updated successfully.');
    }

    public function destroy(Student $student)
    {
        // Business Rule: Check that a student cannot be deleted if registered in sections or has grades
        if ($student->sections()->exists() || method_exists($student, 'grades') && $student->grades()->exists()) {
            return back()->with('error', 'Cannot delete student: the student is registered in sections or has recorded grades.');
        }

        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }
}

// 'section_id' => [
//         'required',
//         'exists:sections,id',
//         // التحقق من عدم تكرار تسجيل الطالب في نفس الشعبة (منع التسجيل المكرر)[cite: 1]
//         Rule::unique('student_section')->where(function ($query) use ($request) {
//             return $query->where('student_id', $request->student_id)
//                          ->where('section_id', $request->section_id);
//         }),
//     ],