<?php

namespace App\Services\Security;

use App\Support\Security\SecurityCheckResult;

class ProductionReadinessService
{
    public function checks(): array
    {
        $env = (string) config('app.env');
        $debug = (bool) config('app.debug');
        $safeMode = (bool) config('security.production_error_safe_mode', true);

        return array_map(fn (SecurityCheckResult $check): array => $check->toArray(), [
            $env === 'production'
                ? SecurityCheckResult::pass('app_env', 'APP_ENV', 'APP_ENV is production.')
                : SecurityCheckResult::warning('app_env', 'APP_ENV', "APP_ENV is [{$env}]; use production for live deployments."),
            ! $debug
                ? SecurityCheckResult::pass('app_debug', 'APP_DEBUG', 'APP_DEBUG is false.')
                : SecurityCheckResult::fail('app_debug', 'APP_DEBUG', 'APP_DEBUG=true can expose stack traces and sensitive configuration in production.'),
            filled(config('app.key'))
                ? SecurityCheckResult::pass('app_key', 'Application key', 'APP_KEY is configured.')
                : SecurityCheckResult::fail('app_key', 'Application key', 'APP_KEY must be configured before production launch.'),
            $safeMode
                ? SecurityCheckResult::pass('production_error_safe_mode', 'Production error safe mode', 'Production error safe mode is enabled for diagnostics.')
                : SecurityCheckResult::warning('production_error_safe_mode', 'Production error safe mode', 'Enable production error safe mode before public launch.'),
            SecurityCheckResult::info('shared_hosting_errors', 'Shared-hosting errors', 'Keep Laravel errors generic in cPanel/Namecheap and review logs through protected hosting tools.'),
        ]);
    }
}
