<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamDashboardController;
use App\Http\Controllers\ExamTypeController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Block 5 - Exam & Grades Management routes
|--------------------------------------------------------------------------
| Loaded from routes/web.php (one `require` line, so the other modules' route
| definitions are not touched).
|
| Uses the `auth` middleware and the `role:` alias (CheckRole) from the shared code:
|   - manager + employee : search/add/edit/view exams and record grades
|   - manager only       : delete exams, manage exam types, issue results
*/

Route::middleware(['auth'])->group(function () {

    // Standard operations (department manager and employee)
    Route::middleware(['role:manager,employee'])->group(function () {
        Route::get('/exams/dashboard', [ExamDashboardController::class, 'index'])->name('exams.dashboard');
        Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
        Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('/exams/{exam}', [ExamController::class, 'show'])->whereNumber('exam')->name('exams.show');
        Route::get('/exams/{exam}/edit', [ExamController::class, 'edit'])->whereNumber('exam')->name('exams.edit');
        Route::put('/exams/{exam}', [ExamController::class, 'update'])->whereNumber('exam')->name('exams.update');

        Route::get('/exams/{exam}/grades', [GradeController::class, 'edit'])->whereNumber('exam')->name('exams.grades.edit');
        Route::put('/exams/{exam}/grades', [GradeController::class, 'update'])->whereNumber('exam')->name('exams.grades.update');
    });

    // Administrative operations exclusive to the manager
    Route::middleware(['role:manager'])->group(function () {
        Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->whereNumber('exam')->name('exams.destroy');

        Route::resource('exam-types', ExamTypeController::class)
            ->except(['create', 'show'])
            ->parameters(['exam-types' => 'examType'])
            ->names('exam_types');

        Route::get('/results', [ResultController::class, 'index'])->name('results.index');
        Route::get('/results/{student}', [ResultController::class, 'show'])->whereNumber('student')->name('results.show');
    });
});
