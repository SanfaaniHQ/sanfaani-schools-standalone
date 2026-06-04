<?php

use App\Services\System\DeploymentModeService;

return [
    /*
    |--------------------------------------------------------------------------
    | Sanfaani Guided Onboarding Foundation
    |--------------------------------------------------------------------------
    |
    | This file controls role-based onboarding visibility and default seeded
    | checklist definitions. It does not implement marketing automation, sales
    | automation, billing, updates, backups, or marketplace packaging.
    |
    */

    'enabled' => (bool) env('SANFAANI_ONBOARDING_ENABLED', true),
    'demo_enabled' => (bool) env('SANFAANI_ONBOARDING_DEMO_ENABLED', true),
    'trial_enabled' => (bool) env('SANFAANI_ONBOARDING_TRIAL_ENABLED', true),
    'progress_widget_enabled' => (bool) env('SANFAANI_ONBOARDING_PROGRESS_WIDGET_ENABLED', true),
    'require_completion' => (bool) env('SANFAANI_ONBOARDING_REQUIRE_COMPLETION', false),

    'roles' => [
        'super_admin',
        'school_admin',
        'teacher',
        'parent',
        'student',
        'result_officer',
        'accountant',
    ],

    'deployment_modes' => DeploymentModeService::DEPLOYMENT_MODES,
    'license_modes' => DeploymentModeService::LICENSE_MODES,

    'checklists' => [
        'super_admin' => [
            'name' => 'Platform operator onboarding',
            'description' => 'Review the hosted platform, demo requests, and school workspace setup for SaaS or managed clients.',
            'role_name' => 'super_admin',
            'deployment_modes' => [DeploymentModeService::MODE_SAAS, DeploymentModeService::MODE_MANAGED],
            'license_modes' => DeploymentModeService::LICENSE_MODES,
            'steps' => [
                ['key' => 'review_platform_settings', 'title' => 'Review platform settings', 'route_name' => 'admin.platform-settings.edit'],
                ['key' => 'create_first_school', 'title' => 'Create or review schools', 'route_name' => 'admin.schools.index', 'deployment_modes' => [DeploymentModeService::MODE_SAAS, DeploymentModeService::MODE_MANAGED]],
                ['key' => 'review_subscriptions', 'title' => 'Review subscriptions', 'route_name' => 'admin.school-subscriptions.index', 'feature_key' => 'saas_billing', 'deployment_modes' => [DeploymentModeService::MODE_SAAS]],
                ['key' => 'review_demo_sessions', 'title' => 'Review demo sessions', 'route_name' => 'admin.demo.index', 'feature_key' => 'demo_system'],
            ],
        ],
        'school_admin' => [
            'name' => 'School admin onboarding',
            'description' => 'Start the school workspace with profile, academic periods, classes, subjects, staff, students, and result settings.',
            'role_name' => 'school_admin',
            'deployment_modes' => DeploymentModeService::DEPLOYMENT_MODES,
            'license_modes' => DeploymentModeService::LICENSE_MODES,
            'steps' => [
                ['key' => 'complete_school_profile', 'title' => 'Complete school profile', 'route_name' => 'school.profile.edit'],
                ['key' => 'configure_sessions', 'title' => 'Configure academic sessions', 'route_name' => 'school.sessions.index'],
                ['key' => 'add_classes', 'title' => 'Add classes', 'route_name' => 'school.classes.index'],
                ['key' => 'add_subjects', 'title' => 'Add subjects', 'route_name' => 'school.subjects.index'],
                ['key' => 'add_teachers', 'title' => 'Add staff and teachers', 'route_name' => 'school.staff.index'],
                ['key' => 'import_students', 'title' => 'Import students', 'route_name' => 'school.students.index'],
                ['key' => 'configure_result_settings', 'title' => 'Configure result settings', 'route_name' => 'school.result-system.index', 'feature_key' => 'result_publication'],
                ['key' => 'test_communication', 'title' => 'Test communication tools', 'route_name' => 'school.communications.bulk', 'feature_key' => 'communication_tools', 'required' => false],
            ],
        ],
        'teacher' => [
            'name' => 'Teacher onboarding',
            'description' => 'Review assigned classes and subjects before entering results.',
            'role_name' => 'teacher',
            'steps' => [
                ['key' => 'review_assigned_classes', 'title' => 'Review assigned classes', 'route_name' => 'school.teacher-assignments.my'],
                ['key' => 'review_subjects', 'title' => 'Review assigned subjects', 'route_name' => 'school.teacher-assignments.my'],
                ['key' => 'enter_scores', 'title' => 'Enter assessment scores', 'route_name' => 'school.teacher-results.index', 'feature_key' => 'result_publication'],
            ],
        ],
        'parent' => [
            'name' => 'Parent onboarding',
            'description' => 'Review linked children and published result access when the parent portal is enabled.',
            'role_name' => 'parent',
            'steps' => [
                ['key' => 'review_children', 'title' => 'Review linked children', 'action_url' => '#'],
                ['key' => 'review_results', 'title' => 'Review published results', 'action_url' => '#', 'feature_key' => 'parent_portal', 'required' => false],
            ],
        ],
        'student' => [
            'name' => 'Student onboarding',
            'description' => 'Review the student dashboard and available result access when the student portal is enabled.',
            'role_name' => 'student',
            'steps' => [
                ['key' => 'open_student_dashboard', 'title' => 'Open student dashboard', 'action_url' => '#'],
                ['key' => 'review_results', 'title' => 'Review personal results', 'action_url' => '#', 'feature_key' => 'student_portal', 'required' => false],
            ],
        ],
        'result_officer' => [
            'name' => 'Result officer onboarding',
            'description' => 'Prepare grading, result review, and publishing settings for the school.',
            'role_name' => 'result_officer',
            'steps' => [
                ['key' => 'configure_grading', 'title' => 'Configure grading scales', 'route_name' => 'school.grading-scales.index'],
                ['key' => 'review_result_system', 'title' => 'Review result workflow', 'route_name' => 'school.result-system.index', 'feature_key' => 'result_publication'],
                ['key' => 'review_publication_settings', 'title' => 'Review publication settings', 'route_name' => 'school.results.publishing.index', 'feature_key' => 'result_publication'],
            ],
        ],
        'accountant' => [
            'name' => 'Accountant onboarding',
            'description' => 'Review payment settings and scratch-card tools used for result access.',
            'role_name' => 'accountant',
            'steps' => [
                ['key' => 'review_payment_settings', 'title' => 'Review payment settings', 'action_url' => '#', 'required' => false],
                ['key' => 'review_scratch_cards', 'title' => 'Review scratch-card tools', 'route_name' => 'school.scratch-cards.index', 'feature_key' => 'scratch_cards'],
            ],
        ],
    ],
];
