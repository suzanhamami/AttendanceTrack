<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesUserRole;
use App\Models\Department;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Block 5 - Issuing results: the final grades statement (transcript) of a student.
 * Department manager only ("إصدار النتائج").
 */
class ResultController extends Controller
{
    use ResolvesUserRole;

    /** Student search (Ajax-friendly) leading to the statement of each student. */
    public function index(Request $request)
    {
        $this->abortUnlessManager();

        // Only plain values are accepted as filters: a crafted `?department_id[]=1` must not reach the query builder.
        $filters = array_filter($request->only(['department_id', 'academic_year', 'search']), 'is_scalar');

        // grades_count comes from a subquery, so this module does not need a grades()
        // relation on the Student model (which belongs to the students module).
        $students = Student::with('department')
            ->addSelect(['students.*', 'grades_count' => Grade::query()
                ->selectRaw('count(*)')
                ->whereColumn('grades.student_id', 'students.student_id'),
            ])
            ->when(($filters['department_id'] ?? '') !== '', fn ($q) => $q->where('department_id', $filters['department_id']))
            ->when(($filters['academic_year'] ?? '') !== '', fn ($q) => $q->where('academic_year', $filters['academic_year']))
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function ($q) use ($filters) {
                $term = '%' . trim($filters['search']) . '%';
                $q->where(fn ($w) => $w->where('full_name', 'like', $term)->orWhere('university_id', 'like', $term));
            })
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('results._table', compact('students'));
        }

        return view('results.index', [
            'students'    => $students,
            'departments' => Department::orderBy('department_name')->get(),
            'isManager'   => true,
        ]);
    }

    /** Final grades statement of one student, grouped by course. */
    public function show(Student $student)
    {
        $this->abortUnlessManager();

        $student->load('department');

        $grades = Grade::with(['exam.type', 'exam.course'])
            ->where('student_id', $student->student_id)
            ->get()
            ->sortBy(fn (Grade $g) => $g->exam->exam_date)
            ->values();

        // One block per course: its graded exams and the combined result.
        $courses = $grades->groupBy(fn (Grade $g) => $g->exam->course_id)->map(function ($rows) {
            return ['course' => $rows->first()->exam->course, 'grades' => $rows, 'summary' => $this->summary($rows)];
        })->values();

        $overall = $this->summary($grades);

        return view('results.show', [
            'student'   => $student,
            'courses'   => $courses,
            'overall'   => $overall,
            'isManager' => true,
        ]);
    }

    /** Combined result of a set of grades: sum of grades / sum of maximum grades. */
    private function summary($grades): array
    {
        $total = $grades->sum(fn (Grade $g) => (float) $g->grade);
        $maxTotal = $grades->sum(fn (Grade $g) => (float) $g->exam->max_grade);
        $passed = $grades->where('is_passed', true)->count();

        return [
            'total'        => round($total, 2),
            'max_total'    => round($maxTotal, 2),
            'percentage'   => $maxTotal > 0 ? round($total / $maxTotal * 100, 2) : 0.0,
            'passed_exams' => $passed,
            'failed_exams' => $grades->count() - $passed,
            'exams'        => $grades->count(),
        ];
    }
}
