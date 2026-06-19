<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstallationAdminContext
{
    private const SUPPORT_SESSION_KEYS = [
        'is_support_session',
        'support_school_id',
        'support_role_context',
        'support_reason',
        'support_access_started_by',
        'support_access_started_at',
        'support_access_last_confirmed_at',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('super_admin')) {
            abort(403, 'You do not have access to the Installation Admin console.');
        }

        session()->forget(self::SUPPORT_SESSION_KEYS);
        TenantContext::set(null, 'super_admin');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $next($request);
    }
}
