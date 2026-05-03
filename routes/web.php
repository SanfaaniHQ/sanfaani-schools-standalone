<?php

use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlatformSettingController;
use App\Http\Controllers\Admin\ResultAccessPolicyController;
use App\Http\Controllers\Admin\ScratchCardRequestController;
use App\Http\Controllers\Admin\SchoolFeatureOverrideController;
use App\Http\Controllers\Admin\SchoolSubscriptionController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SuperAdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\LandingPageController;
use App\Http\Controllers\Public\ResultCheckerController;
use App\Http\Controllers\Public\ResultVerificationController;
use App\Http\Controllers\School\AcademicSessionController;
use App\Http\Controllers\School\AdmissionNumberSettingController;
use App\Http\Controllers\School\GradingScaleController;
use App\Http\Controllers\School\ManualResultController;
use App\Http\Controllers\School\ResultPublishingController;
use App\Http\Controllers\School\ResultUploadController;
use App\Http\Controllers\School\ReportCardSettingController;
use App\Http\Controllers\School\SchoolAdminDashboardController;
use App\Http\Controllers\School\SchoolClassController;
use App\Http\Controllers\School\SchoolProfileController;
use App\Http\Controllers\School\ScratchCardController;
use App\Http\Controllers\School\StaffUserController;
use App\Http\Controllers\School\StudentBulkUploadController;
use App\Http\Controllers\School\StudentController;
use App\Http\Controllers\School\StudentPromotionController;
use App\Http\Controllers\School\SubjectController;
use App\Http\Controllers\School\TermController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'home'])
    ->name('landing.home');

Route::get('/features', [LandingPageController::class, 'features'])
    ->name('landing.features');

Route::get('/pricing', [LandingPageController::class, 'pricing'])
    ->name('landing.pricing');

Route::get('/contact', [LandingPageController::class, 'contact'])
    ->name('landing.contact');

Route::post('/contact', [LandingPageController::class, 'submitContact'])
    ->middleware('throttle:5,1')
    ->name('landing.contact.submit');

Route::get('/demo', [LandingPageController::class, 'demo'])
    ->name('landing.demo');

Route::post('/demo', [LandingPageController::class, 'submitDemo'])
    ->middleware('throttle:5,1')
    ->name('landing.demo.submit');

Route::view('/privacy-policy', 'public.legal.privacy')
    ->name('legal.privacy');

Route::view('/terms', 'public.legal.terms')
    ->name('legal.terms');

Route::get('/result-checker', [ResultCheckerController::class, 'index'])
    ->name('public.results.index');

Route::post('/result-checker/check', [ResultCheckerController::class, 'check'])
    ->middleware('throttle:10,1')
    ->name('public.results.check');

Route::get('/result-checker/view/{token}', [ResultCheckerController::class, 'view'])
    ->name('public.results.view');

Route::get('/result-checker/view/{token}/print', [ResultCheckerController::class, 'print'])
    ->name('public.results.print');

Route::get('/verify-result/{verificationCode}', [ResultVerificationController::class, 'show'])
    ->name('public.results.verify');

Route::get('/s/{school:slug}/result-checker', [ResultCheckerController::class, 'index'])
    ->name('public.school.results.index');

Route::post('/s/{school:slug}/result-checker/check', [ResultCheckerController::class, 'check'])
    ->middleware('throttle:10,1')
    ->name('public.school.results.check');

Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('super_admin')) {
        return redirect()->route('admin.dashboard');
    }

    if (auth()->user()->hasAnyRole(['school_admin', 'result_officer'])) {
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

        Route::get('/platform-settings', [PlatformSettingController::class, 'edit'])
            ->name('platform-settings.edit');

        Route::patch('/platform-settings', [PlatformSettingController::class, 'update'])
            ->name('platform-settings.update');

        Route::resource('schools', SchoolController::class)
            ->except(['show', 'destroy']);

        Route::post('/schools/{school}/archive', [SchoolController::class, 'archive'])
            ->name('schools.archive');

        Route::post('/schools/{school}/restore', [SchoolController::class, 'restore'])
            ->name('schools.restore');

        Route::resource('subscription-plans', SubscriptionPlanController::class)
            ->except(['show', 'destroy']);

        Route::post('/subscription-plans/{subscriptionPlan}/archive', [SubscriptionPlanController::class, 'archive'])
            ->name('subscription-plans.archive');

        Route::post('/subscription-plans/{subscriptionPlan}/activate', [SubscriptionPlanController::class, 'activate'])
            ->name('subscription-plans.activate');

        Route::resource('school-subscriptions', SchoolSubscriptionController::class)
            ->only(['index', 'create', 'store']);

        Route::get('/feature-overrides', [SchoolFeatureOverrideController::class, 'index'])
            ->name('feature-overrides.index');

        Route::post('/feature-overrides', [SchoolFeatureOverrideController::class, 'store'])
            ->name('feature-overrides.store');

        Route::resource('result-access-policies', ResultAccessPolicyController::class)
            ->except(['show', 'destroy']);

        Route::get('/payments', [PaymentController::class, 'index'])
            ->name('payments.index');

        Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])
            ->name('payments.confirm');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index');

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

