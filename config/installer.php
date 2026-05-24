<?php

use App\Services\System\DeploymentModeService;

return [
    /*
    |--------------------------------------------------------------------------
    | Standalone Installer Foundation
    |--------------------------------------------------------------------------
    |
    | The installer is intentionally narrow: it only prepares a single-school
    | installation and writes an installation lock. It does not activate
    | licenses, run updates, run backups, package marketplaces, or automate demos.
    |
    */

    'enabled' => (bool) env('SANFAANI_INSTALLER_ENABLED', false),

    'feature' => 'standalone_installer',

    'lock_file' => 'installed.lock',

    'allowed_modes' => [
        DeploymentModeService::MODE_SINGLE_SCHOOL,
    ],

    'allow_managed' => false,

    'requirements' => [
        'php_minimum' => '8.2.0',
        'required_extensions' => [
            'ctype',
            'fileinfo',
            'json',
            'mbstring',
            'openssl',
            'pdo',
            'tokenizer',
            'xml',
        ],
        'optional_extensions' => [
            'bcmath',
            'curl',
            'gd',
            'intl',
            'redis',
            'zip',
        ],
    ],

    'steps' => [
        'welcome',
        'requirements',
        'permissions',
        'database',
        'environment',
        'app-key',
        'migrations',
        'admin',
        'school',
        'smtp',
        'review',
        'complete',
    ],
];
