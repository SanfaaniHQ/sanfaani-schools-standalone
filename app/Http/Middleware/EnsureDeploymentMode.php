<?php

namespace App\Http\Middleware;

use App\Services\System\DeploymentModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeploymentMode
{
    public function handle(Request $request, Closure $next, string ...$modes): Response
    {
        if ($modes === []) {
            return $next($request);
        }

        $allowedModes = collect($modes)
            ->flatMap(fn (string $mode): array => explode('|', $mode))
            ->map(fn (string $mode): string => str($mode)->trim()->lower()->replace(['-', ' '], '_')->toString())
            ->filter()
            ->values()
            ->all();

        if (in_array(app(DeploymentModeService::class)->mode(), $allowedModes, true)) {
            return $next($request);
        }

        abort(404, 'This page is not available for the current deployment mode.');
    }
}