Route::middleware(['auth'])
    ->prefix('school')
    ->name('school.')
    ->group(function () {
        Route::middleware('role:school_admin|result_officer')
            ->group(function () {
                Route::get('/dashboard', [SchoolAdminDashboardController::class, 'index'])
                    ->name('dashboard');

                Route::middleware('role:school_admin')
                    ->group(function () {
                        Route::get('/students/upload', [StudentBulkUploadController::class, 'index'])
                            ->name('students.upload.index');

                        Route::post('/students/upload', [StudentBulkUploadController::class, 'store'])
                            ->name('students.upload.store');

                        Route::get('/students/upload/template', [StudentBulkUploadController::class, 'downloadTemplate'])
                            ->name('students.upload.template');
                    });

                Route::resource('students', StudentController::class)
                    ->only(['index']);

                Route::resource('grading-scales', GradingScaleController::class)
                    ->parameters(['grading-scales' => 'gradingScale'])
                    ->only(['index']);

                Route::prefix('results')
                    ->name('results.')
                    ->group(function () {
                        Route::resource('manual', ManualResultController::class)
                            ->parameters(['manual' => 'studentResult'])
                            ->except(['show', 'destroy']);

                        Route::get('/publishing', [ResultPublishingController::class, 'index'])
                            ->name('publishing.index');

                        Route::get('/upload', [ResultUploadController::class, 'index'])
                            ->name('upload.index');

                        Route::post('/upload', [ResultUploadController::class, 'store'])
                            ->name('upload.store');

                        Route::get('/upload/template', [ResultUploadController::class, 'downloadTemplate'])
                            ->name('upload.template');
                    });

                Route::get('/report-card-settings/preview', [ReportCardSettingController::class, 'preview'])
                    ->name('report-card-settings.preview');
            });

        Route::middleware('role:school_admin')
            ->group(function () {
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

                Route::get('/admission-number-settings', [AdmissionNumberSettingController::class, 'edit'])
                    ->name('admission-number-settings.edit');

                Route::put('/admission-number-settings', [AdmissionNumberSettingController::class, 'update'])
                    ->name('admission-number-settings.update');

                Route::resource('staff', StaffUserController::class)
                    ->parameters(['staff' => 'staff'])
                    ->except(['show', 'destroy']);

                Route::resource('students', StudentController::class)
                    ->except(['index', 'show', 'destroy']);

                Route::get('/profile', [SchoolProfileController::class, 'edit'])
                    ->name('profile.edit');

                Route::patch('/profile', [SchoolProfileController::class, 'update'])
                    ->name('profile.update');

                Route::get('/student-promotions', [StudentPromotionController::class, 'index'])
                    ->name('student-promotions.index');

                Route::get('/student-promotions/create', [StudentPromotionController::class, 'create'])
                    ->name('student-promotions.create');

                Route::post('/student-promotions/preview', [StudentPromotionController::class, 'preview'])
                    ->name('student-promotions.preview');

                Route::post('/student-promotions', [StudentPromotionController::class, 'store'])
                    ->name('student-promotions.store');

                Route::get('/student-promotions/history', [StudentPromotionController::class, 'history'])
                    ->name('student-promotions.history');

                Route::get('/report-card-settings', [ReportCardSettingController::class, 'edit'])
                    ->name('report-card-settings.edit');

                Route::patch('/report-card-settings', [ReportCardSettingController::class, 'update'])
                    ->name('report-card-settings.update');

                Route::delete('/students/{student}', [StudentController::class, 'destroy'])
                    ->name('students.destroy');

                Route::post('/students/{student}/restore', [StudentController::class, 'restore'])
                    ->name('students.restore');

                Route::resource('grading-scales', GradingScaleController::class)
                    ->parameters(['grading-scales' => 'gradingScale'])
                    ->except(['index', 'show', 'destroy']);

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
                        Route::post('/publishing/publish', [ResultPublishingController::class, 'publish'])
                            ->name('publishing.publish');

                        Route::post('/publishing/unpublish', [ResultPublishingController::class, 'unpublish'])
                            ->name('publishing.unpublish');

                        Route::delete('/manual/{studentResult}', [ManualResultController::class, 'destroy'])
                            ->name('manual.destroy');
                    });
            });

        Route::middleware('role:school_admin|result_officer')
            ->get('/students/{student}', [StudentController::class, 'show'])
            ->name('students.show');
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
