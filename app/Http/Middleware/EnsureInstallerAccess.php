<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallerStateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstallerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app(InstallerStateService::class)->canAccessInstaller()) {
            return $next($request);
        }

        abort(404, 'The installer is not available for this deployment.');
    }
}
