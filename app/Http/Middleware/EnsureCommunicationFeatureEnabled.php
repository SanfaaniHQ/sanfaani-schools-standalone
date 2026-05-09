<?php

namespace App\Http\Middleware;

use App\Services\CurrentSchoolService;
use App\Services\SchoolRoleFeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommunicationFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasAnyRole(['super_admin', 'school_admin'])) {
            return $next($request);
        }

        $school = app(CurrentSchoolService::class)->get($user);
        $role = app(CurrentSchoolService::class)->roleContext($user) ?? $user->roles->pluck('name')->first();

        if (! $school || ! $role) {
            abort(403);
        }

        if (! app(SchoolRoleFeatureService::class)->enabled($school->id, $role, $featureKey)) {
            abort(403, 'This communication feature is disabled for your role.');
        }

        return $next($request);
    }
}
