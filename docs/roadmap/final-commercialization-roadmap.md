# Final Commercialization Roadmap

This roadmap closes the commercialization foundation track for Sanfaani Schools. It summarizes what is implemented, what is ready to operate with manual controls, what is foundation-ready only, and what must still happen before a production launch.

## Executive Summary

Sanfaani Schools now has the documentation, configuration, feature-gating, deployment-mode, licensing, demo, onboarding, marketing, update, backup, marketplace, security, branding, UI, and release-readiness foundations needed to prepare a commercial launch. The platform can be positioned for SaaS, `single_school`, managed, white-label, demo, trial, and marketplace buyer package paths.

The current state is not a fully automated commercial platform. Full billing/payment automation, marketplace ZIP generation, real update download/application, automated restore execution, marketplace API sync, and complete parent/student portal workflows remain planned. Production launch should proceed only after the final validation commands pass, protected dirty files are excluded, and launch blockers are accepted or resolved.

## Current Implementation Status

| Area | Status | Notes |
| --- | --- | --- |
| Core school workflows | Ready for controlled production validation | School setup, users, classes, subjects, students, results, scratch cards, public school pages, CBT access, support, and communication workflows exist in the app. |
| Deployment modes | Foundation-ready | `saas`, `single_school`, and `managed` behavior is mapped through config and services. |
| License modes | Foundation-ready | `subscription`, `annual`, `lifetime`, `managed_contract`, `white_label`, `trial`, and `demo` modes are defined and validated locally. |
| Feature gating | Foundation-ready | Commercial and module features are resolved through feature flags, deployment behavior, school context, overrides, subscriptions, and entitlements. |
| Installer | Foundation-ready | Single-school and managed installer flow exists with safe requirements, permission, database, admin, school, SMTP, and final review stages. |
| Licensing | Foundation-ready | Activation, validation, entitlement checks, masking, hashing, and audit models exist. Remote license server behavior is still stubbed. |
| Demo | Foundation-ready | Demo requests, sessions, credentials, activity, expiry, and role-based demo setup exist. |
| Onboarding | Foundation-ready | Role-based checklists, progress tracking, events, visibility rules, and dashboard widgets exist. |
| Marketing | Foundation-ready | Lead activity, scoring, enrollment, sales task, unsubscribe, and compliance foundations exist. Full sales automation is still planned. |
| Updates | Foundation-ready only | Package metadata, manifest validation, preflight, entitlement checks, logs, and rollback planning exist. Real update download, extraction, patching, and application remain planned. |
| Backups | Foundation-ready only | Metadata, verification, retention, restore plans, and pre-update readiness exist. Automated restore and full archive execution remain planned. |
| Marketplace | Documentation and validation foundation-ready | Packaging docs, include/exclude rules, buyer checklist, and non-destructive validation command exist. Marketplace ZIP generation is not implemented. |
| Security | Foundation-ready | Tenant isolation, authorization, production security audit, email safety, token safety, and secret redaction coverage are documented and tested. |
| Deployment | Foundation-ready | Namecheap, cPanel, VPS, cloud, shared-hosting, performance, and readiness guides exist with read-only diagnostic commands. |
| Branding and white-label | Foundation-ready | Deployment-aware branding, safe uploads, branding tokens, and white-label gates exist. Advanced reseller/domain automation remains planned. |
| Enterprise UI | Foundation-ready | Shared UI components and dashboard standards exist without changing business workflows. |
| Release readiness | Foundation-ready | Release docs, readiness command, risk docs, validation commands, and protected dirty file checks exist. |

## Product Modes Now Supported

| Mode | Current support | Not yet included |
| --- | --- | --- |
| SaaS | `SANFAANI_DEPLOYMENT_MODE=saas`, subscription/trial/demo license modes, platform school management, central settings, feature overrides, demo, onboarding, marketing, diagnostics, release checks. | Full automated billing, payment reconciliation, subscription dunning, and automated SaaS provisioning. |
| `single_school` | `SANFAANI_DEPLOYMENT_MODE=single_school`, standalone installer, local school settings, license activation, guided updates/backups foundations, branding, shared-hosting diagnostics. | Marketplace package generation, one-click deployment, automated update application, automated restore. |
| Managed | `SANFAANI_DEPLOYMENT_MODE=managed`, managed support, managed update/backup visibility, performance/security diagnostics, branding, white-label gates where licensed. | Contract-specific automation, managed backup orchestration, managed update deployment automation. |
| White-label | `SANFAANI_LICENSE_MODE=white_label`, `white_label_branding` gates, school-specific branding controls, safe logo/favicon storage, buyer docs. | Domain provisioning, reseller portal, full theme builder, marketplace-branded ZIP generation. |
| Demo | `SANFAANI_LICENSE_MODE=demo`, demo requests, demo sessions, demo credentials, expiry, activity tracking, role-based demo users. | Automatic demo reset beyond safe disabled defaults, full sales conversion automation. |
| Trial | `SANFAANI_LICENSE_MODE=trial`, onboarding visibility, demo/trial feature scopes, marketing lead scoring. | Trial billing conversion, automated renewal/payment flows. |
| Marketplace buyer package | Buyer docs, `.env.marketplace.example`, packaging plan, include/exclude list, validation checklist, `marketplace:validate-package`. | Final marketplace ZIP generation, marketplace API integration, one-click buyer deployment. |

