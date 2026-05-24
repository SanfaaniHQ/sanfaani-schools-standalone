<?php

namespace App\Http\Middleware;

use App\Services\System\FeatureAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $features = app(FeatureAccessService::class);

        if ($features->enabled($feature, user: $request->user())) {
            return $next($request);
        }

        $config = config('features.features.'.str($feature)->trim()->lower()->replace(['-', ' '], '_')->toString(), []);

        if ((bool) data_get($config, 'hidden_when_disabled', true)) {
            abort(404);
        }

        abort(403, $features->reason($feature, user: $request->user()));
    }
}
