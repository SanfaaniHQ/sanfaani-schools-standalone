<?php

use App\Http\Controllers\Installer\InstallerController;
use Illuminate\Support\Facades\Route;

Route::prefix('install')
    ->name('installer.')
    ->middleware(['installer.access', 'prevent.reinstall'])
    ->group(function () {
        Route::get('/', [InstallerController::class, 'welcome'])->name('welcome');
        Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
        Route::get('/permissions', [InstallerController::class, 'permissions'])->name('permissions');
        Route::get('/database', [InstallerController::class, 'database'])->name('database');
        Route::get('/environment', [InstallerController::class, 'environment'])->name('environment');
        Route::get('/app-key', [InstallerController::class, 'appKey'])->name('app-key');
        Route::get('/migrations', [InstallerController::class, 'migrations'])->name('migrations');
        Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
        Route::post('/admin', [InstallerController::class, 'storeAdmin'])->name('admin.store');
        Route::get('/school', [InstallerController::class, 'school'])->name('school');
        Route::post('/school', [InstallerController::class, 'storeSchool'])->name('school.store');
        Route::get('/smtp', [InstallerController::class, 'smtp'])->name('smtp');
        Route::post('/smtp', [InstallerController::class, 'storeSmtp'])->name('smtp.store');
        Route::get('/review', [InstallerController::class, 'review'])->name('review');
        Route::post('/complete', [InstallerController::class, 'complete'])->name('complete');
    });
