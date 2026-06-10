<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BrandingController as AdminBrandingController;
use App\Http\Controllers\Admin\CommunicationController as AdminCommunicationController;
use App\Http\Controllers\Admin\DemoSessionController as AdminDemoSessionController;
use App\Http\Controllers\Admin\DeploymentPlaceholderController;
use App\Http\Controllers\Admin\LeadRequestController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\MarketingAutomationController;
use App\Http\Controllers\Admin\MarketingCampaignController;
use App\Http\Controllers\Admin\MarketingDashboardController;
use App\Http\Controllers\Admin\MarketingEmailTemplateController;
use App\Http\Controllers\Admin\SalesTaskController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentGatewaySettingController;
use App\Http\Controllers\Admin\PerformanceController;
use App\Http\Controllers\Admin\OnboardingProgressController as AdminOnboardingProgressController;
use App\Http\Controllers\Admin\PlatformSettingController;
use App\Http\Controllers\Admin\PlatformMailSystemController;
use App\Http\Controllers\Admin\ResultAccessPolicyController;
use App\Http\Controllers\Admin\ResultSystemController as AdminResultSystemController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SchoolAdminUserController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SchoolFeatureOverrideController;
use App\Http\Controllers\Admin\SchoolPublicPageController as AdminSchoolPublicPageController;
use App\Http\Controllers\Admin\SchoolSubscriptionController;
use App\Http\Controllers\Admin\ScratchCardRequestController;
use App\Http\Controllers\Admin\SecurityDiagnosticsController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SuperAdminDashboardController;
use App\Http\Controllers\Admin\StandaloneStatusController;
use App\Http\Controllers\Admin\SupportThreadController as AdminSupportThreadController;
use App\Http\Controllers\Admin\SystemMaintenanceController;
use App\Http\Controllers\Admin\SystemStatusController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\ChooseWorkspaceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Demo\MarketplaceLiveDemoController;
use App\Http\Controllers\Demo\DemoRequestController;
use App\Http\Controllers\MarketingTrackingController;
use App\Http\Controllers\MarketingUnsubscribeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Public\LandingPageController;
use App\Http\Controllers\Public\CbtAccessController as PublicCbtAccessController;
use App\Http\Controllers\Public\ResultCheckerController;
use App\Http\Controllers\Public\ResultCheckerPaymentController;
use App\Http\Controllers\Public\ResultVerificationController;
use App\Http\Controllers\Public\SchoolPublicPageController as PublicSchoolPublicPageController;
use App\Http\Controllers\PublicResultController;
use App\Http\Controllers\School\AcademicSessionController;
use App\Http\Controllers\School\AdmissionNumberSettingController;
use App\Http\Controllers\School\AuditLogController as SchoolAuditLogController;
use App\Http\Controllers\School\BrandingController as SchoolBrandingController;
use App\Http\Controllers\School\CbtDashboardController;
use App\Http\Controllers\School\CbtExamController;
use App\Http\Controllers\School\CbtMarkingController;
use App\Http\Controllers\School\CbtQuestionBankController;
use App\Http\Controllers\School\CbtQuestionController;
use App\Http\Controllers\School\ClassUploadController;
use App\Http\Controllers\School\CommunicationController as SchoolCommunicationController;
use App\Http\Controllers\School\GradingScaleController;
use App\Http\Controllers\School\MailSettingController as SchoolMailSettingController;
use App\Http\Controllers\School\ManualResultController;
use App\Http\Controllers\School\ReportCardSettingController;
use App\Http\Controllers\School\ResultAccessPolicyController as SchoolResultAccessPolicyController;
use App\Http\Controllers\School\ResultPublishingController;
use App\Http\Controllers\School\ResultSystemController as SchoolResultSystemController;
use App\Http\Controllers\School\ResultUploadController;
use App\Http\Controllers\School\RoleFeatureSettingController;
use App\Http\Controllers\School\SchoolAdminDashboardController;
use App\Http\Controllers\School\SchoolClassController;
use App\Http\Controllers\School\SchoolProfileController;
use App\Http\Controllers\School\SchoolPublicPageController as SchoolSchoolPublicPageController;
use App\Http\Controllers\School\ScratchCardController;
use App\Http\Controllers\School\StaffUserController;
use App\Http\Controllers\School\StudentBulkUploadController;
use App\Http\Controllers\School\StudentController;
use App\Http\Controllers\School\StudentElectiveSubjectController;
use App\Http\Controllers\School\StudentPromotionController;
use App\Http\Controllers\School\StudentResultWorkspaceController;
use App\Http\Controllers\School\SubjectAssignmentController;
use App\Http\Controllers\School\SubjectController;
use App\Http\Controllers\School\SubjectUploadController;
use App\Http\Controllers\School\SubscriptionController as SchoolPlanController;
use App\Http\Controllers\School\SupportThreadController as SchoolSupportThreadController;
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

Route::get('/demo', [DemoRequestController::class, 'create'])
    ->middleware('feature:demo_system')
    ->name('landing.demo');

Route::get('/demo/thank-you', [DemoRequestController::class, 'thankYou'])
    ->middleware('feature:demo_system')
    ->name('demo.thank-you');

Route::post('/demo', [DemoRequestController::class, 'store'])
    ->middleware(['feature:demo_system', 'throttle:5,1'])
    ->name('landing.demo.submit');

Route::post('/demo/request', [DemoRequestController::class, 'store'])
    ->middleware(['feature:demo_system', 'throttle:5,1'])
    ->name('demo.request.store');

Route::get('/demo/live', [MarketplaceLiveDemoController::class, 'index'])
    ->middleware('feature:demo_system')
    ->name('demo.live');

Route::post('/demo/live/login/{role}', [MarketplaceLiveDemoController::class, 'login'])
    ->middleware(['feature:demo_system', 'throttle:10,1'])
    ->name('demo.live.login');

