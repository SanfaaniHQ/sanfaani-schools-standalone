# Staging Validation Checklist

Use this checklist before promoting the commercialization foundation into staging.

## Git And Scope

- [ ] Current branch is `feature/v7-cbt-localization-hardening`.
- [ ] Latest commits include final commercialization roadmap documentation.
- [ ] Working tree state is reviewed.
- [ ] `public/build.zip` is not staged.
- [ ] `database/migrations/2026_05_01_173857_create_result_publications_table.php` is not staged.
- [ ] No release ZIPs were generated.
- [ ] No deployment automation was run.
- [ ] No destructive database changes were made.

## Required Docs

- [ ] `docs/staging/staging-release-candidate-plan.md`
- [ ] `docs/staging/staging-validation-checklist.md`
- [ ] `docs/staging/staging-environment-matrix.md`
- [ ] `docs/staging/staging-mode-test-plan.md`
- [ ] `docs/staging/staging-smoke-test-results-template.md`
- [ ] `docs/staging/staging-go-no-go-checklist.md`
- [ ] `docs/staging/staging-known-issues.md`
- [ ] `docs/staging/staging-handover-notes.md`
- [ ] Final roadmap docs under `docs/roadmap/`

## Required Config

- [ ] `config/staging.php`
- [ ] `config/sanfaani.php`
- [ ] `config/deployment_modes.php`
- [ ] `config/features.php`
- [ ] `config/installer.php`
- [ ] `config/licensing.php`
- [ ] `config/demo.php`
- [ ] `config/onboarding.php`
- [ ] `config/marketing.php`
- [ ] `config/updates.php`
- [ ] `config/backups.php`
- [ ] `config/packaging.php`
- [ ] `config/deployment_readiness.php`
- [ ] `config/performance.php`
- [ ] `config/security.php`
- [ ] `config/branding.php`
- [ ] `config/release.php`
- [ ] `config/ui.php`

## Required Commands

- [ ] `php artisan staging:check-readiness`
- [ ] `php artisan deployment:check-readiness`
- [ ] `php artisan performance:audit`
- [ ] `php artisan security:audit`
- [ ] `php artisan release:check-readiness`
- [ ] `php artisan marketplace:validate-package`

## Required Validation

```bash
php artisan test --filter=StagingReadinessCommandTest
php artisan test --filter=StagingDocumentationTest
php artisan test --filter=StagingModeMatrixTest
php artisan staging:check-readiness
php artisan test --filter=FinalRoadmapDocumentationTest
php artisan test --filter=UiComponentTest
php artisan test --filter=ReleaseReadinessCommandTest
php artisan test --filter=BrandingResolutionTest
php artisan test --filter=ProductionSecurityAuditTest
php artisan test --filter=PerformanceAuditTest
php artisan test --filter=DeploymentReadinessTest
php artisan test --filter=BackupDashboardTest
php artisan test --filter=UpdatePreflightTest
php artisan test --filter=MarketplacePackageValidationTest
php artisan test --filter=MarketingAutomationTest
php artisan test --filter=OnboardingProgressTest
php artisan test --filter=DemoRequestTest
php artisan test --filter=LicenseValidationTest
php artisan test --filter=InstallerFlowTest
php artisan test --filter=TenantIsolationTest
php artisan test --filter=FeatureAccessServiceTest
php artisan test
php artisan route:list
php artisan deployment:check-readiness
php artisan performance:audit
php artisan release:check-readiness
php artisan marketplace:validate-package
git diff --check
```

Run `php artisan security:audit` with production-style overrides when local `.env` is not production-like.

## Honest Limitations

- [ ] Full billing/payment workflow remains planned.
- [ ] Real update application remains planned.
- [ ] Automated restore remains planned.
- [ ] Marketplace ZIP generation remains planned.
- [ ] Full parent/student portals remain planned where incomplete.
- [ ] White-label domain provisioning and reseller tooling remain planned.
