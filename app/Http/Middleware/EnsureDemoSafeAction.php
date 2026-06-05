<?php

namespace App\Http\Middleware;

use App\Services\Demo\DemoSandboxGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoSafeAction
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app(DemoSandboxGuard::class)->shouldBlock($request)) {
            abort(403, 'Demo safe mode blocks this action.');
        }

        return $next($request);
    }
}
