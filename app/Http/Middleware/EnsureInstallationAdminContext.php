<?php

namespace App\Http\Middleware;

use App\Services\UserWorkspaceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstallationAdminContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isActiveAccount() || ! $user->hasRole('super_admin')) {
            abort(403, 'You do not have access to the Installation Admin console.');
        }

        $workspaces = app(UserWorkspaceService::class);
        $switchingContext = $workspaces->activeKey($user) !== 'global:super_admin';

        abort_unless($workspaces->selectInstallationAdmin($user, $switchingContext), 403);

        return $next($request);
    }
}
