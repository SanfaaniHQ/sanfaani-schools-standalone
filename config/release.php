<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enterprise Release Readiness Foundation
    |--------------------------------------------------------------------------
    |
    | This file defines release validation expectations only. It does not build
    | archives, provision servers, run tests, tag releases, push Git refs, or
    | deploy code.
    |
    */

    'release_channels' => [
        'stable',
        'beta',
        'security',
        'hotfix',
        'managed',
        'white_label',
        'marketplace',
    ],

    'default_channel' => 'stable',

    'versioning_pattern' => '/^v\\d+\\.\\d+\\.\\d+(?:-(?:beta|rc|hotfix|security)\\.\\d+)?$/',

    'required_commands' => [
        'php artisan test --filter=BrandingResolutionTest',
        'php artisan test --filter=ProductionSecurityAuditTest',
        'php artisan test --filter=PerformanceAuditTest',
        'php artisan test --filter=DeploymentReadinessTest',
        'php artisan test --filter=BackupDashboardTest',
        'php artisan test --filter=UpdatePreflightTest',
        'php artisan test --filter=MarketplacePackageValidationTest',
        'php artisan test --filter=MarketingAutomationTest',
        'php artisan test --filter=OnboardingProgressTest',
        'php artisan test --filter=DemoRequestTest',
        'php artisan test --filter=LicenseValidationTest',
        'php artisan test --filter=InstallerFlowTest',
        'php artisan test --filter=TenantIsolationTest',
        'php artisan test --filter=FeatureAccessServiceTest',
        'php artisan test',
        'php artisan route:list',
        'php artisan deployment:check-readiness',
        'php artisan performance:audit',
        'php artisan security:audit',
        'php artisan marketplace:validate-package',
        'git diff --check',
    ],

    'required_test_filters' => [
        'BrandingResolutionTest',
        'ProductionSecurityAuditTest',
        'PerformanceAuditTest',
        'DeploymentReadinessTest',
        'BackupDashboardTest',
        'UpdatePreflightTest',
        'MarketplacePackageValidationTest',
        'MarketingAutomationTest',
        'OnboardingProgressTest',
        'DemoRequestTest',
        'LicenseValidationTest',
        'InstallerFlowTest',
        'TenantIsolationTest',
        'FeatureAccessServiceTest',
    ],

    'required_docs' => [
        'docs/release-notes/release-workflow.md',
        'docs/changelog/CHANGELOG.md',
        'docs/release/enterprise-release-plan.md',
        'docs/release/release-readiness-checklist.md',
        'docs/release/smoke-test-checklist.md',
        'docs/release/regression-test-matrix.md',
        'docs/release/manual-qa-workflow.md',
        'docs/release/versioning-strategy.md',
        'docs/release/changelog-policy.md',
        'docs/release/release-notes-template.md',
        'docs/release/update-package-release-workflow.md',
        'docs/release/backup-before-release-checklist.md',
        'docs/release/rollback-validation-workflow.md',
        'docs/release/marketplace-release-checklist.md',
        'docs/release/white-label-release-checklist.md',
        'docs/release/managed-client-release-checklist.md',
        'docs/release/known-risk-register.md',
        'docs/release/final-preflight-checklist.md',
    ],

    'required_checklists' => [
        'release_readiness',
        'smoke_test',
        'regression_matrix',
        'manual_qa',
        'backup_before_release',
        'rollback_validation',
        'marketplace_release',
        'white_label_release',
        'managed_client_release',
        'final_preflight',
    ],

    'release_artifacts' => [
        'changelog_entry',
        'release_notes',
        'test_summary',
        'route_list_summary',
        'readiness_reports',
        'rollback_notes',
        'known_risk_register',
    ],

    'prohibited_dirty_files' => [
        'database/migrations/2026_05_01_173857_create_result_publications_table.php',
        'public/build.zip',
    ],

    'package_validation_rules' => [
        'no_final_zip_generated' => true,
        'public_build_zip_excluded' => true,
        'env_files_excluded' => true,
        'vendor_node_modules_excluded' => true,
        'storage_logs_cache_sessions_excluded' => true,
        'marketplace_template_required' => true,
    ],
];