## Commercial Foundations Completed

- Deployment mode and license mode foundation.
- Feature flag and module gating system.
- SaaS, single-school, and managed behavior separation.
- Standalone installer foundation.
- License activation, validation, entitlement, masking, hashing, and audit foundation.
- Demo request/session/credential/activity/expiry foundation.
- Role-based guided onboarding foundation.
- Lead nurturing, lead scoring, unsubscribe, and sales task foundation.
- Marketplace packaging documentation and validation foundation.
- White-label branding and buyer-readiness foundation.
- Release readiness and commercialization acceptance documentation.

## Technical Foundations Completed

- `DeploymentModeService`, `DeploymentBehaviorService`, and `FeatureAccessService`.
- Deployment behavior maps for route groups, widgets, and settings sections.
- Feature gates for commercial systems and school modules.
- Read-only deployment, performance, security, release, and marketplace readiness commands.
- Shared-hosting safe defaults for queues, cache, logs, exports, backups, and diagnostics.
- Enterprise UI components and dashboard layout standardization.
- Documentation architecture for `doc.sanfaani.net`.

## Security Foundations Completed

- Tenant isolation audit and authorization test coverage.
- School context and active role enforcement.
- Feature and deployment behavior middleware.
- License validation middleware for restricted commercial routes.
- Installer reinstall lock checks.
- Demo credential expiry and safe credential handling.
- Production security audit command and documentation.
- Outbound email safety, unsubscribe, secret redaction, token, and signed URL guidance.
- Protected dirty file awareness for release readiness.

## Deployment Foundations Completed

- Namecheap shared hosting and production launch guides.
- cPanel, VPS, cloud, queue, cron, storage link, file permission, and troubleshooting guides.
- Deployment readiness command and configuration checks.
- Performance audit command and shared-hosting performance hardening guide.
- Security audit command and production hardening guides.
- Marketplace buyer and managed client deployment guides.

## Documentation Foundations Completed

- Documentation index, summary, URL map, maintenance workflow, role maps, deployment-mode map, and changelog.
- Architecture docs for commercial architecture, deployment modes, feature flags, and tenant isolation.
- Buyer and operator docs for installer, licensing, demo, onboarding, marketing, updates, backups, marketplace, white-label, release, security, UI, and support.
- Final roadmap and acceptance documentation in `docs/roadmap/`.

## Foundation Status By Commercial Area

| Area | Production-ready now | Foundation-ready but not fully automated | Planned |
| --- | --- | --- | --- |
| Installer | Safe guided setup screens and readiness checks. | Shared-hosting friendly guidance and setup lock. | One-click marketplace installation automation. |
| Licensing | Local activation, validation, entitlement, audit, masking, hashing. | Offline grace and deployment/license mode gating. | Real remote license server sync and marketplace license sync. |
| Demo | Requests, sessions, role credentials, expiry, activity. | Sales handoff through marketing foundations. | Safe automated demo resets and full conversion sequences. |
| Onboarding | Role checklists, progress, events, visibility rules. | Dashboard widgets and demo/trial visibility. | Checklist builder UI and deeper automation listeners. |
| Marketing | Lead scoring, activity, sales tasks, unsubscribe compliance. | Email/WhatsApp gates and sequence metadata. | Provider-specific WhatsApp sending, full sales pipeline automation, billing conversion. |
| Updates | Metadata, preflight, entitlement, logs, rollback plans. | Guided update review only. | Real update downloads, extraction, patching, migrations, and deployment automation. |
| Backups | Metadata, verification, retention, restore plans. | Pre-update readiness with recent verified backup checks. | Automated restore, external storage orchestration, signed downloads, full archive creation. |
| Branding | Platform/school/managed/white-label branding controls. | Safe asset upload, color defaults, UI tokens. | Full theme builder, domain provisioning, reseller tooling. |
| UI | Shared components, dashboard standards, responsive docs. | Incremental page migration path. | Broader conversion of older screens as they are touched. |

## What Is Production-Ready Now

- Core Laravel app operation after a normal reviewed deployment.
- Existing school operations that already have routes, controllers, policies, and tests.
- Deployment, performance, security, release, and marketplace validation commands as read-only readiness tools.
- Documentation and checklist coverage for launch preparation.
- Manual commercial operation of demos, onboarding, lead follow-up, installer setup, local license validation, branding, release review, and support handover.

## Foundation-Ready But Not Fully Production-Automated