Route::get('/admin/login', [AdminAuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('admin.login');

Route::post('/admin/login', [AdminAuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('admin.login.store');

Route::get('/admin/forgot-password', [PasswordResetLinkController::class, 'adminCreate'])
    ->middleware('guest')
    ->name('admin.password.request');

Route::post('/admin/forgot-password', [PasswordResetLinkController::class, 'adminStore'])
    ->middleware(['guest', 'throttle:6,1'])
    ->name('admin.password.email');

Route::get('/admin/reset-password/{token}', [NewPasswordController::class, 'adminCreate'])
    ->middleware('guest')
    ->name('admin.password.reset');

Route::post('/admin/reset-password', [NewPasswordController::class, 'adminStore'])
    ->middleware(['guest', 'throttle:6,1'])
    ->name('admin.password.store');

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

Route::get('/results/{slug}', [PublicResultController::class, 'showForm'])
    ->middleware('throttle:10,1')
    ->name('public.results.slug.index');

Route::post('/results/{slug}/identify', [PublicResultController::class, 'identify'])
    ->middleware('throttle:10,1')
    ->name('public.results.slug.identify');

Route::post('/results/{slug}/check', [PublicResultController::class, 'check'])
    ->middleware('throttle:10,1')
    ->name('public.results.slug.check');

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

Route::get('/schools/{slug}', [PublicSchoolPublicPageController::class, 'show'])
    ->name('public.schools.show');

Route::get('/schools/{slug}/admissions', [PublicSchoolPublicPageController::class, 'admissions'])
    ->name('public.schools.admissions');

Route::get('/schools/{slug}/contact', [PublicSchoolPublicPageController::class, 'contact'])
    ->name('public.schools.contact');

Route::get('/schools/{slug}/results', [PublicSchoolPublicPageController::class, 'results'])
    ->name('public.schools.results.index');

Route::post('/schools/{slug}/results/identify', [PublicSchoolPublicPageController::class, 'identifyResult'])
    ->middleware('throttle:10,1')
    ->name('public.schools.results.identify');

Route::post('/schools/{slug}/results/check', [PublicSchoolPublicPageController::class, 'checkResult'])
    ->middleware('throttle:10,1')
    ->name('public.schools.results.check');

Route::get('/s/{slug}', [PublicSchoolPublicPageController::class, 'show'])
    ->name('public.school-page.show');

Route::get('/cbt/attempts/{attempt:attempt_uuid}', [PublicCbtAccessController::class, 'take'])
    ->middleware('throttle:60,1')
    ->name('public.cbt.take');

Route::post('/cbt/attempts/{attempt:attempt_uuid}/save', [PublicCbtAccessController::class, 'save'])
    ->middleware('throttle:120,1')
    ->name('public.cbt.save');

Route::post('/cbt/attempts/{attempt:attempt_uuid}/submit', [PublicCbtAccessController::class, 'submit'])
    ->middleware('throttle:30,1')
    ->name('public.cbt.submit');

Route::get('/cbt/attempts/{attempt:attempt_uuid}/result', [PublicCbtAccessController::class, 'result'])
    ->middleware('throttle:30,1')
    ->name('public.cbt.result');

Route::get('/cbt/attempts/{attempt:attempt_uuid}/snapshot', [PublicCbtAccessController::class, 'snapshot'])
    ->middleware('throttle:12,1')
    ->name('public.cbt.snapshot');

Route::get('/cbt/{school:slug}/{exam}', [PublicCbtAccessController::class, 'entry'])
    ->middleware('throttle:30,1')
    ->name('public.cbt.entry');

Route::post('/cbt/{school:slug}/{exam}/access', [PublicCbtAccessController::class, 'access'])
    ->middleware('throttle:12,1')
    ->name('public.cbt.access');

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

Route::get('/m/open/t/{token}', [MarketingTrackingController::class, 'openToken'])
    ->middleware('signed')
    ->name('marketing.track.open');

Route::get('/m/click/t/{token}', [MarketingTrackingController::class, 'clickToken'])
    ->middleware('signed')
    ->name('marketing.track.click');

Route::get('/m/unsubscribe/t/{token}', [MarketingTrackingController::class, 'unsubscribeToken'])
    ->middleware('signed')
    ->name('marketing.unsubscribe');

Route::get('/m/open/{recipient}', [MarketingTrackingController::class, 'open'])
    ->middleware('signed')
    ->name('marketing.track.open.legacy');

Route::get('/m/click/{recipient}', [MarketingTrackingController::class, 'click'])
    ->middleware('signed')
    ->name('marketing.track.click.legacy');

Route::get('/m/unsubscribe/{recipient}', [MarketingTrackingController::class, 'unsubscribe'])
    ->middleware('signed')
    ->name('marketing.unsubscribe.legacy');

Route::get('/unsubscribe/{token}', MarketingUnsubscribeController::class)
    ->name('marketing.unsubscribe.public');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'demo.safe'])->group(function () {
    Route::get('/choose-workspace', [ChooseWorkspaceController::class, 'create'])
        ->name('workspace.create');

    Route::post('/choose-workspace', [ChooseWorkspaceController::class, 'store'])
        ->name('workspace.store');

    Route::middleware(['feature:guided_onboarding', 'role:super_admin|school_admin|teacher|parent|student|result_officer|accountant'])
        ->prefix('onboarding')
        ->name('onboarding.')
        ->group(function () {
            Route::get('/', [OnboardingController::class, 'index'])
                ->name('index');
            Route::post('/steps/{onboardingStep}/complete', [OnboardingController::class, 'complete'])
                ->name('steps.complete');
            Route::post('/steps/{onboardingStep}/skip', [OnboardingController::class, 'skip'])
                ->name('steps.skip');
        });
});

Route::middleware(['auth', 'role:super_admin', 'demo.safe'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/standalone/status', StandaloneStatusController::class)
            ->name('standalone.status')
            ->middleware('deployment.behavior:standalone_status');

        Route::get('/platform-settings', [PlatformSettingController::class, 'edit'])
            ->name('platform-settings.edit')
            ->middleware('deployment.behavior:platform_settings|local_school_settings');

        Route::patch('/platform-settings', [PlatformSettingController::class, 'update'])
            ->name('platform-settings.update')
            ->middleware('deployment.behavior:platform_settings|local_school_settings');

        Route::resource('schools', SchoolController::class)
            ->except(['show', 'destroy'])
            ->middleware('deployment.behavior:platform_schools');

        Route::post('/schools/{school}/support-access/start', [SchoolController::class, 'startSupportAccess'])
            ->name('schools.support-access.start')
            ->middleware('deployment.behavior:platform_schools|platform_support');

        Route::get('/schools/{school}/public-page', [AdminSchoolPublicPageController::class, 'edit'])
            ->name('schools.public-page.edit')
            ->middleware('deployment.behavior:platform_schools');

        Route::patch('/schools/{school}/public-page', [AdminSchoolPublicPageController::class, 'update'])
            ->name('schools.public-page.update')
            ->middleware('deployment.behavior:platform_schools');

        Route::post('/support-access/stop', [SchoolController::class, 'stopSupportAccess'])
            ->name('support-access.stop');

        Route::post('/revoke-support-session', [SchoolController::class, 'revokeSupportSession'])
            ->name('support-access.revoke');

        Route::post('/support-access/continue', [SchoolController::class, 'continueSupportAccess'])
            ->name('support-access.continue');

        Route::post('/schools/{school}/archive', [SchoolController::class, 'archive'])
            ->name('schools.archive')
            ->middleware('deployment.behavior:platform_schools');

        Route::post('/schools/{school}/restore', [SchoolController::class, 'restore'])
            ->name('schools.restore')
            ->middleware('deployment.behavior:platform_schools');

        // School Admin User Management
        Route::get('/schools/{school}/admins', [SchoolAdminUserController::class, 'index'])
            ->name('schools.admins.index')
            ->middleware('deployment.behavior:platform_schools');

        Route::get('/schools/{school}/admins/create', [SchoolAdminUserController::class, 'create'])
            ->name('schools.admins.create')
            ->middleware('deployment.behavior:platform_schools');

        Route::post('/schools/{school}/admins', [SchoolAdminUserController::class, 'store'])
            ->name('schools.admins.store')
            ->middleware('deployment.behavior:platform_schools');

        Route::post('/schools/{school}/admins/{admin}/reset-password', [SchoolAdminUserController::class, 'resetPassword'])
            ->name('schools.admins.reset-password')
            ->middleware('deployment.behavior:platform_schools');

        Route::post('/schools/{school}/admins/{admin}/send-reset-link', [SchoolAdminUserController::class, 'sendResetLink'])
            ->name('schools.admins.send-reset-link')
            ->middleware('deployment.behavior:platform_schools');

        Route::post('/schools/{school}/admins/{admin}/disable', [SchoolAdminUserController::class, 'disable'])
            ->name('schools.admins.disable')
            ->middleware('deployment.behavior:platform_schools');

        Route::post('/schools/{school}/admins/{admin}/enable', [SchoolAdminUserController::class, 'enable'])
            ->name('schools.admins.enable')
            ->middleware('deployment.behavior:platform_schools');

        Route::resource('subscription-plans', SubscriptionPlanController::class)
            ->except(['show', 'destroy'])
            ->middleware('deployment.behavior:platform_subscriptions');

        Route::post('/subscription-plans/{subscriptionPlan}/archive', [SubscriptionPlanController::class, 'archive'])
            ->name('subscription-plans.archive')
            ->middleware('deployment.behavior:platform_subscriptions');

        Route::post('/subscription-plans/{subscriptionPlan}/activate', [SubscriptionPlanController::class, 'activate'])
            ->name('subscription-plans.activate')
            ->middleware('deployment.behavior:platform_subscriptions');

        Route::resource('school-subscriptions', SchoolSubscriptionController::class)
            ->only(['index', 'create', 'store'])
            ->middleware('deployment.behavior:platform_subscriptions');

        Route::get('/feature-overrides', [SchoolFeatureOverrideController::class, 'index'])
            ->name('feature-overrides.index')
            ->middleware('deployment.behavior:platform_features');

        Route::post('/feature-overrides', [SchoolFeatureOverrideController::class, 'store'])
            ->name('feature-overrides.store')
            ->middleware('deployment.behavior:platform_features');

        Route::resource('result-access-policies', ResultAccessPolicyController::class)
            ->except(['destroy'])
            ->middleware('deployment.behavior:platform_result_system');

        Route::get('/result-system', [AdminResultSystemController::class, 'index'])
            ->name('result-system.index')
            ->middleware('deployment.behavior:platform_result_system');

        Route::get('/roles-permissions', [RolePermissionController::class, 'index'])
            ->name('roles-permissions.index')
            ->middleware('deployment.behavior:platform_security');

        Route::get('/onboarding/progress', [AdminOnboardingProgressController::class, 'index'])
            ->name('onboarding.progress')
            ->middleware(['feature:guided_onboarding', 'deployment.behavior:guided_onboarding']);

        Route::resource('lead-requests', LeadRequestController::class)
            ->only(['index', 'show', 'update'])
            ->middleware('deployment.behavior:platform_onboarding');
        Route::post('/lead-requests/{leadRequest}/notes', [LeadRequestController::class, 'storeNote'])
            ->name('lead-requests.notes.store')
            ->middleware('deployment.behavior:platform_onboarding');
        Route::post('/lead-requests/{leadRequest}/communications', [LeadRequestController::class, 'storeCommunication'])
            ->name('lead-requests.communications.store')
            ->middleware('deployment.behavior:platform_onboarding');
        Route::post('/lead-requests/{leadRequest}/convert', [LeadRequestController::class, 'convert'])
            ->name('lead-requests.convert')
            ->middleware('deployment.behavior:platform_onboarding');

        Route::prefix('marketing')
            ->name('marketing.')
            ->middleware(['feature:marketing_automation', 'deployment.behavior:platform_marketing'])
            ->group(function () {
                Route::get('/', [MarketingAutomationController::class, 'dashboard'])
                    ->name('index');
                Route::get('/leads', [MarketingAutomationController::class, 'leads'])
                    ->name('leads');
                Route::get('/activities', [MarketingAutomationController::class, 'activities'])
                    ->name('activities');
                Route::get('/sequences', [MarketingAutomationController::class, 'sequences'])
                    ->name('sequences');
            });

        Route::prefix('sales')
            ->name('sales.')
            ->middleware(['feature:marketing_automation', 'deployment.behavior:platform_marketing'])
            ->group(function () {
                Route::get('/tasks', [SalesTaskController::class, 'index'])
                    ->name('tasks.index');
                Route::post('/tasks/{salesTask}/complete', [SalesTaskController::class, 'complete'])
                    ->name('tasks.complete');
            });

        Route::prefix('demo')
            ->name('demo.')
            ->middleware(['feature:demo_system', 'deployment.behavior:demo_sessions'])
            ->group(function () {
                Route::get('/', [AdminDemoSessionController::class, 'index'])
                    ->name('index');
                Route::get('/{demoSession}', [AdminDemoSessionController::class, 'show'])
                    ->name('show');
                Route::post('/{demoSession}/expire', [AdminDemoSessionController::class, 'expire'])
                    ->name('expire');
                Route::post('/{demoSession}/credentials', [AdminDemoSessionController::class, 'credentials'])
                    ->name('credentials');
            });

        Route::prefix('email-marketing')
            ->name('email-marketing.')
            ->middleware('deployment.behavior:platform_marketing')
            ->group(function () {
                Route::get('/', MarketingDashboardController::class)
                    ->name('dashboard');

                Route::resource('campaigns', MarketingCampaignController::class)
                    ->except(['destroy']);
                Route::post('/campaigns/{campaign}/send', [MarketingCampaignController::class, 'send'])
                    ->name('campaigns.send');
                Route::post('/campaigns/{campaign}/duplicate', [MarketingCampaignController::class, 'duplicate'])
                    ->name('campaigns.duplicate');
                Route::post('/campaigns/{campaign}/pause', [MarketingCampaignController::class, 'pause'])
                    ->name('campaigns.pause');
                Route::post('/campaigns/{campaign}/resume', [MarketingCampaignController::class, 'resume'])
                    ->name('campaigns.resume');
                Route::post('/campaigns/{campaign}/archive', [MarketingCampaignController::class, 'archive'])
                    ->name('campaigns.archive');

                Route::resource('templates', MarketingEmailTemplateController::class)
                    ->except(['show', 'destroy']);

                Route::get('/automations', [MarketingAutomationController::class, 'index'])
                    ->name('automations.index');
                Route::post('/automations', [MarketingAutomationController::class, 'store'])
                    ->name('automations.store');
                Route::patch('/automations/{automation}', [MarketingAutomationController::class, 'update'])
                    ->name('automations.update');
                Route::post('/automations/run', [MarketingAutomationController::class, 'run'])
                    ->name('automations.run');
            });

        Route::get('/system-maintenance', [SystemMaintenanceController::class, 'index'])
            ->name('system-maintenance.index')
            ->middleware('deployment.behavior:system_maintenance');

        Route::get('/system/status', SystemStatusController::class)
            ->name('system.status')
            ->middleware('deployment.behavior:system_status');

        Route::prefix('updates')
            ->name('updates.')
            ->middleware(['feature:update_manager', 'deployment.behavior:platform_updates|standalone_updates|managed_updates', 'license.valid'])
            ->group(function () {
                Route::get('/', [UpdateController::class, 'index'])
                    ->name('index');
                Route::get('/check', [UpdateController::class, 'check'])
                    ->name('check');
                Route::get('/upload', [UpdateController::class, 'upload'])
                    ->name('upload');
                Route::post('/upload', [UpdateController::class, 'store'])
                    ->name('store');
                Route::get('/{updatePackage}', [UpdateController::class, 'show'])
                    ->name('show');
                Route::post('/{updatePackage}/preflight', [UpdateController::class, 'preflight'])
                    ->name('preflight');
                Route::post('/{updatePackage}/mark-ready', [UpdateController::class, 'markReady'])
                    ->name('mark-ready');
            });

        Route::prefix('backups')
            ->name('backups.')
            ->middleware(['feature:backup_manager', 'deployment.behavior:platform_backups|standalone_backups|managed_backups', 'license.valid'])
            ->group(function () {
                Route::get('/', [BackupController::class, 'index'])
                    ->name('index');
                Route::get('/create', [BackupController::class, 'create'])
                    ->name('create');
                Route::post('/', [BackupController::class, 'store'])
                    ->name('store');
                Route::get('/{backup}', [BackupController::class, 'show'])
                    ->name('show');
                Route::post('/{backup}/verify', [BackupController::class, 'verify'])
                    ->name('verify');
                Route::get('/{backup}/restore-plan', [BackupController::class, 'restorePlan'])
                    ->name('restore-plan');
                Route::post('/prune', [BackupController::class, 'prune'])
                    ->name('prune');
            });

        Route::prefix('performance')
            ->name('performance.')
            ->middleware(['feature:performance_diagnostics', 'deployment.behavior:platform_performance|standalone_performance|managed_performance'])
            ->group(function () {
                Route::get('/', [PerformanceController::class, 'index'])
                    ->name('index');
                Route::get('/audit', [PerformanceController::class, 'audit'])
                    ->name('audit');
                Route::get('/shared-hosting', [PerformanceController::class, 'sharedHosting'])
                    ->name('shared-hosting');
                Route::get('/cache', [PerformanceController::class, 'cache'])
                    ->name('cache');
                Route::get('/queues', [PerformanceController::class, 'queues'])
                    ->name('queues');
                Route::get('/logs', [PerformanceController::class, 'logs'])
                    ->name('logs');
            });

        Route::prefix('branding')
            ->name('branding.')
            ->middleware(['feature:branding_manager', 'deployment.behavior:platform_branding|standalone_branding|managed_branding'])
            ->group(function () {
                Route::get('/', [AdminBrandingController::class, 'edit'])
                    ->name('edit');
                Route::patch('/', [AdminBrandingController::class, 'update'])
                    ->name('update');
                Route::post('/logo', [AdminBrandingController::class, 'logo'])
                    ->name('logo');
                Route::post('/favicon', [AdminBrandingController::class, 'favicon'])
                    ->name('favicon');
            });

        Route::get('/license', [LicenseController::class, 'index'])
            ->name('license.index')
            ->middleware(['feature:license_activation', 'deployment.behavior:standalone_license|license_activation']);

        Route::get('/license/activate', [LicenseController::class, 'activate'])
            ->name('license.activate')
            ->middleware(['feature:license_activation', 'deployment.behavior:standalone_license|license_activation']);

        Route::post('/license/activate', [LicenseController::class, 'store'])
            ->name('license.store')
            ->middleware(['feature:license_activation', 'deployment.behavior:standalone_license|license_activation']);

        Route::post('/license/validate', [LicenseController::class, 'validateNow'])
            ->name('license.validate')
            ->middleware(['feature:license_activation', 'deployment.behavior:standalone_license|license_activation']);

        Route::get('/deployment/{section}', [DeploymentPlaceholderController::class, 'show'])
            ->whereIn('section', [
                'standalone-installer',
                'standalone-license',
                'standalone-updates',
                'local-branding',
                'local-mail',
                'managed-support',
                'managed-backups',
                'managed-updates',
                'managed-white-label',
            ])
            ->middleware('deployment.behavior:standalone_installer|standalone_license|standalone_updates|local_branding|local_mail_settings|managed_support|managed_backups|managed_updates|managed_white_label')
            ->name('deployment.placeholder');

        Route::post('/system-maintenance/clear-all-cache', [SystemMaintenanceController::class, 'clearAllCache'])
            ->name('system-maintenance.clear-all-cache')
            ->middleware('deployment.behavior:system_maintenance');

        Route::post('/system-maintenance/clear-config-cache', [SystemMaintenanceController::class, 'clearConfigCache'])
            ->name('system-maintenance.clear-config-cache')
            ->middleware('deployment.behavior:system_maintenance');

        Route::post('/system-maintenance/clear-route-cache', [SystemMaintenanceController::class, 'clearRouteCache'])
            ->name('system-maintenance.clear-route-cache')
            ->middleware('deployment.behavior:system_maintenance');

        Route::post('/system-maintenance/clear-view-cache', [SystemMaintenanceController::class, 'clearViewCache'])
            ->name('system-maintenance.clear-view-cache')
            ->middleware('deployment.behavior:system_maintenance');

        Route::post('/system-maintenance/clear-app-cache', [SystemMaintenanceController::class, 'clearAppCache'])
            ->name('system-maintenance.clear-app-cache')
            ->middleware('deployment.behavior:system_maintenance');

        Route::post('/system-maintenance/optimize', [SystemMaintenanceController::class, 'optimize'])
            ->name('system-maintenance.optimize')
            ->middleware('deployment.behavior:system_maintenance');

        Route::post('/system-maintenance/storage-link', [SystemMaintenanceController::class, 'storageLink'])
            ->name('system-maintenance.storage-link')
            ->middleware('deployment.behavior:system_maintenance');

        Route::post('/system-maintenance/backups', [SystemMaintenanceController::class, 'createBackup'])
            ->name('system-maintenance.backups.create')
            ->middleware('deployment.behavior:system_maintenance');

        Route::post('/system-maintenance/backups/cleanup', [SystemMaintenanceController::class, 'cleanupBackups'])
            ->name('system-maintenance.backups.cleanup')
            ->middleware('deployment.behavior:system_maintenance');

        Route::get('/system-maintenance/backups/{fileName}', [SystemMaintenanceController::class, 'downloadBackup'])
            ->where('fileName', '[A-Za-z0-9._-]+')
            ->name('system-maintenance.backups.download')
            ->middleware('deployment.behavior:system_maintenance');

        Route::get('/payments', [PaymentController::class, 'index'])
            ->name('payments.index')
            ->middleware('deployment.behavior:platform_payments');

        Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])
            ->name('payments.confirm')
            ->middleware('deployment.behavior:platform_payments');

        Route::get('/payment-settings', [PaymentGatewaySettingController::class, 'index'])
            ->name('payment-settings.index')
            ->middleware('deployment.behavior:platform_payments');

        Route::patch('/payment-settings/{gateway}', [PaymentGatewaySettingController::class, 'update'])
            ->name('payment-settings.update')
            ->middleware('deployment.behavior:platform_payments');

        Route::get('/mail-settings', [MailSettingController::class, 'edit'])
            ->name('mail-settings.edit')
            ->middleware('deployment.behavior:platform_mail');

        Route::get('/platform-mail-system', [PlatformMailSystemController::class, 'index'])
            ->name('platform-mail-system.index')
            ->middleware('deployment.behavior:platform_mail');

        Route::patch('/mail-settings', [MailSettingController::class, 'update'])
            ->name('mail-settings.update')
            ->middleware('deployment.behavior:platform_mail');

        Route::post('/mail-settings/test', [MailSettingController::class, 'test'])
            ->name('mail-settings.test')
            ->middleware('deployment.behavior:platform_mail');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index')
            ->middleware('deployment.behavior:platform_audit');
        Route::get('/audit-logs/export', [AuditLogController::class, 'export'])
            ->name('audit-logs.export')
            ->middleware('deployment.behavior:platform_audit');

        Route::prefix('security')
            ->name('security.')
            ->middleware(['feature:security_diagnostics', 'deployment.behavior:platform_security_diagnostics|standalone_security|managed_security'])
            ->group(function () {
                Route::get('/', [SecurityDiagnosticsController::class, 'index'])
                    ->name('index');
                Route::get('/audit', [SecurityDiagnosticsController::class, 'audit'])
                    ->name('audit');
                Route::get('/email', [SecurityDiagnosticsController::class, 'email'])
                    ->name('email');
                Route::get('/logging', [SecurityDiagnosticsController::class, 'logging'])
                    ->name('logging');
                Route::get('/tokens', [SecurityDiagnosticsController::class, 'tokens'])
                    ->name('tokens');
                Route::get('/production', [SecurityDiagnosticsController::class, 'production'])
                    ->name('production');
            });

        Route::get('/communications', [AdminCommunicationController::class, 'index'])
            ->name('communications.index')
            ->middleware('deployment.behavior:platform_communications');
        Route::get('/communications/logs', [AdminCommunicationController::class, 'logs'])
            ->name('communications.logs')
            ->middleware('deployment.behavior:platform_communications');
        Route::post('/communications/send', [AdminCommunicationController::class, 'send'])
            ->name('communications.send')
            ->middleware('deployment.behavior:platform_communications');
        Route::post('/communications/{communicationLog}/resend', [AdminCommunicationController::class, 'resend'])
            ->name('communications.resend')
            ->middleware('deployment.behavior:platform_communications');
        Route::post('/communications/retry-failed', [AdminCommunicationController::class, 'retryFailed'])
            ->name('communications.retry-failed')
            ->middleware('deployment.behavior:platform_communications');
        Route::get('/communications/export', [AdminCommunicationController::class, 'export'])
            ->name('communications.export')
            ->middleware('deployment.behavior:platform_communications');

        Route::get('/support-threads', [AdminSupportThreadController::class, 'index'])
            ->name('support-threads.index')
            ->middleware('deployment.behavior:platform_support|managed_support');

        Route::get('/support-threads/{thread}', [AdminSupportThreadController::class, 'show'])
            ->name('support-threads.show')
            ->middleware('deployment.behavior:platform_support|managed_support');

        Route::get('/support-attachments/{attachment}', [AdminSupportThreadController::class, 'downloadAttachment'])
            ->name('support-attachments.download')
            ->middleware('deployment.behavior:platform_support|managed_support');

        Route::post('/support-threads/{thread}/reply', [AdminSupportThreadController::class, 'reply'])
            ->name('support-threads.reply')
            ->middleware('deployment.behavior:platform_support|managed_support');

        Route::patch('/support-threads/{thread}/status', [AdminSupportThreadController::class, 'status'])
            ->name('support-threads.status')
            ->middleware('deployment.behavior:platform_support|managed_support');

        Route::patch('/support-threads/{thread}/assign', [AdminSupportThreadController::class, 'assign'])
            ->name('support-threads.assign')
            ->middleware('deployment.behavior:platform_support|managed_support');

        Route::prefix('scratch-card-requests')
            ->name('scratch-card-requests.')
            ->middleware('deployment.behavior:platform_scratch_cards')
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
            ->name('scratch-cards.revoke')
            ->middleware('deployment.behavior:platform_scratch_cards');
    });

