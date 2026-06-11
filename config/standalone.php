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

    'pwa_offline' => [
        'capture_enabled' => (bool) env('SANFAANI_PWA_OFFLINE_CAPTURE_ENABLED', false),
        'sync_enabled' => (bool) env('SANFAANI_PWA_OFFLINE_SYNC_ENABLED', false),
        'allowed_modules' => array_values(array_filter(array_map(
            fn (string $module): string => strtolower(trim($module)),
            explode(',', (string) env('SANFAANI_PWA_OFFLINE_ALLOWED_MODULES', 'attendance'))
        ))),
    ],

    'scheduler_monitor' => [
        'enabled' => (bool) env('SANFAANI_SCHEDULER_MONITOR_ENABLED', true),
        'cache_store' => env('SANFAANI_SCHEDULER_HEARTBEAT_CACHE_STORE', 'file'),
        'schedule_cache_store' => env('SANFAANI_SCHEDULER_MUTEX_CACHE_STORE', 'file'),
        'cache_key' => env('SANFAANI_SCHEDULER_HEARTBEAT_CACHE_KEY', 'standalone.scheduler.last_heartbeat_at'),
        'stale_after_minutes' => (int) env('SANFAANI_SCHEDULER_STALE_AFTER_MINUTES', 15),
        'cache_ttl_days' => (int) env('SANFAANI_SCHEDULER_HEARTBEAT_TTL_DAYS', 7),
    ],

    'health' => [
        'disk_free_warning_mb' => (int) env('SANFAANI_HEALTH_DISK_FREE_WARNING_MB', 1024),
        'writable_paths' => [
            'storage/app',
            'storage/framework/cache',
            'storage/logs',
        ],
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

    'surface_gates' => [
        'standalone_navigation_enabled' => (bool) env('SANFAANI_STANDALONE_NAVIGATION_ENABLED', true),
        'private_homepage_enabled' => (bool) env('SANFAANI_STANDALONE_PRIVATE_HOMEPAGE_ENABLED', true),
        'hide_saas_surfaces' => (bool) env('SANFAANI_STANDALONE_HIDE_SAAS_SURFACES', true),
        'hide_marketplace_surfaces' => (bool) env('SANFAANI_STANDALONE_HIDE_MARKETPLACE_SURFACES', true),
        'hide_demo_surfaces' => (bool) env('SANFAANI_STANDALONE_HIDE_DEMO_SURFACES', true),
        'hide_platform_marketing_surfaces' => (bool) env('SANFAANI_STANDALONE_HIDE_PLATFORM_MARKETING_SURFACES', true),
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
        'SANFAANI_PWA_OFFLINE_CAPTURE_ENABLED' => 'false',
        'SANFAANI_PWA_OFFLINE_SYNC_ENABLED' => 'false',
        'SANFAANI_PWA_OFFLINE_ALLOWED_MODULES' => 'attendance',
        'SANFAANI_STANDALONE_HIDE_SAAS_SURFACES' => 'true',
        'SANFAANI_STANDALONE_HIDE_MARKETPLACE_SURFACES' => 'true',
        'SANFAANI_STANDALONE_HIDE_DEMO_SURFACES' => 'true',
        'SANFAANI_STANDALONE_HIDE_PLATFORM_MARKETING_SURFACES' => 'true',
    ],
];