- SaaS billing visibility exists through feature gates and subscription routes, but full billing/payment workflow remains planned.
- Updates can be reviewed and preflighted, but real update application remains planned.
- Backups can be tracked and verified through metadata, but automated restore remains planned.
- Marketplace packaging can be validated, but final ZIP generation remains planned.
- White-label branding can be configured, but reseller/domain automation remains planned.
- Parent and student onboarding placeholders exist, but full parent/student portals remain planned.

## What Remains Planned

- Full billing/payment workflow, subscription invoicing, renewal reminders tied to billing, dunning, and payment reconciliation.
- Remote license server integration and marketplace license sync.
- Final marketplace ZIP generation and marketplace API submission.
- Real update downloads, extraction, code patching, migration execution, rollback execution, and deployment automation.
- Automated restore execution, external backup storage orchestration, signed downloads, and complete backup archive creation.
- Full parent and student portals.
- White-label domain provisioning, reseller console, and advanced theme builder.
- Contract-specific managed deployment automation.
- Production support SLAs, incident response ownership, and post-launch monitoring runbooks.

## Risk Register Summary

The detailed register is in `docs/roadmap/risk-register.md`.

| Risk | Level | Current mitigation |
| --- | --- | --- |
| Launching before full validation passes | High | Final validation command list and go/no-go checklist. |
| Overstating automation in sales materials | High | Acceptance docs separate ready, foundation-ready, and planned items. |
| Billing expectations exceed implementation | High | Billing is documented as planned unless manually operated. |
| Update application misunderstood as live | High | Update docs state metadata/preflight only. |
| Restore misunderstood as automated | High | Backup docs state restore plans are manual guidance only. |
| Marketplace buyer expects ZIP automation | Medium | Marketplace docs state package validation only. |
| Shared-hosting limits cause launch friction | Medium | Namecheap/cPanel guides and performance diagnostics. |
| Incomplete parent/student portals create sales friction | Medium | Portal scope documented as planned where incomplete. |

## Launch Blockers

- Any failure in the final validation commands.
- `APP_ENV`, `APP_DEBUG`, `APP_URL`, database, mail, queue, storage, cache, license, update, and backup settings not validated for the target environment.
- Protected dirty files included unintentionally, especially `database/migrations/2026_05_01_173857_create_result_publications_table.php` and `public/build.zip`.
- Missing manual backup before production deployment or update.
- Missing support contact, incident owner, rollback owner, and release approver.
- Sales or marketplace copy claiming full billing, marketplace ZIP generation, real update application, automated restore, or complete parent/student portals.
- Any tenant isolation, authorization, security audit, performance audit, or release readiness failure.

## Recommended Next 30 Days

- Run and record the full final validation set.
- Resolve all P0 launch blockers in `docs/roadmap/remaining-work-register.md`.
- Finalize production `.env`, SMTP, storage, queue, cache, cron, and backup responsibilities.
- Finalize sales copy so it matches the foundation/completed boundary.
- Run pilot school validation for school admin, teacher, result officer, accountant, public result checker, and CBT flows.
- Confirm support playbook, escalation contacts, and launch monitoring.

## Recommended Next 60 Days

- Implement remote license validation or document the manual license operating model.
- Define billing architecture before claiming SaaS billing automation.
- Harden managed deployment handover, backup ownership, and update ownership.
- Add marketplace asset screenshots, buyer FAQ, and install walkthrough.
- Expand onboarding and marketing listeners where they do not change billing commitments.
- Create production incident response and rollback drills.

## Recommended Next 90 Days

- Build full billing/payment automation if SaaS self-service launch is required.
- Build real update delivery only after backup/rollback guarantees are proven.
- Build automated restore only after secure storage and signed download rules are finalized.
- Build marketplace ZIP generation and release packaging automation.
- Build parent and student portal workflows if they are part of the paid offer.
- Build reseller/domain automation for white-label sales.

## Final Validation Commands

Run these before go/no-go approval:

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

- [ ] Final validation commands pass.
- [ ] Production environment values are reviewed and secrets are not committed.
- [ ] Protected dirty files are excluded from release scope unless explicitly resolved.
- [ ] Manual backup is completed and restore plan is documented.
- [ ] Security, performance, deployment, release, and marketplace readiness reports are reviewed.
- [ ] Buyer and operator docs match the exact launch offer.
- [ ] Sales copy avoids claims about unfinished automation.
- [ ] Support contacts and escalation paths are ready.
- [ ] Release approver records final go/no-go.

## Related Roadmap Documents

- `docs/roadmap/commercialization-acceptance-checklist.md`
- `docs/roadmap/product-mode-capability-matrix.md`
- `docs/roadmap/remaining-work-register.md`
- `docs/roadmap/production-launch-readiness.md`
- `docs/roadmap/risk-register.md`
- `docs/roadmap/next-30-60-90-days.md`
- `docs/roadmap/final-executive-summary.md`
