<?php

use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SuperAdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\School\AcademicSessionController;
use App\Http\Controllers\School\SchoolAdminDashboardController;
use App\Http\Controllers\School\SchoolClassController;
use App\Http\Controllers\School\StudentController;
use App\Http\Controllers\School\SubjectController;
use App\Http\Controllers\School\TermController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('super_admin')) {
        return redirect()->route('admin.dashboard');
    }

    if (auth()->user()->hasRole('school_admin')) {
        return redirect()->route('school.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('schools', SchoolController::class)
            ->except(['show', 'destroy']);
    });

Route::middleware(['auth', 'role:school_admin'])
    ->prefix('school')
    ->name('school.')
    ->group(function () {
        Route::get('/dashboard', [SchoolAdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('classes', SchoolClassController::class)
            ->parameters(['classes' => 'class'])
            ->except(['show', 'destroy']);

        Route::resource('subjects', SubjectController::class)
            ->except(['show', 'destroy']);

        Route::resource('sessions', AcademicSessionController::class)
            ->parameters(['sessions' => 'academicSession'])
            ->except(['show', 'destroy']);

        Route::resource('terms', TermController::class)
            ->except(['show', 'destroy']);

        Route::resource('students', StudentController::class)
            ->except(['show', 'destroy']);
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';
