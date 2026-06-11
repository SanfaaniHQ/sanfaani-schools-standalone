<?php

namespace App\Providers;

use App\Models\CommunicationLog;
use App\Models\School;
use App\Models\StudentResult;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherResultSubmission;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Events\DemoRequested;
use App\Events\LicenseExpiring;
use App\Events\OnboardingChecklistCompleted;
use App\Events\OnboardingStepCompleted;
use App\Listeners\Marketing\CreateRenewalReminderFromLicense;
use App\Listeners\Marketing\CreateSalesTaskFromDemoRequest;
use App\Listeners\Marketing\TrackOnboardingConversionActivity;
use App\Policies\CommunicationLogPolicy;
use App\Policies\SchoolPolicy;
use App\Policies\StudentResultPolicy;
use App\Policies\TeacherAssignmentPolicy;
use App\Policies\TeacherResultSubmissionPolicy;
use App\Policies\UserPolicy;
use App\Services\AuditService;
use App\Services\BrandingService;
use App\Services\CurrentSchoolService;
use App\Services\DashboardWidgetService;
use App\Services\MailSettingService;
use App\Services\PlatformSettingService;
use App\Services\SchoolAuthorizationService;
use App\Services\ScratchAnalyticsService;
use App\Services\System\DeploymentBehaviorService;
use App\Services\System\DeploymentModeService;
use App\Services\System\FeatureAccessService;
use App\Services\TenantMailManager;
use App\Services\TenantThemeResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PlatformSettingService::class);
        $this->app->singleton(MailSettingService::class);
        $this->app->singleton(BrandingService::class);
        $this->app->singleton(TenantThemeResolver::class);
        $this->app->singleton(TenantMailManager::class);
        $this->app->singleton(ScratchAnalyticsService::class);
        $this->app->singleton(DashboardWidgetService::class);
        $this->app->singleton(DeploymentModeService::class);
        $this->app->singleton(DeploymentBehaviorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Model::preventLazyLoading(! $this->app->isProduction());
        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation): void {
            logger()->warning('Lazy loading prevented.', [
                'model' => $model::class,
                'relation' => $relation,
                'route' => app()->runningInConsole() ? null : request()->route()?->getName(),
            ]);
        });

        Gate::policy(StudentResult::class, StudentResultPolicy::class);
        Gate::policy(CommunicationLog::class, CommunicationLogPolicy::class);
        Gate::policy(TeacherClassAssignment::class, TeacherAssignmentPolicy::class);
        Gate::policy(TeacherSubjectAssignment::class, TeacherAssignmentPolicy::class);
        Gate::policy(TeacherResultSubmission::class, TeacherResultSubmissionPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(School::class, SchoolPolicy::class);

        foreach ([
            'communication.send',
            'communication.bulk',
            'results.publish',
            'results.review',
            'results.manual_entry',
            'student.promote',
            'student.transfer',
            'support.manage',
            'support.direct_escalation',
            'teacher.assignment.manage',
            'attendance.view',
            'attendance.manage',
            'cbt.manage',
            'cbt.question_bank',
            'cbt.mark_theory',
            'cbt.publish_results',
            'cbt.public_competition',
            'cbt.certificates',
            'pdf.snapshots',
        ] as $featureKey) {
            Gate::define($featureKey, fn (User $user, School $school) => app(SchoolAuthorizationService::class)
                ->can($user, $school, $featureKey));
        }

        Gate::define('access-school-feature', fn (User $user, School $school, string $featureKey) => app(SchoolAuthorizationService::class)
            ->can($user, $school, $featureKey));

        Gate::define('school.feature', function (User $user, string $featureSlug): bool {
            $school = app(CurrentSchoolService::class)->get($user);

            return app(SchoolAuthorizationService::class)->can($user, $school, $featureSlug);
        });

        Blade::if('schoolFeature', function (string ...$featureKeys): bool {
            $user = auth()->user();
            $school = app(CurrentSchoolService::class)->get($user);

            return app(SchoolAuthorizationService::class)->canAny($user, $school, $featureKeys);
        });

        Blade::if('feature', function (string $featureKey, ?School $school = null, ?User $user = null): bool {
            return app(FeatureAccessService::class)->enabled($featureKey, $school, $user);
        });

        app(MailSettingService::class)->applyConfigured();
        app(TenantMailManager::class)->configureCurrent();

        Event::listen(NotificationSending::class, function (NotificationSending $event): void {
            AuditService::log('notification', class_basename($event->notification), [
                'channel' => $event->channel,
                'notifiable_type' => $event->notifiable::class,
                'notifiable_id' => method_exists($event->notifiable, 'getKey') ? $event->notifiable->getKey() : null,
                'school_id' => data_get($event->notifiable, 'school_id'),
            ]);
        });

        Event::listen(DemoRequested::class, CreateSalesTaskFromDemoRequest::class);
        Event::listen(OnboardingStepCompleted::class, TrackOnboardingConversionActivity::class);
        Event::listen(OnboardingChecklistCompleted::class, TrackOnboardingConversionActivity::class);
        Event::listen(LicenseExpiring::class, CreateRenewalReminderFromLicense::class);

        View::composer('*', function ($view) {
            $service = app(PlatformSettingService::class);
            $settings = $service->get();
            $schoolBranding = app(BrandingService::class)->current();
            $tenantTheme = app(TenantThemeResolver::class)->forBranding($schoolBranding);
            $supportedLanguageCodes = config('sanfaani.supported_languages', ['en', 'ar', 'fr', 'yo', 'ha']);
            $supportedLanguages = collect(config('sanfaani.languages', []))
                ->only($supportedLanguageCodes)
                ->all();
            $rtlLocales = config('sanfaani.rtl_locales', ['ar']);

            $view->with([
                'platformSettings' => $settings,
                'platformLogoUrl' => $service->assetUrl($settings->logo_path),
                'platformFaviconUrl' => $service->assetUrl($settings->favicon_path),
                'platformLoginBackgroundUrl' => $service->assetUrl($settings->login_background_path),
                'platformInitials' => $service->initials($settings->platform_name),
                'schoolBranding' => $schoolBranding,
                'tenantTheme' => $tenantTheme,
                'tenantCssVariables' => app(TenantThemeResolver::class)->cssVariables($schoolBranding),
                'supportedLanguages' => $supportedLanguages,
                'rtlLocales' => $rtlLocales,
                'isRtl' => in_array(app()->getLocale(), $rtlLocales, true),
            ]);
        });
    }
}