Route::middleware(['auth', 'school.context', 'demo.safe'])
    ->prefix('school')
    ->name('school.')
    ->group(function () {
        Route::middleware('role:school_admin|result_officer|teacher|super_admin')
            ->group(function () {
                Route::get('/dashboard', [SchoolAdminDashboardController::class, 'index'])
                    ->name('dashboard');

                Route::prefix('cbt')
                    ->name('cbt.')
                    ->middleware('feature.school:cbt.manage,cbt.question_bank,cbt.mark_theory,cbt.publish_results')
                    ->group(function () {
                        Route::get('/', [CbtDashboardController::class, 'index'])
                            ->name('dashboard');

                        Route::resource('question-banks', CbtQuestionBankController::class)
                            ->only(['index', 'create', 'store', 'show'])
                            ->middleware('feature.school:cbt.question_bank,cbt.manage');

                        Route::post('/question-banks/{questionBank}/import', [CbtQuestionBankController::class, 'import'])
                            ->middleware('feature.school:cbt.question_bank,cbt.manage')
                            ->name('question-banks.import');

                        Route::post('/question-banks/{questionBank}/questions', [CbtQuestionController::class, 'store'])
                            ->middleware('feature.school:cbt.question_bank,cbt.manage')
                            ->name('questions.store');

                        Route::resource('exams', CbtExamController::class)
                            ->only(['index', 'create', 'store', 'show'])
                            ->middleware('feature.school:cbt.manage,cbt.question_bank');

                        Route::post('/exams/{exam}/questions', [CbtExamController::class, 'attachQuestions'])
                            ->middleware('feature.school:cbt.manage')
                            ->name('exams.questions.attach');

                        Route::post('/exams/{exam}/open', [CbtExamController::class, 'open'])
                            ->middleware('feature.school:cbt.manage')
                            ->name('exams.open');

                        Route::post('/exams/{exam}/access-codes', [CbtExamController::class, 'generateCodes'])
                            ->middleware('feature.school:cbt.manage')
                            ->name('exams.access-codes.generate');

                        Route::post('/exams/{exam}/publish-results', [CbtExamController::class, 'publishResults'])
                            ->middleware('feature.school:cbt.publish_results,results.publish')
                            ->name('exams.publish-results');

                        Route::get('/marking', [CbtMarkingController::class, 'index'])
                            ->middleware('feature.school:cbt.mark_theory')
                            ->name('marking.index');

                        Route::get('/marking/{attempt}', [CbtMarkingController::class, 'show'])
                            ->middleware('feature.school:cbt.mark_theory')
                            ->name('marking.show');

                        Route::patch('/marking/answers/{answer}', [CbtMarkingController::class, 'update'])
                            ->middleware('feature.school:cbt.mark_theory')
                            ->name('marking.answers.update');
                    });

                Route::get('/students', [StudentController::class, 'index'])
                    ->middleware('feature.school:students.view,students.view_assigned')
                    ->name('students.index');

                Route::middleware('role:school_admin')
                    ->group(function () {
                        Route::get('/students/upload', [StudentBulkUploadController::class, 'index'])
                            ->name('students.upload.index');

                        Route::post('/students/upload', [StudentBulkUploadController::class, 'store'])
                            ->name('students.upload.store');

                        Route::get('/students/upload/template', [StudentBulkUploadController::class, 'downloadTemplate'])
                            ->name('students.upload.template');

                        Route::get('/mail-settings', [SchoolMailSettingController::class, 'edit'])
                            ->name('mail-settings.edit');

                        Route::patch('/mail-settings', [SchoolMailSettingController::class, 'update'])
                            ->name('mail-settings.update');

                        Route::post('/mail-settings/test', [SchoolMailSettingController::class, 'test'])
                            ->name('mail-settings.test');

                        Route::get('/audit-logs', [SchoolAuditLogController::class, 'index'])
                            ->name('audit-logs.index');

                        Route::get('/audit-logs/export', [SchoolAuditLogController::class, 'export'])
                            ->name('audit-logs.export');
                    });

                Route::middleware('role:school_admin|result_officer|super_admin')
                    ->group(function () {
                        Route::resource('grading-scales', GradingScaleController::class)
                            ->parameters(['grading-scales' => 'gradingScale'])
                            ->only(['index']);

                        Route::prefix('results')
                            ->name('results.')
                            ->group(function () {
                                Route::resource('manual', ManualResultController::class)
                                    ->parameters(['manual' => 'studentResult'])
                                    ->except(['show', 'destroy'])
                                    ->middleware('feature.school:results.manual_entry');

                                Route::get('/publishing', [ResultPublishingController::class, 'index'])
                                    ->middleware('feature.school:results.publish')
                                    ->name('publishing.index');

                                Route::get('/upload', [ResultUploadController::class, 'index'])
                                    ->middleware('feature.school:results.upload')
                                    ->name('upload.index');

                                Route::post('/upload', [ResultUploadController::class, 'store'])
                                    ->middleware('feature.school:results.upload')
                                    ->name('upload.store');

                                Route::get('/upload/template', [ResultUploadController::class, 'downloadTemplate'])
                                    ->middleware('feature.school:results.upload')
                                    ->name('upload.template');
                            });

                        Route::get('/result-system', [SchoolResultSystemController::class, 'index'])
                            ->middleware('feature.school:results.manual_entry,results.review,results.publish')
                            ->name('result-system.index');

                        Route::get('/result-access-policy', [SchoolResultAccessPolicyController::class, 'show'])
                            ->name('result-access-policy.show');

                        Route::get('/report-card-settings/preview', [ReportCardSettingController::class, 'preview'])
                            ->name('report-card-settings.preview');
                    });

                Route::get('/teacher-results', [TeacherResultEntryController::class, 'index'])
                    ->name('teacher-results.index');

                Route::get('/teacher-assignments/my', [TeacherAssignmentController::class, 'myAssignments'])
                    ->middleware('feature.school:teacher.assignments.view')
                    ->name('teacher-assignments.my');

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

                Route::get('/support', [SchoolSupportThreadController::class, 'index'])
                    ->middleware('feature.school:support.manage')
                    ->name('support.index');

                Route::get('/support/create', [SchoolSupportThreadController::class, 'create'])
                    ->middleware('feature.school:support.manage')
                    ->name('support.create');

                Route::post('/support', [SchoolSupportThreadController::class, 'store'])
                    ->middleware('feature.school:support.manage')
                    ->name('support.store');

                Route::get('/support/{thread}', [SchoolSupportThreadController::class, 'show'])
                    ->middleware('feature.school:support.manage')
                    ->name('support.show');

                Route::get('/support-attachments/{attachment}', [SchoolSupportThreadController::class, 'downloadAttachment'])
                    ->middleware('feature.school:support.manage')
                    ->name('support-attachments.download');

                Route::post('/support/{thread}/reply', [SchoolSupportThreadController::class, 'reply'])
                    ->middleware('feature.school:support.manage')
                    ->name('support.reply');

                Route::patch('/support/{thread}/assign', [SchoolSupportThreadController::class, 'assign'])
                    ->middleware('feature.school:support.manage')
                    ->name('support.assign');

                Route::post('/support/{thread}/escalate', [SchoolSupportThreadController::class, 'escalate'])
                    ->middleware('feature.school:support.manage')
                    ->name('support.escalate');

                Route::patch('/support/{thread}/close', [SchoolSupportThreadController::class, 'close'])
                    ->middleware('feature.school:support.manage')
                    ->name('support.close');
            });

        Route::middleware('role:school_admin|super_admin')
            ->group(function () {
                Route::get('/teacher-assignments', [TeacherAssignmentController::class, 'index'])
                    ->middleware('feature.school:teacher.assignment.manage')
                    ->name('teacher-assignments.index');

                Route::post('/teacher-assignments', [TeacherAssignmentController::class, 'store'])
                    ->middleware('feature.school:teacher.assignment.manage')
                    ->name('teacher-assignments.store');

                Route::get('/teacher-assignments/create', [TeacherAssignmentController::class, 'create'])
                    ->middleware('feature.school:teacher.assignment.manage')
                    ->name('teacher-assignments.create');

                Route::get('/teacher-assignments/{assignment}/edit', [TeacherAssignmentController::class, 'edit'])
                    ->middleware('feature.school:teacher.assignment.manage')
                    ->name('teacher-assignments.edit');

                Route::patch('/teacher-assignments/{assignment}', [TeacherAssignmentController::class, 'update'])
                    ->middleware('feature.school:teacher.assignment.manage')
                    ->name('teacher-assignments.update');

                Route::post('/teacher-assignments/{assignment}/archive', [TeacherAssignmentController::class, 'archive'])
                    ->middleware('feature.school:teacher.assignment.manage')
                    ->name('teacher-assignments.archive');

                Route::post('/teacher-assignments/{assignment}/restore', [TeacherAssignmentController::class, 'restore'])
                    ->middleware('feature.school:teacher.assignment.manage')
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

                Route::post('/sessions/{academicSession}/activate', [AcademicSessionController::class, 'activate'])
                    ->name('sessions.activate');

                Route::post('/sessions/{academicSession}/archive', [AcademicSessionController::class, 'archive'])
                    ->name('sessions.archive');

                Route::post('/sessions/{academicSession}/restore', [AcademicSessionController::class, 'restore'])
                    ->name('sessions.restore');

                Route::resource('terms', TermController::class)
                    ->except(['show', 'destroy']);

                Route::post('/terms/{term}/activate', [TermController::class, 'activate'])
                    ->name('terms.activate');

                Route::post('/terms/{term}/archive', [TermController::class, 'archive'])
                    ->name('terms.archive');

                Route::post('/terms/{term}/restore', [TermController::class, 'restore'])
                    ->name('terms.restore');

                Route::get('/admission-number-settings', [AdmissionNumberSettingController::class, 'edit'])
                    ->name('admission-number-settings.edit');

                Route::put('/admission-number-settings', [AdmissionNumberSettingController::class, 'update'])
                    ->name('admission-number-settings.update');

                Route::resource('staff', StaffUserController::class)
                    ->parameters(['staff' => 'staff'])
                    ->except(['show', 'destroy']);

                Route::post('/staff/{staff}/disable', [StaffUserController::class, 'disable'])
                    ->name('staff.disable');

                Route::post('/staff/{staff}/enable', [StaffUserController::class, 'enable'])
                    ->name('staff.enable');

                Route::get('/role-features', [RoleFeatureSettingController::class, 'edit'])
                    ->name('role-features.edit');

                Route::patch('/role-features', [RoleFeatureSettingController::class, 'update'])
                    ->name('role-features.update');

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

                Route::prefix('branding')
                    ->name('branding.')
                    ->middleware('feature:branding_manager')
                    ->group(function () {
                        Route::get('/', [SchoolBrandingController::class, 'edit'])
                            ->name('edit');
                        Route::patch('/', [SchoolBrandingController::class, 'update'])
                            ->name('update');
                        Route::post('/logo', [SchoolBrandingController::class, 'logo'])
                            ->name('logo');
                        Route::post('/favicon', [SchoolBrandingController::class, 'favicon'])
                            ->name('favicon');
                    });

                Route::get('/subscription', [SchoolPlanController::class, 'show'])
                    ->name('subscription.show');

                Route::get('/student-promotions', [StudentPromotionController::class, 'index'])
                    ->middleware('feature.school:student.promote,student.transfer')
                    ->name('student-promotions.index');

                Route::get('/student-promotions/create', [StudentPromotionController::class, 'create'])
                    ->middleware('feature.school:student.promote,student.transfer')
                    ->name('student-promotions.create');

                Route::post('/student-promotions/preview', [StudentPromotionController::class, 'preview'])
                    ->middleware('feature.school:student.promote,student.transfer')
                    ->name('student-promotions.preview');

                Route::post('/student-promotions', [StudentPromotionController::class, 'store'])
                    ->middleware('feature.school:student.promote,student.transfer')
                    ->name('student-promotions.store');

                Route::get('/student-promotions/history', [StudentPromotionController::class, 'history'])
                    ->middleware('feature.school:student.promote,student.transfer')
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
                        Route::delete('/manual/{studentResult}', [ManualResultController::class, 'destroy'])
                            ->middleware('feature.school:results.manual_entry')
                            ->name('manual.destroy');

                        Route::patch('/manual/{studentResult}/inline', [ManualResultController::class, 'inlineUpdate'])
                            ->middleware('feature.school:results.manual_entry')
                            ->name('manual.inline-update');

                        Route::post('/manual/{studentResult}/submit', [ManualResultController::class, 'submit'])
                            ->middleware('feature.school:results.manual_entry,results.review')
                            ->name('manual.submit');

                        Route::post('/manual/{studentResult}/return', [ManualResultController::class, 'returnForCorrection'])
                            ->middleware('feature.school:results.review')
                            ->name('manual.return');

                        Route::post('/manual/{studentResult}/approve', [ManualResultController::class, 'approve'])
                            ->middleware('feature.school:results.review')
                            ->name('manual.approve');
                    });
            });

        Route::middleware('role:school_admin|result_officer|super_admin')
            ->group(function () {
                Route::prefix('results')
                    ->name('results.')
                    ->group(function () {
                        Route::post('/publishing/publish', [ResultPublishingController::class, 'publish'])
                            ->middleware('feature.school:results.publish')
                            ->name('publishing.publish');

                        Route::post('/publishing/{studentResult}/publish', [ResultPublishingController::class, 'publishSingle'])
                            ->middleware('feature.school:results.publish')
                            ->name('publishing.publish-single');

                        Route::post('/publishing/unpublish', [ResultPublishingController::class, 'unpublish'])
                            ->middleware('feature.school:results.publish')
                            ->name('publishing.unpublish');

                        Route::post('/publishing/{studentResult}/unpublish', [ResultPublishingController::class, 'unpublishSingle'])
                            ->middleware('feature.school:results.publish')
                            ->name('publishing.unpublish-single');
                    });

                Route::get('/result-reviews', [TeacherResultReviewController::class, 'index'])
                    ->middleware('feature.school:results.review')
                    ->name('result-reviews.index');

                Route::get('/result-reviews/{submission}', [TeacherResultReviewController::class, 'show'])
                    ->middleware('feature.school:results.review')
                    ->name('result-reviews.show');

                Route::patch('/result-reviews/{submission}', [TeacherResultReviewController::class, 'update'])
                    ->middleware('feature.school:results.review')
                    ->name('result-reviews.update');

                Route::post('/result-reviews/{submission}/return', [TeacherResultReviewController::class, 'return'])
                    ->middleware('feature.school:results.review')
                    ->name('result-reviews.return');

                Route::post('/result-reviews/{submission}/approve', [TeacherResultReviewController::class, 'approve'])
                    ->middleware('feature.school:results.review')
                    ->name('result-reviews.approve');

                Route::post('/result-reviews/{submission}/publish', [TeacherResultReviewController::class, 'publish'])
                    ->middleware('feature.school:results.publish')
                    ->name('result-reviews.publish');

                Route::post('/result-reviews/{submission}/void', [TeacherResultReviewController::class, 'voidSubmission'])
                    ->middleware('feature.school:results.review')
                    ->name('result-reviews.void');
            });

        Route::middleware(['role:school_admin'])
            ->group(function () {
                Route::post('/students/{student}/communication/send', [SchoolCommunicationController::class, 'sendStudentMessage'])
                    ->middleware('feature.communication:communication.send')
                    ->name('communications.students.send');

                Route::get('/communications/bulk', [SchoolCommunicationController::class, 'bulkForm'])
                    ->middleware('feature.communication:communication.bulk')
                    ->name('communications.bulk');
                Route::post('/communications/bulk/send', [SchoolCommunicationController::class, 'sendBulk'])
                    ->middleware('feature.communication:communication.bulk')
                    ->name('communications.bulk.send');
                Route::post('/communications/bulk/batches/{bulkCommunicationBatch}/process', [SchoolCommunicationController::class, 'processBulkBatch'])
                    ->middleware('feature.communication:communication.bulk')
                    ->name('communications.bulk.process');
                Route::post('/communications/bulk/batches/{bulkCommunicationBatch}/retry-failed', [SchoolCommunicationController::class, 'retryBulkBatchFailures'])
                    ->middleware('feature.communication:communication.bulk')
                    ->name('communications.bulk.retry-failed');
            });

        Route::middleware('role:school_admin|result_officer|teacher|super_admin')
            ->group(function () {
                Route::get('/students/{student}/results', StudentResultWorkspaceController::class)
                    ->middleware('feature.school:students.view,students.view_assigned')
                    ->name('students.results')
                    ->withTrashed();

                Route::get('/students/{student}/results/workspace', StudentResultWorkspaceController::class)
                    ->middleware('feature.school:students.view,students.view_assigned')
                    ->name('students.results.workspace')
                    ->withTrashed();

                Route::get('/students/{student}', [StudentController::class, 'show'])
                    ->middleware('feature.school:students.view,students.view_assigned')
                    ->name('students.show')
                    ->withTrashed();
            });
    });

Route::middleware(['auth', 'demo.safe'])->group(function () {
    Route::get('/search', SearchController::class)
        ->name('search');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/feed', [NotificationController::class, 'feed'])
        ->name('notifications.feed');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';
