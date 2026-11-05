<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DummyCourseController;
use App\Http\Controllers\CourseController;

// Public Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('login');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // -------------------------------------------------------------
    // MODULE 2: PHASE 1 - DUMMY STATIC DATA
    // -------------------------------------------------------------
    Route::prefix('dummy/courses')->name('dummy.courses.')->group(function () {
        Route::get('/', [DummyCourseController::class, 'index'])->name('index');
        Route::get('/create', [DummyCourseController::class, 'create'])->name('create');
        Route::post('/', [DummyCourseController::class, 'store'])->name('store');
        Route::get('/{id}', [DummyCourseController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DummyCourseController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DummyCourseController::class, 'update'])->name('update');
        Route::delete('/{id}', [DummyCourseController::class, 'destroy'])->name('destroy');
    });

    // -------------------------------------------------------------
    // MODULE 2: PHASE 2 - DATABASE-BACKED CRUD
    // -------------------------------------------------------------
    // Standard operations accessible by both Employee and Manager
    Route::middleware(['role:manager,employee'])->prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/create', [CourseController::class, 'create'])->name('create');
        Route::post('/', [CourseController::class, 'store'])->name('store');
        Route::get('/{course:course_id}', [CourseController::class, 'show'])->name('show');
        Route::get('/{course:course_id}/edit', [CourseController::class, 'edit'])->name('edit');
        Route::put('/{course:course_id}', [CourseController::class, 'update'])->name('update');
    });

    // Administrative operations exclusive to Manager
    Route::middleware(['role:manager'])->prefix('courses')->name('courses.')->group(function () {
        Route::patch('/{course:course_id}/toggle-status', [CourseController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{course:course_id}', [CourseController::class, 'destroy'])->name('destroy');
    });
});