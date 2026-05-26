# Commercialization Acceptance Checklist

Use this checklist for final acceptance of the commercialization foundation. It confirms that documentation exists, validation commands are recorded, and planned work is not described as complete.

## Final Documentation Acceptance

- [ ] `docs/roadmap/final-commercialization-roadmap.md` exists.
- [ ] `docs/roadmap/commercialization-acceptance-checklist.md` exists.
- [ ] `docs/roadmap/product-mode-capability-matrix.md` exists.
- [ ] `docs/roadmap/remaining-work-register.md` exists.
- [ ] `docs/roadmap/production-launch-readiness.md` exists.
- [ ] `docs/roadmap/risk-register.md` exists.
- [ ] `docs/roadmap/next-30-60-90-days.md` exists.
- [ ] `docs/roadmap/final-executive-summary.md` exists.
- [ ] `docs/README.md` references the final roadmap.
- [ ] `docs/SUMMARY.md` references every roadmap document.
- [ ] `docs/initial-documentation-backlog.md` records the roadmap docs as launch documentation.
- [ ] `docs/changelog/CHANGELOG.md` includes the final roadmap documentation entry.

## Honesty And Scope Acceptance

- [ ] The roadmap separates implemented production features from foundations.
- [ ] The roadmap states that full billing/payment workflow remains planned.
- [ ] The roadmap states that marketplace ZIP generation remains planned.
- [ ] The roadmap states that real update download/application remains planned.
- [ ] The roadmap states that automated restore execution remains planned.
- [ ] The roadmap states that full parent/student portal workflows remain planned where incomplete.
- [ ] Sales, marketplace, and buyer copy do not overstate automation.
- [ ] No runtime features were added in the roadmap step.
- [ ] No app logic was changed in the roadmap step.
- [ ] No database migrations were modified in the roadmap step.
- [ ] `public/build.zip` was not modified by the roadmap step.
- [ ] No release ZIPs were generated.
- [ ] No deployment automation was run.
- [ ] No business workflows were changed.

## Final Validation Commands

Run and record:

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

## Go/No-Go Launch Checklist

- [ ] All final validation commands pass.
- [ ] Production `.env` values are confirmed for the target mode.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL` is correct.
- [ ] Database, storage, cache, queue, scheduler, mail, license, update, and backup settings are reviewed.
- [ ] A fresh manual backup exists before launch or update.
- [ ] Rollback plan has a named owner.
- [ ] Support owner and escalation contacts are named.
- [ ] Security and tenant isolation risks are accepted by the release owner.
- [ ] Marketplace, managed, single-school, SaaS, and white-label offers match the implemented scope.
- [ ] Final release approver records go/no-go.

## Marketplace Readiness Checklist

- [ ] `.env.marketplace.example` is buyer-safe.
- [ ] Include/exclude list excludes `.env`, logs, backups, `vendor`, `node_modules`, private storage, and `public/build.zip`.
- [ ] Buyer installation checklist is complete.
- [ ] Marketplace package validation command passes.
- [ ] Listing copy avoids claims of one-click deployment, full billing, automated update application, and automated restore.
- [ ] Screenshots and demo script match the current product.
- [ ] Marketplace ZIP generation is treated as planned until implemented.

## Managed Client Readiness Checklist

- [ ] `SANFAANI_DEPLOYMENT_MODE=managed` is configured.
- [ ] `SANFAANI_LICENSE_MODE=managed_contract` or another approved managed license is configured.
- [ ] Hosting, domain, database, SMTP, queue, cron, storage, backup, and update responsibilities are recorded.
- [ ] Managed support contact and escalation policy are recorded.
- [ ] Managed backup and update foundations are described as guided/manual unless automation is contracted and implemented.
- [ ] Handover checklist is complete.

## Single-School Buyer Readiness Checklist

- [ ] `SANFAANI_DEPLOYMENT_MODE=single_school` is configured.
- [ ] Installer prerequisites are satisfied.
- [ ] License activation or validation path is documented.
- [ ] Buyer has hosting, domain, database, SMTP, storage, queue, and backup responsibilities.
- [ ] Update and backup features are described as guided foundations.
- [ ] Buyer docs avoid claims of automated restore or update application.

## SaaS Readiness Checklist

- [ ] `SANFAANI_DEPLOYMENT_MODE=saas` is configured.
- [ ] Platform school management and feature overrides are tested.
- [ ] Tenant isolation and authorization tests pass.
- [ ] Demo, onboarding, marketing, support, and diagnostics are tested.
- [ ] Full billing/payment automation is not promised unless implemented later.
- [ ] Manual billing operations are documented if SaaS launches before billing automation.

## White-Label Readiness Checklist

- [ ] White-label license entitlement is confirmed.
- [ ] `white_label_branding` feature access is verified.
- [ ] Logo, favicon, colors, email footer, report footer, and public identity are reviewed.
- [ ] Marketplace or managed white-label buyer docs are aligned to the current foundation.
- [ ] Domain provisioning, reseller tooling, and full theme builder remain planned unless implemented later.

## Support Readiness Checklist

- [ ] Support email, phone, WhatsApp, and escalation owner are confirmed.
- [ ] Support playbook is reviewed.
- [ ] Common troubleshooting docs are linked in buyer/operator docs.
- [ ] Incident response owner and rollback owner are named.
- [ ] Post-launch monitoring plan includes logs, mail delivery, result checker, login, scratch cards, CBT, and queues.

## Release Readiness Checklist

- [ ] Changelog entry is present.
- [ ] Release notes or release summary is prepared.
- [ ] Final preflight checklist is complete.
- [ ] Protected dirty files are reviewed and excluded unless explicitly in scope.
- [ ] Backup-before-release checklist is complete.
- [ ] Rollback validation workflow is reviewed.
- [ ] Known risks are accepted or assigned.
- [ ] Final validation results are attached to the release record.

## Final Acceptance Checklist

- [ ] Final roadmap docs exist.
- [ ] Acceptance checklist exists.
- [ ] Product mode capability matrix exists.
- [ ] Remaining work register exists.
- [ ] Production launch readiness doc exists.
- [ ] Risk register exists.
- [ ] 30/60/90 day roadmap exists.
- [ ] Final executive summary exists.
- [ ] Docs index and summary are updated.
- [ ] Roadmap is honest about planned versus implemented items.
- [ ] Final validation commands are documented.
- [ ] No runtime features were added.
- [ ] Protected dirty files were not touched.
- [ ] Tests pass.
