<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesUserRole;
use App\Http\Requests\ExamRequest;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Section;
use Illuminate\Http\Request;

/**
 * Block 5 - Exam management (list/search, create, details, edit, delete).
 *
 * Department employee: add, edit, search, view details (and record grades, see GradeController).
 * Department manager : everything above + delete exams.
 */
class ExamController extends Controller
{
    use ResolvesUserRole;

    /** Grid View with advanced search. Ajax requests only get the table fragment back. */
    public function index(Request $request)
    {
        // Only plain values are accepted as filters: a crafted `?course_id[]=1` must not reach the query builder.
        $filters = array_filter(
            $request->only(['exam_type_id', 'section_id', 'course_id', 'exam_date', 'search']),
            'is_scalar'
        );

        $exams = Exam::with(['type', 'course', 'section'])
            ->withCount('grades')
            ->filter($filters)
            ->orderByDesc('exam_date')
            ->orderBy('exam_time')
            ->paginate(10)
            ->withQueryString();

        $isManager = $this->isManager();

        if ($request->ajax()) {
            return view('exams._table', compact('exams', 'isManager'));
        }

        return view('exams.index', [
            'exams'     => $exams,
            'isManager' => $isManager,
            'types'     => ExamType::orderBy('type_name')->get(),
            'courses'   => Course::orderBy('course_name')->get(),
            'sections'  => Section::with('course')->orderBy('section_name')->get(),
        ]);
    }

    public function create()
    {
        return view('exams.create', $this->formData() + ['isManager' => $this->isManager()]);
    }

    public function store(ExamRequest $request)
    {
        $exam = Exam::create($request->validated());

        return redirect()->route('exams.show', $exam)->with('success', 'Exam added successfully.');
    }

    /** Full exam details with the grades of the students registered in its section. */
    public function show(Exam $exam)
    {
        $exam->load(['type', 'course', 'section.instructor']);

        $students = $exam->enrolledStudents()->orderBy('full_name')->get();
        $grades = $exam->grades()->get()->keyBy('student_id');

        // Every grade belongs to this exam: set the relation so the accessors need no extra query.
        foreach ($grades as $grade) {
            $grade->setRelation('exam', $exam);
        }

        // Simple statistics of the recorded grades (Collection methods).
        $values = $grades->pluck('grade')->map(fn ($g) => (float) $g);
        $passed = $grades->where('is_passed', true)->count();
        $stats = [
            'count'     => $values->count(),
            'average'   => $values->isEmpty() ? null : round($values->avg(), 2),
            'highest'   => $values->max(),
            'lowest'    => $values->min(),
            'pass_rate' => $values->isEmpty() ? null : round($passed / $values->count() * 100, 2),
        ];

        return view('exams.show', [
            'exam'      => $exam,
            'students'  => $students,
            'grades'    => $grades,
            'stats'     => $stats,
            'isManager' => $this->isManager(),
        ]);
    }

    public function edit(Exam $exam)
    {
        return view('exams.edit', $this->formData() + [
            'exam'      => $exam,
            'hasGrades' => $exam->grades()->exists(),
            'isManager' => $this->isManager(),
        ]);
    }

    public function update(ExamRequest $request, Exam $exam)
    {
        $exam->update($request->validated());

        return redirect()->route('exams.show', $exam)->with('success', 'Exam updated successfully.');
    }

    /** Manager only, and only when no grades are recorded for the exam. */
    public function destroy(Exam $exam)
    {
        $this->abortUnlessManager();

        if ($exam->grades()->exists()) {
            return back()->with('error', 'Cannot delete this exam: grades are already recorded for it.');
        }

        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Exam deleted successfully.');
    }

    /** Dropdown data shared by the create and edit forms. */
    private function formData(): array
    {
        return [
            'types'    => ExamType::orderBy('type_name')->get(),
            'courses'  => Course::orderBy('course_name')->get(),
            'sections' => Section::with('course')->orderBy('section_name')->get(),
        ];
    }
}
