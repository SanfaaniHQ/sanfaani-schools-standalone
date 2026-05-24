<?php

namespace App\Http\Middleware;

use App\Services\System\DeploymentBehaviorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeploymentBehavior
{
    public function handle(Request $request, Closure $next, string ...$groups): Response
    {
        if ($groups === []) {
            return $next($request);
        }

        $behavior = app(DeploymentBehaviorService::class);
        $allowed = collect($groups)
            ->flatMap(fn (string $group): array => explode('|', $group))
            ->map(fn (string $group): string => str($group)->trim()->lower()->replace(['-', ' '], '_')->toString())
            ->filter()
            ->contains(fn (string $group): bool => $behavior->allowsRouteGroup($group, user: $request->user()));

        if ($allowed) {
            return $next($request);
        }

        abort(404, 'This area is not available for the current deployment behavior.');
    }
}
