<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DummyStudentController;
use App\Http\Controllers\StudentController;

// Public Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('login');
    // return view('welcome');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // -------------------------------------------------------------
    // MODULE 2: PHASE 1 - DUMMY STATIC DATA
    // -------------------------------------------------------------

    Route::prefix('dummy/students')->name('dummy.students.')->group(function () {
        Route::get('/', [DummyStudentController::class, 'index'])->name('index');
        Route::get('/create', [DummyStudentController::class, 'create'])->name('create');
        Route::post('/', [DummyStudentController::class, 'store'])->name('store');
        Route::get('/{id}', [DummyStudentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DummyStudentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DummyStudentController::class, 'update'])->name('update');
        Route::delete('/{id}', [DummyStudentController::class, 'destroy'])->name('destroy');
    });

    // -------------------------------------------------------------
    // MODULE 2: PHASE 2 - DATABASE-BACKED CRUD
    // -------------------------------------------------------------

    // Standard operations accessible by both Employee and Manager
    Route::middleware(['role:manager,employee'])->prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/create', [StudentController::class, 'create'])->name('create');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('/{student:student_id}', [StudentController::class, 'show'])->name('show');
        Route::get('/{student:student_id}/edit', [StudentController::class, 'edit'])->name('edit');
        Route::put('/{student:student_id}', [StudentController::class, 'update'])->name('update');
    });

    // Administrative operations exclusive to Manager
    Route::middleware(['role:manager'])->prefix('students')->name('students.')->group(function () {
        Route::patch('/{student:student_id}/toggle-status', [StudentController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{student:student_id}', [StudentController::class, 'destroy'])->name('destroy');
    });

});

