# Production Launch Readiness

This guide defines what must be true before Sanfaani Schools is launched commercially.

## Launch Position

The product is ready for final production validation as a controlled commercial release with manual operating procedures around billing, updates, backups, marketplace packaging, and support. It is not ready to be sold as a fully automated SaaS billing platform, one-click marketplace package, self-applying updater, or automated restore system.

## Production-Ready Now

- Core school management and result workflows already implemented in the app.
- Public result checker and public school page flows already implemented in the app.
- Role-based dashboards and existing school user workflows.
- Feature, deployment behavior, tenant isolation, authorization, and release readiness foundations.
- Read-only deployment, performance, security, release, and marketplace readiness commands.
- Documentation for deployment, installer, license activation, demo, onboarding, marketing, updates, backups, marketplace, white-label, release, UI, support, and security.

## Foundation-Ready Only

- Billing is visibility/foundation/manual-operation ready, not fully automated.
- Licensing is local activation/validation ready, not remote-license-server complete.
- Updates are metadata/preflight ready, not real update application ready.
- Backups are metadata/verification/restore-plan ready, not automated restore ready.
- Marketplace package validation is ready, but final ZIP generation is planned.
- White-label branding is foundation-ready, but domain provisioning and reseller tooling are planned.
- Parent and student portal scope remains planned where workflows are incomplete.

## Required Pre-Launch Review

- Confirm target mode: SaaS, `single_school`, managed, white-label, demo, trial, or marketplace buyer package.
- Confirm target license mode and feature gates.
- Confirm production `.env` and secrets.
- Confirm database, storage, cache, queue, scheduler, mail, license, updates, backups, and branding config.
- Confirm `storage` and `bootstrap/cache` permissions.
- Confirm public document root points to `public` or follows the shared-hosting workaround.
- Confirm `.env`, backups, logs, private storage, `vendor`, `node_modules`, and `public/build.zip` are not exposed.
- Confirm a manual backup exists before migration, deployment, or update.
- Confirm rollback plan and release owner.
- Confirm support contact and escalation owner.

## Final Validation Commands

```bash
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
php artisan security:audit
php artisan release:check-readiness
php artisan marketplace:validate-package
git diff --check
```

## Launch Blockers

- Final validation command failure.
- Security audit failure.
- Tenant isolation or authorization failure.
- Deployment readiness failure that affects the selected environment.
- Performance audit failure that affects shared hosting or expected traffic.
- Missing backup before deployment or update.
- Missing rollback owner.
- Protected dirty files unintentionally changed or included.
- Sales, marketplace, or support docs that overstate planned systems.
- Production secret exposure.
- Missing support escalation owner.

## Go/No-Go Decision

Go only when:

- [ ] Required validation passes.
- [ ] Risks are reviewed in `docs/roadmap/risk-register.md`.
- [ ] Remaining work is triaged in `docs/roadmap/remaining-work-register.md`.
- [ ] Launch mode is clear.
- [ ] Manual operations are documented for unfinished automation.
- [ ] Release owner accepts the current product boundary.

No-go when:

- [ ] Billing automation is required but not implemented.
- [ ] Marketplace ZIP generation is required but not implemented.
- [ ] Automated update application is required but not implemented.
- [ ] Automated restore is required but not implemented.
- [ ] Full parent/student portals are part of the offer but not complete.
- [ ] Any P0 blocker remains unresolved.
