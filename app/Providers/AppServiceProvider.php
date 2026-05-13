<?php

namespace App\Providers;

use App\Models\StudentResult;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherResultSubmission;
use App\Models\TeacherSubjectAssignment;
use App\Models\School;
use App\Models\User;
use App\Policies\StudentResultPolicy;
use App\Policies\TeacherAssignmentPolicy;
use App\Policies\TeacherResultSubmissionPolicy;
use App\Services\CurrentSchoolService;
use App\Services\PlatformSettingService;
use App\Services\MailSettingService;
use App\Services\SchoolAuthorizationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PlatformSettingService::class);
        $this->app->singleton(MailSettingService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(StudentResult::class, StudentResultPolicy::class);
        Gate::policy(TeacherClassAssignment::class, TeacherAssignmentPolicy::class);
        Gate::policy(TeacherSubjectAssignment::class, TeacherAssignmentPolicy::class);
        Gate::policy(TeacherResultSubmission::class, TeacherResultSubmissionPolicy::class);

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
        ] as $featureKey) {
            Gate::define($featureKey, fn (User $user, School $school) => app(SchoolAuthorizationService::class)
                ->can($user, $school, $featureKey));
        }

        Gate::define('access-school-feature', fn (User $user, School $school, string $featureKey) => app(SchoolAuthorizationService::class)
            ->can($user, $school, $featureKey));

        Blade::if('schoolFeature', function (string ...$featureKeys): bool {
            $user = auth()->user();
            $school = app(CurrentSchoolService::class)->get($user);

            return app(SchoolAuthorizationService::class)->canAny($user, $school, $featureKeys);
        });

        app(MailSettingService::class)->applyConfigured();

        View::composer('*', function ($view) {
            $service = app(PlatformSettingService::class);
            $settings = $service->get();

            $view->with([
                'platformSettings' => $settings,
                'platformLogoUrl' => $service->assetUrl($settings->logo_path),
                'platformFaviconUrl' => $service->assetUrl($settings->favicon_path),
                'platformLoginBackgroundUrl' => $service->assetUrl($settings->login_background_path),
                'platformInitials' => $service->initials($settings->platform_name),
            ]);
        });
    }
}
