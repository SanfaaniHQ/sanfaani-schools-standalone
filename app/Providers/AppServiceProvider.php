<?php

namespace App\Providers;

use App\Models\StudentResult;
use App\Models\TeacherResultSubmission;
use App\Policies\StudentResultPolicy;
use App\Policies\TeacherResultSubmissionPolicy;
use App\Services\PlatformSettingService;
use App\Services\MailSettingService;
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
        Gate::policy(TeacherResultSubmission::class, TeacherResultSubmissionPolicy::class);

        app(MailSettingService::class)->apply();

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
