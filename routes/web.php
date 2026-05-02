<?php

use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\ScratchCardRequestController;
use App\Http\Controllers\Admin\SuperAdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\School\AcademicSessionController;
use App\Http\Controllers\School\GradingScaleController;
use App\Http\Controllers\School\ManualResultController;
use App\Http\Controllers\School\ResultPublishingController;
use App\Http\Controllers\School\ResultUploadController;
use App\Http\Controllers\School\SchoolAdminDashboardController;
use App\Http\Controllers\School\SchoolClassController;
use App\Http\Controllers\School\ScratchCardController;
use App\Http\Controllers\School\StudentBulkUploadController;
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

        Route::prefix('scratch-card-requests')
            ->name('scratch-card-requests.')
            ->group(function () {
                Route::get('/', [ScratchCardRequestController::class, 'index'])
                    ->name('index');

                Route::get('/{batch}', [ScratchCardRequestController::class, 'show'])
                    ->name('show');

                Route::post('/{batch}/confirm-payment', [ScratchCardRequestController::class, 'confirmPayment'])
                    ->name('confirm-payment');

                Route::post('/{batch}/generate', [ScratchCardRequestController::class, 'generate'])
                    ->name('generate');

                Route::get('/{batch}/download', [ScratchCardRequestController::class, 'download'])
                    ->name('download');

                Route::post('/{batch}/revoke', [ScratchCardRequestController::class, 'revokeBatch'])
                    ->name('revoke');
            });

        Route::post('/scratch-cards/{card}/revoke', [ScratchCardRequestController::class, 'revokeCard'])
            ->name('scratch-cards.revoke');
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

        Route::get('/students/upload', [StudentBulkUploadController::class, 'index'])
            ->name('students.upload.index');

        Route::post('/students/upload', [StudentBulkUploadController::class, 'store'])
            ->name('students.upload.store');

        Route::get('/students/upload/template', [StudentBulkUploadController::class, 'downloadTemplate'])
            ->name('students.upload.template');

        Route::resource('students', StudentController::class)
            ->except(['show', 'destroy']);

        Route::resource('grading-scales', GradingScaleController::class)
            ->parameters(['grading-scales' => 'gradingScale'])
            ->except(['show', 'destroy']);

        Route::prefix('scratch-cards')
            ->name('scratch-cards.')
            ->group(function () {
                Route::get('/', [ScratchCardController::class, 'index'])
                    ->name('index');

                Route::get('/create', [ScratchCardController::class, 'create'])
                    ->name('create');

                Route::post('/', [ScratchCardController::class, 'store'])
                    ->name('store');

                Route::get('/batches/{batch}', [ScratchCardController::class, 'show'])
                    ->name('show');

                Route::get('/batches/{batch}/download', [ScratchCardController::class, 'download'])
                    ->name('download');
            });

        Route::prefix('results')
            ->name('results.')
            ->group(function () {
                Route::resource('manual', ManualResultController::class)
                    ->parameters(['manual' => 'studentResult'])
                    ->except(['show', 'destroy']);

                Route::get('/publishing', [ResultPublishingController::class, 'index'])
                    ->name('publishing.index');

                Route::post('/publishing/publish', [ResultPublishingController::class, 'publish'])
                    ->name('publishing.publish');

                Route::post('/publishing/unpublish', [ResultPublishingController::class, 'unpublish'])
                    ->name('publishing.unpublish');

                Route::get('/upload', [ResultUploadController::class, 'index'])
                    ->name('upload.index');

                Route::post('/upload', [ResultUploadController::class, 'store'])
                    ->name('upload.store');

                Route::get('/upload/template', [ResultUploadController::class, 'downloadTemplate'])
                    ->name('upload.template');
            });
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
