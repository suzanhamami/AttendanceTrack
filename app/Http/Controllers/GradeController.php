<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesUserRole;
use App\Http\Requests\GradeBatchRequest;
use App\Models\Exam;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;

/**
 * Block 5 - Recording and updating the grades of one exam (manager and employee).
 */
class GradeController extends Controller
{
    use ResolvesUserRole;

    /** Form listing the students registered in the exam's section with their current grade. */
    public function edit(Exam $exam)
    {
        $exam->load(['type', 'course', 'section']);

        return view('exams.grades', [
            'exam'      => $exam,
            'students'  => $exam->enrolledStudents()->orderBy('full_name')->get(),
            'grades'    => $exam->grades()->get()->keyBy('student_id'),
            'isManager' => $this->isManager(),
        ]);
    }

    /**
     * Saves every filled grade. A student that already has a grade gets it updated
     * (unique exam_id + student_id), a new one is inserted. Empty inputs are ignored.
     */
    public function update(GradeBatchRequest $request, Exam $exam)
    {
        $date = $request->validated('recorded_date');
        $saved = 0;

        DB::transaction(function () use ($request, $exam, $date, &$saved) {
            foreach ($request->validated('grades') as $studentId => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                Grade::updateOrCreate(
                    ['exam_id' => $exam->exam_id, 'student_id' => (int) $studentId],
                    ['grade' => $value, 'recorded_date' => $date]
                );

                $saved++;
            }
        });

        return redirect()->route('exams.show', $exam)->with('success', "$saved grade(s) saved successfully.");
    }
}
