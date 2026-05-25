<?php

namespace App\Http\Middleware;

use App\Services\Licensing\LicenseValidationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $result = app(LicenseValidationService::class)->validate();

        if ($result->valid()) {
            return $next($request);
        }

        if ($request->user()?->hasRole('super_admin') && ! $request->routeIs('admin.license.*')) {
            return redirect()
                ->route('admin.license.index')
                ->with('error', 'License validation is required before accessing that area.');
        }

        abort(403, 'A valid license is required.');
    }
}
