<?php

use App\Services\System\DeploymentModeService;

return [
    /*
    |--------------------------------------------------------------------------
    | Sanfaani License Foundation
    |--------------------------------------------------------------------------
    |
    | This configuration powers local activation, validation, entitlement, and
    | audit foundations. It does not implement billing, update delivery, backup
    | management, demo automation, marketplace packaging, or remote license calls.
    |
    */

    'server_url' => env('SANFAANI_LICENSE_SERVER_URL'),
    'license_key' => env('SANFAANI_LICENSE_KEY'),
    'offline_grace_days' => (int) env('SANFAANI_LICENSE_OFFLINE_GRACE_DAYS', 7),
    'validation_enabled' => (bool) env('SANFAANI_LICENSE_VALIDATION_ENABLED', true),
    'require_domain_match' => (bool) env('SANFAANI_LICENSE_REQUIRE_DOMAIN_MATCH', true),
    'expiry_warning_days' => 30,
    'remote_validation_enabled' => false,
    'audit_entitlement_checks' => false,
    'allow_unlicensed_modes' => [
        DeploymentModeService::LICENSE_TRIAL => false,
        DeploymentModeService::LICENSE_DEMO => false,
    ],
    'types' => DeploymentModeService::LICENSE_MODES,
    'status_values' => [
        'active',
        'trial',
        'demo',
        'expired',
        'suspended',
        'revoked',
    ],
];
