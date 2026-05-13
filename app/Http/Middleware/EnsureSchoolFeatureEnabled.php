<?php

namespace App\Http\Middleware;

use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolFeatureEnabled
{
    public function handle(Request $request, Closure $next, string ...$featureKeys): Response
    {
        $user = $request->user();
        $school = app(CurrentSchoolService::class)->get($user);

        if (! $user || ! $school || $featureKeys === []) {
            abort(403);
        }

        if (! app(SchoolAuthorizationService::class)->canAny($user, $school, $featureKeys)) {
            abort(403, 'This feature is not enabled for your current school role.');
        }

        return $next($request);
    }
}
