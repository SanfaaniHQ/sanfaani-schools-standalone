<?php

use App\Services\System\DeploymentModeService;

return [
    /*
    |--------------------------------------------------------------------------
    | Staging Release Candidate Readiness
    |--------------------------------------------------------------------------
    |
    | This configuration defines staging validation expectations only. It does
    | not deploy, build archives, run migrations, write environment files, clear
    | caches, call external services, or change business workflows.
    |
    */

    'required_staging_commands' => [
        'staging:check-readiness',
        'deployment:check-readiness',
        'performance:audit',
        'security:audit',
        'release:check-readiness',
        'marketplace:validate-package',
    ],

    'required_configs' => [
        'config/staging.php',
        'config/sanfaani.php',
        'config/deployment_modes.php',
        'config/features.php',
        'config/installer.php',
        'config/licensing.php',
        'config/demo.php',
        'config/onboarding.php',
        'config/marketing.php',
        'config/updates.php',
        'config/backups.php',
        'config/packaging.php',
        'config/deployment_readiness.php',
        'config/performance.php',
        'config/security.php',
        'config/branding.php',
        'config/release.php',
        'config/ui.php',
    ],

    'staging_required_docs' => [
        'docs/staging/staging-release-candidate-plan.md',
        'docs/staging/staging-validation-checklist.md',
        'docs/staging/staging-environment-matrix.md',
        'docs/staging/staging-mode-test-plan.md',
        'docs/staging/staging-smoke-test-results-template.md',
        'docs/staging/staging-go-no-go-checklist.md',
        'docs/staging/staging-known-issues.md',
        'docs/staging/staging-handover-notes.md',
    ],

    'final_roadmap_docs' => [
        'docs/roadmap/final-commercialization-roadmap.md',
        'docs/roadmap/commercialization-acceptance-checklist.md',
        'docs/roadmap/product-mode-capability-matrix.md',
        'docs/roadmap/remaining-work-register.md',
        'docs/roadmap/production-launch-readiness.md',
        'docs/roadmap/risk-register.md',
        'docs/roadmap/next-30-60-90-days.md',
        'docs/roadmap/final-executive-summary.md',
    ],

    'required_feature_flags' => [
        'saas_billing',
        'standalone_installer',
        'license_activation',
        'demo_system',
        'guided_onboarding',
        'update_manager',
        'backup_manager',
        'managed_backups',
        'performance_diagnostics',
        'security_diagnostics',
        'branding_manager',
        'white_label_branding',
        'marketing_automation',
    ],

    'required_deployment_modes' => [
        DeploymentModeService::MODE_SAAS,
        DeploymentModeService::MODE_SINGLE_SCHOOL,
        DeploymentModeService::MODE_MANAGED,
    ],

    'required_license_modes' => [
        DeploymentModeService::LICENSE_SUBSCRIPTION,
        DeploymentModeService::LICENSE_ANNUAL,
        DeploymentModeService::LICENSE_LIFETIME,
        DeploymentModeService::LICENSE_MANAGED_CONTRACT,
        DeploymentModeService::LICENSE_WHITE_LABEL,
        DeploymentModeService::LICENSE_TRIAL,
        DeploymentModeService::LICENSE_DEMO,
    ],

    'required_route_groups' => [
        'platform_dashboard',
        'platform_schools',
        'platform_subscriptions',
        'platform_marketing',
        'platform_demo',
        'demo_sessions',
        'guided_onboarding',
        'platform_updates',
        'platform_backups',
        'platform_performance',
        'platform_security_diagnostics',
        'platform_branding',
        'local_dashboard',
        'local_school_settings',
        'license_activation',
        'standalone_installer',
        'standalone_updates',
        'standalone_backups',
        'standalone_branding',
        'managed_support',
        'managed_backups',
        'managed_updates',
        'managed_branding',
        'managed_white_label',
    ],

    'mode_validation_matrix' => [
        'saas' => [
            'env_values' => [
                'SANFAANI_DEPLOYMENT_MODE=saas',
                'SANFAANI_LICENSE_MODE=subscription',
                'SANFAANI_DEMO_ENABLED=true',
                'SANFAANI_MARKETING_AUTOMATION_ENABLED=true',
            ],
            'enabled_features' => [
                'saas_billing',
                'demo_system',
                'guided_onboarding',
                'marketing_automation',
                'update_manager',
                'backup_manager',
                'branding_manager',
                'performance_diagnostics',
                'security_diagnostics',
            ],
            'hidden_features' => [
                'standalone_installer',
                'license_activation',
                'managed_backups',
                'managed_white_label',
            ],
            'admin_routes' => [
                'admin.dashboard',
                'admin.schools.index',
                'admin.school-subscriptions.index',
                'admin.demo.index',
                'admin.marketing.index',
                'admin.updates.index',
                'admin.backups.index',
                'admin.branding.edit',
            ],
            'school_routes' => [
                'school.dashboard',
                'school.profile.edit',
                'school.results.publishing.index',
                'school.cbt.dashboard',
                'school.branding.edit',
            ],
            'known_limitations' => [
                'Full billing/payment automation remains planned.',
                'Billing may require manual operations in staging.',
            ],
        ],

        'single_school' => [
            'env_values' => [
                'SANFAANI_DEPLOYMENT_MODE=single_school',
                'SANFAANI_LICENSE_MODE=annual',
                'SANFAANI_INSTALLER_ENABLED=true',
                'SANFAANI_LICENSE_VALIDATION_ENABLED=true',
            ],
            'enabled_features' => [
                'standalone_installer',
                'license_activation',
                'guided_onboarding',
                'update_manager',
                'backup_manager',
                'branding_manager',
                'performance_diagnostics',
                'security_diagnostics',
            ],
            'hidden_features' => [
                'saas_billing',
                'platform_marketing',
                'platform_demo',
                'managed_backups',
                'managed_white_label',
            ],
            'admin_routes' => [
                'installer.welcome',
                'admin.license.index',
                'admin.updates.index',
                'admin.backups.index',
                'admin.performance.index',
                'admin.security.index',
            ],
            'school_routes' => [
                'school.dashboard',
                'school.profile.edit',
                'school.mail-settings.edit',
                'school.branding.edit',
                'school.subscription.show',
            ],
            'known_limitations' => [
                'Real update application remains planned.',
                'Automated restore remains planned.',
            ],
        ],

        'managed' => [
            'env_values' => [
                'SANFAANI_DEPLOYMENT_MODE=managed',
                'SANFAANI_LICENSE_MODE=managed_contract',
                'SANFAANI_BACKUPS_ENABLED=true',
                'SANFAANI_UPDATES_ENABLED=true',
            ],
            'enabled_features' => [
                'license_activation',
                'guided_onboarding',
                'marketing_automation',
                'update_manager',
                'backup_manager',
                'managed_backups',
                'branding_manager',
                'white_label_branding',
                'performance_diagnostics',
                'security_diagnostics',
            ],
            'hidden_features' => [
                'saas_billing',
                'platform_demo',
                'standalone_license',
            ],
            'admin_routes' => [
                'admin.dashboard',
                'admin.schools.index',
                'admin.support-threads.index',
                'admin.updates.index',
                'admin.backups.index',
                'admin.branding.edit',
            ],
            'school_routes' => [
                'school.dashboard',
                'school.profile.edit',
                'school.support.index',
                'school.branding.edit',
            ],
            'known_limitations' => [
                'Managed deployment automation is contract-specific and remains planned.',
                'Managed backups are foundation-ready unless separately automated.',
            ],
        ],

        'demo' => [
            'env_values' => [
                'SANFAANI_DEPLOYMENT_MODE=saas',
                'SANFAANI_LICENSE_MODE=demo',
                'SANFAANI_DEMO_ENABLED=true',
                'SANFAANI_DEMO_RESET_ENABLED=false',
            ],
            'enabled_features' => [
                'demo_system',
                'guided_onboarding',
                'marketing_automation',
                'branding_manager',
            ],
            'hidden_features' => [
                'backup_manager',
                'update_manager',
                'license_activation',
            ],
            'admin_routes' => [
                'landing.demo',
                'landing.demo.submit',
                'admin.demo.index',
                'admin.demo.show',
                'admin.marketing.index',
            ],
            'school_routes' => [
                'school.dashboard',
                'school.results.publishing.index',
                'school.cbt.dashboard',
            ],
            'known_limitations' => [
                'Demo reset remains disabled unless a safe demo-only reset pattern exists.',
                'Demo conversion to paid billing remains planned.',
            ],
        ],

        'trial' => [
            'env_values' => [
                'SANFAANI_DEPLOYMENT_MODE=saas',
                'SANFAANI_LICENSE_MODE=trial',
                'SANFAANI_ONBOARDING_TRIAL_ENABLED=true',
                'SANFAANI_MARKETING_AUTOMATION_ENABLED=true',
            ],
            'enabled_features' => [
                'saas_billing',
                'demo_system',
                'guided_onboarding',
                'marketing_automation',
                'branding_manager',
                'performance_diagnostics',
                'security_diagnostics',
            ],
            'hidden_features' => [
                'standalone_installer',
                'managed_backups',
                'managed_white_label',
            ],
            'admin_routes' => [
                'admin.dashboard',
                'admin.schools.index',
                'admin.onboarding.progress',
                'admin.marketing.index',
                'admin.sales.tasks.index',
            ],
            'school_routes' => [
                'school.dashboard',
                'school.profile.edit',
                'school.subscription.show',
            ],
            'known_limitations' => [
                'Trial-to-paid billing conversion remains planned.',
                'Manual sales follow-up may be required in staging.',
            ],
        ],

        'white_label' => [
            'env_values' => [
                'SANFAANI_DEPLOYMENT_MODE=managed',
                'SANFAANI_LICENSE_MODE=white_label',
                'SANFAANI_WHITE_LABEL_ENABLED=true',
                'SANFAANI_BRAND_MODE=white_label',
            ],
            'enabled_features' => [
                'branding_manager',
                'white_label_branding',
                'guided_onboarding',
                'license_activation',
                'update_manager',
                'backup_manager',
                'performance_diagnostics',
                'security_diagnostics',
            ],
            'hidden_features' => [
                'saas_billing',
                'platform_demo',
            ],
            'admin_routes' => [
                'admin.branding.edit',
                'admin.license.index',
                'admin.updates.index',
                'admin.backups.index',
            ],
            'school_routes' => [
                'school.branding.edit',
                'school.profile.edit',
                'school.public-page.edit',
            ],
            'known_limitations' => [
                'White-label domain provisioning remains planned.',
                'Reseller tooling and full theme builder remain planned.',
            ],
        ],

        'marketplace_buyer_package' => [
            'env_values' => [
                'SANFAANI_DEPLOYMENT_MODE=single_school',
                'SANFAANI_LICENSE_MODE=annual',
                'SANFAANI_INSTALLER_ENABLED=true',
                'SANFAANI_MARKETING_AUTOMATION_ENABLED=false',
            ],
            'enabled_features' => [
                'standalone_installer',
                'license_activation',
                'guided_onboarding',
                'update_manager',
                'backup_manager',
                'branding_manager',
                'performance_diagnostics',
                'security_diagnostics',
            ],
            'hidden_features' => [
                'saas_billing',
                'marketing_automation',
                'managed_backups',
                'managed_white_label',
            ],
            'admin_routes' => [
                'installer.welcome',
                'admin.license.index',
                'admin.updates.index',
                'admin.backups.index',
                'admin.performance.index',
            ],
            'school_routes' => [
                'school.dashboard',
                'school.profile.edit',
                'school.mail-settings.edit',
                'school.branding.edit',
            ],
            'known_limitations' => [
                'Marketplace ZIP generation remains planned.',
                'Marketplace API integration remains planned.',
                'Buyer deployment remains guided, not one-click automated.',
            ],
        ],
    ],

    'staging_go_no_go_checks' => [
        'tests_pass',
        'route_list_passes',
        'deployment_readiness_passes',
        'performance_audit_passes',
        'security_audit_passes_with_production_overrides',
        'release_readiness_passes',
        'marketplace_validation_passes',
        'git_diff_check_passes',
        'protected_files_not_staged',
        'planned_vs_implemented_limitations_documented',
    ],

    'protected_files' => [
        'database/migrations/2026_05_01_173857_create_result_publications_table.php',
        'public/build.zip',
    ],

    'known_local_warnings' => [
        'Local APP_ENV may be local; staging/prod should use production-like env values.',
        'Local APP_DEBUG may be true; staging/prod must set APP_DEBUG=false.',
        'Optional redis and zip PHP extensions may be absent locally.',
        'public/build.zip may exist locally but must not be packaged or deployed.',
    ],
];
