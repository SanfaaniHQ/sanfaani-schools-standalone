<?php

use App\Http\Middleware\EnsureActiveRole;
use App\Http\Middleware\EnsureCommunicationFeatureEnabled;
use App\Http\Middleware\EnsureDemoSafeAction;
use App\Http\Middleware\EnsureDeploymentBehavior;
use App\Http\Middleware\EnsureDeploymentMode;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\EnsureInstallationAdminContext;
use App\Http\Middleware\EnsureValidLicense;
use App\Http\Middleware\EnsureSchoolFeatureEnabled;
use App\Http\Middleware\EnsureValidSchoolContext;
use App\Http\Middleware\IdleTimeoutMiddleware;
use App\Http\Middleware\EnsureInstallerAccess;
use App\Http\Middleware\PreventReinstall;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path('routes/installer.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            IdleTimeoutMiddleware::class,
        ]);

        $middleware->alias([
            'role' => EnsureActiveRole::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'feature' => EnsureFeatureEnabled::class,
            'feature.communication' => EnsureCommunicationFeatureEnabled::class,
            'feature.school' => EnsureSchoolFeatureEnabled::class,
            'installation.admin' => EnsureInstallationAdminContext::class,
            'demo.safe' => EnsureDemoSafeAction::class,
            'deployment.behavior' => EnsureDeploymentBehavior::class,
            'deployment.mode' => EnsureDeploymentMode::class,
            'installer.access' => EnsureInstallerAccess::class,
            'license.valid' => EnsureValidLicense::class,
            'prevent.reinstall' => PreventReinstall::class,
            'school.context' => EnsureValidSchoolContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
