<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesUserRole;
use App\Http\Requests\ExamTypeRequest;
use App\Models\ExamType;

/**
 * Block 5 - Managing exam types (Midterm, Final, ...). Department manager only.
 */
class ExamTypeController extends Controller
{
    use ResolvesUserRole;

    public function index()
    {
        $this->abortUnlessManager();

        return view('exam_types.index', [
            'types'     => ExamType::withCount('exams')->orderBy('type_name')->get(),
            'isManager' => true,
        ]);
    }

    public function store(ExamTypeRequest $request)
    {
        $this->abortUnlessManager();

        ExamType::create($request->validated());

        return redirect()->route('exam_types.index')->with('success', 'Exam type added successfully.');
    }

    public function edit(ExamType $examType)
    {
        $this->abortUnlessManager();

        return view('exam_types.edit', ['type' => $examType, 'isManager' => true]);
    }

    public function update(ExamTypeRequest $request, ExamType $examType)
    {
        $this->abortUnlessManager();

        $examType->update($request->validated());

        return redirect()->route('exam_types.index')->with('success', 'Exam type updated successfully.');
    }

    /** An exam type that is used by exams cannot be deleted. */
    public function destroy(ExamType $examType)
    {
        $this->abortUnlessManager();

        if ($examType->exams()->exists()) {
            return back()->with('error', 'Cannot delete this exam type: it is used by existing exams.');
        }

        $examType->delete();

        return redirect()->route('exam_types.index')->with('success', 'Exam type deleted successfully.');
    }
}
