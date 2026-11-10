<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

use App\Http\Controllers\Block1LoginController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Block 1 routes - loaded by App\Providers\Block1ServiceProvider (web middleware group)
|--------------------------------------------------------------------------
| Permissions come from the permissions table:
|   employee : users.view, users.create, users.edit
|   manager  : + users.status, users.delete, roles.manage
*/

// Fallback login for running Block 1 alone. This file is loaded before routes/web.php, so when
// web.php defines the shared /login, /logout routes (AuthController), those replace these three.
Route::get('/login', [Block1LoginController::class, 'show'])->name('login');
Route::post('/login', [Block1LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [Block1LoginController::class, 'logout'])->name('logout');

$can = fn (string $permission) => CheckPermission::class . ':' . $permission;

Route::middleware('auth')->group(function () use ($can) {

    Route::get('/users/dashboard', [UserDashboardController::class, 'index'])->middleware($can('users.view'))->name('users.dashboard');

    Route::middleware($can('users.view'))->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->whereNumber('user')->name('users.show');
    });

    Route::middleware($can('users.create'))->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware($can('users.edit'))->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->whereNumber('user')->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->whereNumber('user')->name('users.update');
    });

    // Manager only
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->whereNumber('user')->middleware($can('users.status'))->name('users.toggle-status');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->whereNumber('user')->middleware($can('users.delete'))->name('users.destroy');

    Route::middleware($can('roles.manage'))->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->whereNumber('role')->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->whereNumber('role')->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->whereNumber('role')->name('roles.destroy');
    });
});
