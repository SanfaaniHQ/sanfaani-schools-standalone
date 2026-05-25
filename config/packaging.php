<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Marketplace Packaging Foundation
    |--------------------------------------------------------------------------
    |
    | This manifest describes what a future marketplace package should contain
    | and what must stay out. The validator reads this list only; it does not
    | build, copy, zip, upload, or delete files.
    |
    */

    'package_name' => 'sanfaani-schools',

    'default_channel' => 'stable',

    'package_modes' => [
        'marketplace_single_school',
        'direct_single_school',
        'managed_client',
        'white_label',
        'demo_sales',
    ],

    'include_paths' => [
        'app',
        'bootstrap',
        'config',
        'database',
        'docs',
        'public',
        'resources',
        'routes',
        'tests',
        'artisan',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'vite.config.js',
        '.env.example',
        '.env.marketplace.example',
        'README.md',
    ],

    'exclude_paths' => [
        '.env',
        '.env.*.local',
        'vendor',
        'node_modules',
        'storage/logs',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/app/backups',
        'storage/app/private',
        'storage/app/database',
        'storage/app/updates',
        'public/build.zip',
        'database/*.sqlite',
        'database/*.sqlite-*',
        '.idea',
        '.vscode',
        '.DS_Store',
        'Thumbs.db',
        '*.key',
        '*.pem',
        '*.p12',
        '*.dump',
        '*.sql',
        '*.tar',
        '*.tar.gz',
        '*.zip',
        'coverage',
        '.phpunit.cache',
        'npm-debug.log',
        'yarn-error.log',
    ],

    'prohibited_paths' => [
        '.env',
        'vendor',
        'node_modules',
        'storage/logs',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/app/private',
        'public/build.zip',
    ],

    'required_docs' => [
        'docs/marketplace/marketplace-package-structure.md',
        'docs/marketplace/include-exclude-list.md',
        'docs/marketplace/buyer-installation-checklist.md',
        'docs/marketplace/marketplace-documentation-checklist.md',
        'docs/marketplace/screenshot-checklist.md',
        'docs/marketplace/demo-checklist.md',
        'docs/marketplace/reseller-checklist.md',
        'docs/marketplace/release-packaging-checklist.md',
        'docs/marketplace/package-validation-checklist.md',
        'docs/marketplace/post-purchase-onboarding-checklist.md',
        'docs/marketplace/white-label-buyer-checklist.md',
        'docs/marketplace/managed-client-handover-checklist.md',
        'docs/marketplace/marketplace-listing-copy.md',
        'docs/marketplace/marketplace-packaging-plan.md',
        'docs/installation/single-school-installer.md',
        'docs/licensing/license-activation.md',
        'docs/updates/update-system-plan.md',
        'docs/backups/backup-system-plan.md',
    ],

    'required_checks' => [
        'required_docs_exist',
        'marketplace_env_template_safe',
        'prohibited_paths_excluded',
        'public_build_zip_excluded',
        'installer_license_update_backup_docs_present',
    ],

    'marketplace_assets' => [
        'listing_copy' => 'docs/marketplace/marketplace-listing-copy.md',
        'screenshots' => 'docs/marketplace/screenshot-checklist.md',
        'demo_script' => 'docs/marketplace/demo-checklist.md',
        'buyer_docs' => 'docs/marketplace/buyer-installation-checklist.md',
    ],
];
