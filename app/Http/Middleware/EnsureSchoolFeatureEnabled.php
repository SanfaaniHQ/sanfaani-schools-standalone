<?php

namespace App\Http\Middleware;

use App\Services\CurrentSchoolService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

        foreach ($featureKeys as $featureKey) {
            if (Gate::forUser($user)->allows('school.feature', $featureKey)) {
                return $next($request);
            }
        }

        abort(403, 'This feature is not enabled for your current school role.');
    }
}
