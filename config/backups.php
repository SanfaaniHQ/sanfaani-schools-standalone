<?php

use App\Services\System\DeploymentModeService;

return [
    /*
    |--------------------------------------------------------------------------
    | Backup System Foundation
    |--------------------------------------------------------------------------
    |
    | These settings power safe backup metadata, preflight checks, verification,
    | retention, restore planning, and pre-update readiness. The web workflow
    | does not restore, dump secrets, or create full application archives.
    |
    */

    'enabled' => (bool) env('SANFAANI_BACKUPS_ENABLED', true),

    'feature' => 'backup_manager',

    'disk' => env('SANFAANI_BACKUP_DISK', 'local'),

    'retention_days' => (int) env('SANFAANI_BACKUP_RETENTION_DAYS', 14),

    'max_archive_mb' => (int) env('SANFAANI_BACKUP_MAX_ARCHIVE_MB', 250),

    'database_enabled' => (bool) env('SANFAANI_BACKUP_DATABASE_ENABLED', true),

    'files_enabled' => (bool) env('SANFAANI_BACKUP_FILES_ENABLED', true),

    'config_enabled' => (bool) env('SANFAANI_BACKUP_CONFIG_ENABLED', true),

    'pre_update_required' => (bool) env('SANFAANI_BACKUP_PRE_UPDATE_REQUIRED', true),

    'verify_after_create' => (bool) env('SANFAANI_BACKUP_VERIFY_AFTER_CREATE', true),

    'require_license_entitlement' => true,

    'trial_allowed' => false,

    'metadata_directory' => 'backups/metadata',

    'recent_verified_days' => (int) env('SANFAANI_BACKUP_RETENTION_DAYS', 14),

    'shell_dump_enabled' => false,

    'entitlement_keys' => [
        'backup_manager',
        'backups',
        'managed_backups',
    ],

    'safe_file_roots' => [
        'storage/app/public',
        'public/storage',
        'public/uploads',
    ],

    'excluded_paths' => [
        'vendor',
        'node_modules',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/logs',
        'storage/app/private/backups',
        'storage/app/updates',
        'public/build.zip',
        '.env',
    ],

    'deployment_route_groups' => [
        DeploymentModeService::MODE_SAAS => 'platform_backups',
        DeploymentModeService::MODE_SINGLE_SCHOOL => 'standalone_backups',
        DeploymentModeService::MODE_MANAGED => 'managed_backups',
    ],

    'labels' => [
        DeploymentModeService::MODE_SAAS => 'Platform Backups',
        DeploymentModeService::MODE_SINGLE_SCHOOL => 'Backups',
        DeploymentModeService::MODE_MANAGED => 'Managed Backups',
    ],
];
