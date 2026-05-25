<?php

use App\Services\System\DeploymentModeService;

return [
    /*
    |--------------------------------------------------------------------------
    | Sanfaani Demo Automation Foundation
    |--------------------------------------------------------------------------
    |
    | This configuration powers demo requests, demo sessions, demo credentials,
    | activity tracking, and expiry. It does not implement onboarding,
    | marketing automation sequences, billing, update delivery, backups, or
    | marketplace packaging.
    |
    */

    'enabled' => (bool) env('SANFAANI_DEMO_ENABLED', true),
    'default_duration_days' => (int) env('SANFAANI_DEMO_DEFAULT_DURATION_DAYS', 7),
    'max_active_sessions' => (int) env('SANFAANI_DEMO_MAX_ACTIVE_SESSIONS', 25),
    'reset_enabled' => (bool) env('SANFAANI_DEMO_RESET_ENABLED', false),
    'email_enabled' => (bool) env('SANFAANI_DEMO_EMAIL_ENABLED', true),
    'school_slug_prefix' => 'demo-school',
    'password_length' => 14,
    'roles' => [
        'super_admin' => [
            'label' => 'Super Admin demo',
            'assign_role' => 'school_admin',
            'note' => 'Simulated Super Admin experience scoped to the demo school.',
        ],
        'school_admin' => [
            'label' => 'School Admin demo',
            'assign_role' => 'school_admin',
        ],
        'teacher' => [
            'label' => 'Teacher demo',
            'assign_role' => 'teacher',
        ],
        'parent' => [
            'label' => 'Parent demo',
            'assign_role' => 'parent',
        ],
        'student' => [
            'label' => 'Student demo',
            'assign_role' => 'student',
        ],
        'result_officer' => [
            'label' => 'Result Officer demo',
            'assign_role' => 'result_officer',
        ],
        'accountant' => [
            'label' => 'Accountant demo',
            'assign_role' => 'accountant',
        ],
    ],
    'deployment_modes' => [
        DeploymentModeService::MODE_SAAS,
        DeploymentModeService::MODE_SINGLE_SCHOOL,
        DeploymentModeService::MODE_MANAGED,
    ],
    'license_modes' => [
        DeploymentModeService::LICENSE_SUBSCRIPTION,
        DeploymentModeService::LICENSE_TRIAL,
        DeploymentModeService::LICENSE_DEMO,
    ],
];
