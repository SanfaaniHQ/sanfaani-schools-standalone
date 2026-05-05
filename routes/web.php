<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\LeadRequestController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentGatewaySettingController;
use App\Http\Controllers\Admin\PlatformSettingController;
use App\Http\Controllers\Admin\ResultAccessPolicyController;
use App\Http\Controllers\Admin\ResultSystemController as AdminResultSystemController;
use App\Http\Controllers\Admin\ScratchCardRequestController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SchoolFeatureOverrideController;
use App\Http\Controllers\Admin\SchoolPublicPageController as AdminSchoolPublicPageController;
use App\Http\Controllers\Admin\SchoolSubscriptionController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SuperAdminDashboardController;
use App\Http\Controllers\Admin\SystemMaintenanceController;
use App\Http\Controllers\Admin\SystemUpdateController;
use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\ChooseWorkspaceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\LandingPageController;
use App\Http\Controllers\Public\ResultCheckerController;
use App\Http\Controllers\Public\ResultCheckerPaymentController;
use App\Http\Controllers\Public\SchoolPublicPageController as PublicSchoolPublicPageController;
use App\Http\Controllers\Public\ResultVerificationController;
use App\Http\Controllers\School\AcademicSessionController;
use App\Http\Controllers\School\AdmissionNumberSettingController;
use App\Http\Controllers\School\ClassUploadController;
use App\Http\Controllers\School\GradingScaleController;
use App\Http\Controllers\School\ManualResultController;
use App\Http\Controllers\School\ResultAccessPolicyController as SchoolResultAccessPolicyController;
use App\Http\Controllers\School\ResultPublishingController;
use App\Http\Controllers\School\ResultSystemController as SchoolResultSystemController;
use App\Http\Controllers\School\ResultUploadController;
use App\Http\Controllers\School\ReportCardSettingController;
use App\Http\Controllers\School\SchoolAdminDashboardController;
use App\Http\Controllers\School\SchoolClassController;
use App\Http\Controllers\School\SchoolPublicPageController as SchoolSchoolPublicPageController;
use App\Http\Controllers\School\SchoolProfileController;
use App\Http\Controllers\School\ScratchCardController;
use App\Http\Controllers\School\StaffUserController;
use App\Http\Controllers\School\StudentBulkUploadController;
use App\Http\Controllers\School\StudentController;
use App\Http\Controllers\School\StudentElectiveSubjectController;
use App\Http\Controllers\School\StudentPromotionController;
use App\Http\Controllers\School\SubjectAssignmentController;
use App\Http\Controllers\School\SubscriptionController as SchoolPlanController;
use App\Http\Controllers\School\SubjectController;
use App\Http\Controllers\School\SubjectUploadController;
use App\Http\Controllers\School\TeacherAssignmentController;
use App\Http\Controllers\School\TeacherResultEntryController;
use App\Http\Controllers\School\TeacherResultReviewController;
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

