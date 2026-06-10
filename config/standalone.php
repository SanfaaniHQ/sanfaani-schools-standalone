<?php

use App\Services\System\DeploymentModeService;

return [
    /*
    |--------------------------------------------------------------------------
    | Standalone School Edition
    |--------------------------------------------------------------------------
    |
    | These values describe the single-school local-first product posture. They
    | are intentionally safe for private school installations: installer on,
    | local database as the source of truth, and cloud sync disabled until an
    | owner explicitly configures an endpoint and token.
    |
    */

    'product_edition' => env('SANFAANI_PRODUCT_EDITION', 'standalone'),
    'deployment_mode' => env('SANFAANI_DEPLOYMENT_MODE', DeploymentModeService::MODE_SINGLE_SCHOOL),
    'installer_enabled' => (bool) env('SANFAANI_INSTALLER_ENABLED', true),
    'installed' => (bool) env('SANFAANI_INSTALLED', false),
    'license_mode' => env('SANFAANI_LICENSE_MODE', DeploymentModeService::LICENSE_ANNUAL),
    'offline_mode' => env('SANFAANI_STANDALONE_OFFLINE_MODE', 'local_first'),

    'sync' => [
        'enabled' => (bool) env('SANFAANI_STANDALONE_SYNC_ENABLED', false),
        'endpoint' => env('SANFAANI_STANDALONE_SYNC_ENDPOINT', ''),
        'token' => env('SANFAANI_STANDALONE_SYNC_TOKEN', ''),
        'backup_enabled' => (bool) env('SANFAANI_STANDALONE_BACKUP_SYNC_ENABLED', false),
    ],

    'main_flow' => [
        'run_installer',
        'create_school_admin',
        'activate_license',
        'use_local_school_dashboard',
        'configure_backup_or_sync_later',
    ],

    'demoted_flows' => [
        'saas_school_signup',
        'saas_billing',
        'customer_acquisition_demo_requests',
        'marketplace_live_demo_homepage',
        'marketplace_package_builder_as_primary_flow',
    ],

    'recommended_env' => [
        'SANFAANI_PRODUCT_EDITION' => 'standalone',
        'SANFAANI_DEPLOYMENT_MODE' => DeploymentModeService::MODE_SINGLE_SCHOOL,
        'SANFAANI_INSTALLER_ENABLED' => 'true',
        'SANFAANI_INSTALLED' => 'false',
        'SANFAANI_LICENSE_MODE' => DeploymentModeService::LICENSE_ANNUAL,
        'SANFAANI_STANDALONE_OFFLINE_MODE' => 'local_first',
        'SANFAANI_STANDALONE_SYNC_ENABLED' => 'false',
        'SANFAANI_STANDALONE_SYNC_ENDPOINT' => '',
        'SANFAANI_STANDALONE_SYNC_TOKEN' => '',
        'SANFAANI_STANDALONE_BACKUP_SYNC_ENABLED' => 'false',
    ],
];
