<?php

use App\Http\Controllers\Api\PublicAdmissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('public/admissions')
    ->middleware('throttle:'.config('admissions.api_throttle', '10,1'))
    ->group(function () {
        Route::get('/config', [PublicAdmissionController::class, 'config'])
            ->name('api.public.admissions.config');
        Route::post('/', [PublicAdmissionController::class, 'store'])
            ->name('api.public.admissions.store');
    });