Route::get('/admin/login', [AdminAuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('admin.login');

Route::post('/admin/login', [AdminAuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('admin.login.store');

Route::view('/privacy-policy', 'public.legal.privacy')
    ->name('legal.privacy');

Route::view('/terms', 'public.legal.terms')
    ->name('legal.terms');

Route::get('/result-checker', [ResultCheckerController::class, 'index'])
    ->name('public.results.index');

Route::post('/result-checker/identify', [ResultCheckerController::class, 'identify'])
    ->middleware('throttle:10,1')
    ->name('public.results.identify');

Route::post('/result-checker/check', [ResultCheckerController::class, 'check'])
    ->middleware('throttle:10,1')
    ->name('public.results.check');

Route::get('/result-checker/view/{token}', [ResultCheckerController::class, 'view'])
    ->name('public.results.view');

Route::get('/result-checker/view/{token}/print', [ResultCheckerController::class, 'print'])
    ->name('public.results.print');

Route::post('/result-checker/payment/initiate', [ResultCheckerPaymentController::class, 'initiate'])
    ->middleware('throttle:10,1')
    ->name('public.results.payment.initiate');

Route::get('/result-checker/payment/callback/{gateway?}', [ResultCheckerPaymentController::class, 'callback'])
    ->name('public.results.payment.callback');

Route::get('/verify-result/{verificationCode}', [ResultVerificationController::class, 'show'])
    ->name('public.results.verify');

Route::get('/s/{slug}', [PublicSchoolPublicPageController::class, 'show'])
    ->name('public.school-page.show');

Route::get('/s/{school:slug}/result-checker', [ResultCheckerController::class, 'index'])
    ->name('public.school.results.index');

Route::post('/s/{school:slug}/result-checker/identify', [ResultCheckerController::class, 'identify'])
    ->middleware('throttle:10,1')
    ->name('public.school.results.identify');

Route::post('/s/{school:slug}/result-checker/check', [ResultCheckerController::class, 'check'])
    ->middleware('throttle:10,1')
    ->name('public.school.results.check');

Route::post('/s/{school:slug}/payment/initiate', [ResultCheckerPaymentController::class, 'initiateForSchool'])
    ->middleware('throttle:10,1')
    ->name('public.school.payment.initiate');

Route::get('/s/{school:slug}/payment/callback/{gateway?}', [ResultCheckerPaymentController::class, 'callbackForSchool'])
    ->name('public.school.payment.callback');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/choose-workspace', [ChooseWorkspaceController::class, 'create'])
        ->name('workspace.create');

    Route::post('/choose-workspace', [ChooseWorkspaceController::class, 'store'])
        ->name('workspace.store');
});

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

        Route::post('/schools/{school}/support-access/start', [SchoolController::class, 'startSupportAccess'])
            ->name('schools.support-access.start');

        Route::get('/schools/{school}/public-page', [AdminSchoolPublicPageController::class, 'edit'])
            ->name('schools.public-page.edit');

        Route::patch('/schools/{school}/public-page', [AdminSchoolPublicPageController::class, 'update'])
            ->name('schools.public-page.update');

        Route::post('/support-access/stop', [SchoolController::class, 'stopSupportAccess'])
            ->name('support-access.stop');

        Route::post('/support-access/continue', [SchoolController::class, 'continueSupportAccess'])
            ->name('support-access.continue');

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
            ->except(['destroy']);

        Route::get('/result-system', [AdminResultSystemController::class, 'index'])
            ->name('result-system.index');

        Route::resource('lead-requests', LeadRequestController::class)
            ->only(['index', 'show', 'update']);

        Route::get('/system-updates', [SystemUpdateController::class, 'index'])
            ->name('system-updates.index');

        Route::post('/system-updates/upload', [SystemUpdateController::class, 'upload'])
            ->name('system-updates.upload');

        Route::get('/system-maintenance', [SystemMaintenanceController::class, 'index'])
            ->name('system-maintenance.index');

        Route::post('/system-maintenance/clear-all-cache', [SystemMaintenanceController::class, 'clearAllCache'])
            ->name('system-maintenance.clear-all-cache');

        Route::post('/system-maintenance/clear-config-cache', [SystemMaintenanceController::class, 'clearConfigCache'])
            ->name('system-maintenance.clear-config-cache');

        Route::post('/system-maintenance/clear-route-cache', [SystemMaintenanceController::class, 'clearRouteCache'])
            ->name('system-maintenance.clear-route-cache');

        Route::post('/system-maintenance/clear-view-cache', [SystemMaintenanceController::class, 'clearViewCache'])
            ->name('system-maintenance.clear-view-cache');

        Route::post('/system-maintenance/clear-app-cache', [SystemMaintenanceController::class, 'clearAppCache'])
            ->name('system-maintenance.clear-app-cache');

        Route::post('/system-maintenance/optimize', [SystemMaintenanceController::class, 'optimize'])
            ->name('system-maintenance.optimize');

        Route::post('/system-maintenance/storage-link', [SystemMaintenanceController::class, 'storageLink'])
            ->name('system-maintenance.storage-link');

        Route::get('/payments', [PaymentController::class, 'index'])
            ->name('payments.index');

        Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])
            ->name('payments.confirm');

        Route::get('/payment-settings', [PaymentGatewaySettingController::class, 'index'])
            ->name('payment-settings.index');

        Route::patch('/payment-settings/{gateway}', [PaymentGatewaySettingController::class, 'update'])
            ->name('payment-settings.update');

        Route::get('/mail-settings', [MailSettingController::class, 'edit'])
            ->name('mail-settings.edit');

        Route::patch('/mail-settings', [MailSettingController::class, 'update'])
            ->name('mail-settings.update');

        Route::post('/mail-settings/test', [MailSettingController::class, 'test'])
            ->name('mail-settings.test');

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
        Route::middleware('role:school_admin|result_officer|teacher|super_admin')
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

                Route::middleware('role:school_admin|result_officer|super_admin')
                    ->group(function () {
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

                        Route::get('/result-system', [SchoolResultSystemController::class, 'index'])
                            ->name('result-system.index');

                        Route::get('/result-access-policy', [SchoolResultAccessPolicyController::class, 'show'])
                            ->name('result-access-policy.show');

                        Route::get('/report-card-settings/preview', [ReportCardSettingController::class, 'preview'])
                            ->name('report-card-settings.preview');
                    });

                Route::get('/teacher-results', [TeacherResultEntryController::class, 'index'])
                    ->name('teacher-results.index');

                Route::get('/teacher-results/create', [TeacherResultEntryController::class, 'create'])
                    ->name('teacher-results.create');

                Route::post('/teacher-results', [TeacherResultEntryController::class, 'store'])
                    ->name('teacher-results.store');

                Route::get('/teacher-results/{submission}', [TeacherResultEntryController::class, 'show'])
                    ->name('teacher-results.show');

                Route::get('/teacher-results/{submission}/edit', [TeacherResultEntryController::class, 'edit'])
                    ->name('teacher-results.edit');

                Route::patch('/teacher-results/{submission}', [TeacherResultEntryController::class, 'update'])
                    ->name('teacher-results.update');

                Route::post('/teacher-results/{submission}/submit', [TeacherResultEntryController::class, 'submit'])
                    ->name('teacher-results.submit');
            });

        Route::middleware('role:school_admin|super_admin')
            ->group(function () {
                Route::get('/teacher-assignments', [TeacherAssignmentController::class, 'index'])
                    ->name('teacher-assignments.index');

                Route::post('/teacher-assignments', [TeacherAssignmentController::class, 'store'])
                    ->name('teacher-assignments.store');

                Route::get('/teacher-assignments/create', [TeacherAssignmentController::class, 'create'])
                    ->name('teacher-assignments.create');

                Route::get('/teacher-assignments/{assignment}/edit', [TeacherAssignmentController::class, 'edit'])
                    ->name('teacher-assignments.edit');

                Route::patch('/teacher-assignments/{assignment}', [TeacherAssignmentController::class, 'update'])
                    ->name('teacher-assignments.update');

                Route::post('/teacher-assignments/{assignment}/archive', [TeacherAssignmentController::class, 'archive'])
                    ->name('teacher-assignments.archive');

                Route::post('/teacher-assignments/{assignment}/restore', [TeacherAssignmentController::class, 'restore'])
                    ->name('teacher-assignments.restore');

                Route::get('/classes/upload', [ClassUploadController::class, 'index'])
                    ->name('classes.upload.index');

                Route::post('/classes/upload', [ClassUploadController::class, 'store'])
                    ->name('classes.upload.store');

                Route::get('/classes/upload/template', [ClassUploadController::class, 'downloadTemplate'])
                    ->name('classes.upload.template');

                Route::resource('classes', SchoolClassController::class)
                    ->parameters(['classes' => 'class'])
                    ->except(['show']);

                Route::post('/classes/{class}/restore', [SchoolClassController::class, 'restore'])
                    ->name('classes.restore');

                Route::get('/subjects/upload', [SubjectUploadController::class, 'index'])
                    ->name('subjects.upload.index');

                Route::post('/subjects/upload', [SubjectUploadController::class, 'store'])
                    ->name('subjects.upload.store');

                Route::get('/subjects/upload/template', [SubjectUploadController::class, 'downloadTemplate'])
                    ->name('subjects.upload.template');

                Route::resource('subjects', SubjectController::class)
                    ->except(['show']);

                Route::post('/subjects/{subject}/restore', [SubjectController::class, 'restore'])
                    ->name('subjects.restore');

                Route::get('/subject-assignments', [SubjectAssignmentController::class, 'index'])
                    ->name('subject-assignments.index');

                Route::post('/subject-assignments', [SubjectAssignmentController::class, 'store'])
                    ->name('subject-assignments.store');

                Route::get('/subject-assignments/create', [SubjectAssignmentController::class, 'create'])
                    ->name('subject-assignments.create');

                Route::get('/subject-assignments/{assignment}/edit', [SubjectAssignmentController::class, 'edit'])
                    ->name('subject-assignments.edit');

                Route::patch('/subject-assignments/{assignment}', [SubjectAssignmentController::class, 'update'])
                    ->name('subject-assignments.update');

                Route::post('/subject-assignments/{assignment}/archive', [SubjectAssignmentController::class, 'archive'])
                    ->name('subject-assignments.archive');

                Route::post('/subject-assignments/{assignment}/restore', [SubjectAssignmentController::class, 'restore'])
                    ->name('subject-assignments.restore');

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

                Route::get('/public-page', [SchoolSchoolPublicPageController::class, 'edit'])
                    ->name('public-page.edit');

                Route::patch('/public-page', [SchoolSchoolPublicPageController::class, 'update'])
                    ->name('public-page.update');

                Route::get('/subscription', [SchoolPlanController::class, 'show'])
                    ->name('subscription.show');

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

                Route::post('/students/{student}/elective-subjects', [StudentElectiveSubjectController::class, 'store'])
                    ->name('students.elective-subjects.store');

                Route::delete('/students/{student}/elective-subjects/{electiveSubject}', [StudentElectiveSubjectController::class, 'destroy'])
                    ->name('students.elective-subjects.destroy');

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

        Route::middleware('role:school_admin|result_officer|super_admin')
            ->group(function () {
                Route::get('/result-reviews', [TeacherResultReviewController::class, 'index'])
                    ->name('result-reviews.index');

                Route::get('/result-reviews/{submission}', [TeacherResultReviewController::class, 'show'])
                    ->name('result-reviews.show');

                Route::patch('/result-reviews/{submission}', [TeacherResultReviewController::class, 'update'])
                    ->name('result-reviews.update');

                Route::post('/result-reviews/{submission}/return', [TeacherResultReviewController::class, 'return'])
                    ->name('result-reviews.return');

                Route::post('/result-reviews/{submission}/approve', [TeacherResultReviewController::class, 'approve'])
                    ->name('result-reviews.approve');

                Route::post('/result-reviews/{submission}/publish', [TeacherResultReviewController::class, 'publish'])
                    ->name('result-reviews.publish');

                Route::post('/result-reviews/{submission}/void', [TeacherResultReviewController::class, 'voidSubmission'])
                    ->name('result-reviews.void');
            });

        Route::middleware('role:school_admin|result_officer|teacher|super_admin')
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
