<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesUserRole;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Grade;

/**
 * Block 5 home page (dashboard). Users of this block land here after login.
 * The control-panel buttons change with the role: the manager also sees exam types and results.
 */
class ExamDashboardController extends Controller
{
    use ResolvesUserRole;

    public function index()
    {
        $today = now()->toDateString();

        $stats = [
            'exams'    => Exam::count(),
            'upcoming' => Exam::whereDate('exam_date', '>=', $today)->count(),
            'grades'   => Grade::count(),
            'types'    => ExamType::count(),
            // Exams already held that still have no recorded grade.
            'awaiting' => Exam::whereDate('exam_date', '<', $today)->doesntHave('grades')->count(),
        ];

        $upcoming = Exam::with(['type', 'course', 'section'])
            ->whereDate('exam_date', '>=', $today)
            ->orderBy('exam_date')
            ->orderBy('exam_time')
            ->limit(5)
            ->get();

        return view('exams.dashboard', [
            'stats'     => $stats,
            'upcoming'  => $upcoming,
            'isManager' => $this->isManager(),
        ]);
    }
}
