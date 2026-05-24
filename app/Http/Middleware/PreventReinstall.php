<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallerStateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventReinstall
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(InstallerStateService::class)->isInstalled()) {
            return $next($request);
        }

        abort(404, 'This application is already installed.');
    }
}
