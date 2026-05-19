<?php

use App\Http\Middleware\EnsureCommunicationFeatureEnabled;
use App\Http\Middleware\EnsureActiveRole;
use App\Http\Middleware\EnsureSchoolFeatureEnabled;
use App\Http\Middleware\EnsureValidSchoolContext;
use App\Http\Middleware\IdleTimeoutMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
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
            'feature.communication' => EnsureCommunicationFeatureEnabled::class,
            'feature.school' => EnsureSchoolFeatureEnabled::class,
            'school.context' => EnsureValidSchoolContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
