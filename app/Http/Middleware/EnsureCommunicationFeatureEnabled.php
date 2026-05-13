<?php

namespace App\Http\Middleware;

use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;
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

        $school = app(CurrentSchoolService::class)->get($user);

        if (! $school) {
            abort(403);
        }

        if (! app(SchoolAuthorizationService::class)->can($user, $school, $featureKey)) {
            abort(403, 'This communication feature is disabled for your role.');
        }

        return $next($request);
    }
}
