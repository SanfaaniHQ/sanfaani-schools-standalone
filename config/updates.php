<?php

use App\Services\System\DeploymentModeService;

return [
    /*
    |--------------------------------------------------------------------------
    | Guided Update System Foundation
    |--------------------------------------------------------------------------
    |
    | This configuration powers local update visibility, package metadata
    | review, preflight checks, entitlement checks, logging, and rollback
    | planning. It does not download, extract, patch, migrate, or deploy code.
    |
    */

    'enabled' => (bool) env('SANFAANI_UPDATES_ENABLED', true),

    'feature' => 'update_manager',

    'channel' => env('SANFAANI_UPDATE_CHANNEL', 'stable'),

    'channels' => [
        'stable',
        'beta',
        'security',
    ],

    'server_url' => env('SANFAANI_UPDATE_SERVER_URL'),

    'allow_package_upload' => (bool) env('SANFAANI_UPDATE_ALLOW_PACKAGE_UPLOAD', true),

    'require_license_entitlement' => (bool) env('SANFAANI_UPDATE_REQUIRE_LICENSE_ENTITLEMENT', true),

    'backup_required' => (bool) env('SANFAANI_UPDATE_BACKUP_REQUIRED', true),

    'max_package_mb' => (int) env('SANFAANI_UPDATE_MAX_PACKAGE_MB', 50),

    'allowed_package_extensions' => [
        'zip',
    ],

    'allowed_package_mimes' => [
        'application/zip',
        'application/x-zip',
        'application/x-zip-compressed',
        'application/octet-stream',
    ],

    'allowed_product_names' => [
        'Sanfaani Schools',
        'Sanfaani Schools Standalone',
    ],

    'allowed_editions' => [
        'standalone',
        'single_school',
        'saas',
        'platform',
        'managed',
    ],

    'protected_paths' => [
        '.env',
        '.env.local',
        'public/build.zip',
        'database/migrations/2026_05_01_173857_create_result_publications_table.php',
    ],

    'package_disk' => 'updates',

    'package_directory' => 'packages',

    'entitlement_keys' => [
        'update_manager',
        'updates',
        'guided_updates',
    ],

    'trial_allowed' => false,

    'remote_checks_enabled' => false,

    'php_minimum' => '8.2.0',

    'laravel_minimum' => null,

    'deployment_route_groups' => [
        DeploymentModeService::MODE_SAAS => 'platform_updates',
        DeploymentModeService::MODE_SINGLE_SCHOOL => 'standalone_updates',
        DeploymentModeService::MODE_MANAGED => 'managed_updates',
    ],

    'labels' => [
        DeploymentModeService::MODE_SAAS => 'Platform Updates',
        DeploymentModeService::MODE_SINGLE_SCHOOL => 'Guided Updates',
        DeploymentModeService::MODE_MANAGED => 'Managed Updates',
    ],
];
