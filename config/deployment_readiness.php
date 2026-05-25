<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Deployment Readiness Foundation
    |--------------------------------------------------------------------------
    |
    | This config powers a read-only deployment readiness report. It does not
    | provision servers, write environment files, run migrations, clear caches,
    | create symlinks, or modify production assets.
    |
    */

    'required_php_extensions' => [
        'ctype',
        'curl',
        'fileinfo',
        'json',
        'mbstring',
        'openssl',
        'pdo',
        'tokenizer',
        'xml',
    ],

    'optional_php_extensions' => [
        'bcmath',
        'gd',
        'intl',
        'pdo_mysql',
        'redis',
        'zip',
    ],

    'writable_paths' => [
        'storage',
        'bootstrap/cache',
    ],

    'required_docs' => [
        'docs/deployment/namecheap-shared-hosting.md',
        'docs/deployment/cpanel-hosting.md',
        'docs/deployment/vps-hosting.md',
        'docs/deployment/cloud-hosting.md',
        'docs/deployment/shared-hosting-readiness-checklist.md',
        'docs/deployment/public-folder-mapping.md',
        'docs/deployment/storage-link-workarounds.md',
        'docs/deployment/queue-and-cron-strategy.md',
        'docs/deployment/file-permissions.md',
        'docs/deployment/smtp-setup.md',
        'docs/deployment/deployment-troubleshooting.md',
        'docs/deployment/managed-client-deployment.md',
        'docs/deployment/marketplace-buyer-deployment.md',
        'docs/deployment/single-school-production-launch-checklist.md',
        'docs/installation/single-school-installer.md',
        'docs/licensing/license-activation.md',
        'docs/updates/update-system-plan.md',
        'docs/backups/backup-system-plan.md',
        'docs/marketplace/marketplace-packaging-plan.md',
    ],

    'shared_hosting_checks' => [
        'document_root_points_to_public',
        'env_outside_public_root',
        'storage_outside_public_root',
        'cron_scheduler_configured',
        'queue_fallback_selected',
        'storage_link_or_workaround_ready',
    ],

    'production_env_checks' => [
        'app_env_not_local',
        'app_debug_false',
        'app_key_present',
        'database_config_present',
        'mail_config_present',
        'queue_config_present',
        'license_config_present',
        'updates_config_present',
        'backups_config_present',
    ],

    'prohibited_production_settings' => [
        'APP_DEBUG=true',
        'APP_ENV=local',
        'MAIL_MAILER=log',
        'QUEUE_CONNECTION=null',
    ],

    'public_exposure_checks' => [
        '.env',
        'storage/logs',
        'storage/framework/cache',
        'storage/framework/sessions',
        'vendor',
        'node_modules',
        'public/build.zip',
    ],
];
