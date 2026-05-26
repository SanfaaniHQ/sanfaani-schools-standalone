# Regression Test Matrix

| Area | Required checks |
| --- | --- |
| Tenant isolation | `TenantIsolationTest`, authorization boundary tests |
| Feature/deployment | `FeatureAccessServiceTest`, deployment behavior tests |
| Installer | `InstallerFlowTest` |
| Licensing | `LicenseValidationTest`, entitlement tests |
| Demo | `DemoRequestTest`, demo session tests |
| Onboarding | `OnboardingProgressTest` |
| Marketing | `MarketingAutomationTest` |
| Updates | `UpdatePreflightTest`, update dashboard/package tests |
| Backups | `BackupDashboardTest`, creation/verification/retention tests |
| Marketplace | `MarketplacePackageValidationTest` |
| Deployment | `DeploymentReadinessTest` |
| Performance | `PerformanceAuditTest` |
| Security | `ProductionSecurityAuditTest` |
| Branding | `BrandingResolutionTest`, asset/access/email tests |

The full `php artisan test` suite is mandatory before release.
