# v1 Known Limitations And Backlog

Release label: `v1.0.0-rc1`

This document prevents v1 from overpromising. Items below are known limitations, backlog items, or separately approved future work.

## Dependency Vulnerabilities

GitHub dependency vulnerabilities remain a separate maintenance/security backlog.

Do not fix dependency vulnerabilities inside the v1 production-readiness commit unless a separate dependency remediation scope is approved. Dependency upgrades may alter lockfiles, runtime behavior, and test results, so they need their own review, testing, and release note.

## Website Add-On

The Next.js school website repository is future work.

Current state:

- Laravel public pages and admissions/result links exist.
- Documentation defines a future separate Next.js website strategy.
- Laravel remains the source of truth.

Not included:

- No Next.js app in this Laravel repository.
- No separate website repo created in v1.
- No website-owned admissions database.

## Offline Support

Offline support is not a full offline school portal.

Current state:

- Attendance-focused browser/PWA offline capture and sync foundation exists.
- Offline sync monitor and support runbook exist.

Not included:

- Full offline LMS, finance, admissions, reports, or user-management workflows.
- Conflict-free multi-device full-school offline sync.
- Cloud sync transport as a complete production service.

## Live Classes

Live classes support manual links and provider abstraction.

Current state:

- Manual live-class provider is enabled by default.
- Provider classes exist for Google Meet, Zoom, and Microsoft Teams boundaries.

Not included:

- Real Google Meet API automation.
- Real Zoom API automation.
- Real Microsoft Teams API automation.
- Automatic meeting provisioning, token refresh, recording import, or attendance sync from external providers.

## Updates

The update system is guided/manual local package review.

Current state:

- Package metadata review, entitlement checks, preflight, backup-required policy, update logs, and rollback planning exist.

Not included:

- Online update server.
- Auto-download.
- Destructive auto-apply.
- Automatic migration execution from uploaded packages.
- Automatic rollback execution.

## Backup And Restore

Backup readiness and restore planning exist.

Not included:

- Automated restore execution from the UI.
- Guaranteed host-level backups.
- Full disaster recovery without a verified external backup location.

## Licensing And Billing

Installer/license are local/commercial readiness foundations.

Not included:

- Remote license server.
- SaaS billing automation.
- Subscription payment gateway automation.
- Marketplace purchase verification service.

## Payments

Some payment-related settings and result/admissions payment surfaces exist as product foundations.

Not included:

- Full billing/payment gateway automation for SaaS or all school workflows.
- Complete automated reconciliation across every payment provider.

## Reports And Analytics

Reports pack and operational reporting exist.

Not included:

- Custom BI/report builder.
- Full executive analytics warehouse.
- Self-service drag-and-drop report designer.

## LMS

LMS foundation and LMS/CBT integration exist.

Not included:

- Advanced assignment submissions.
- Discussions/forums.
- Deep LMS analytics.
- Full content marketplace.
- Full virtual classroom automation.

## Communications

Communication center, templates, logs, notifications, bulk batches, and retries exist.

Not included unless separately configured and tested:

- External WhatsApp provider automation.
- External SMS provider automation.
- Guaranteed third-party delivery receipts across all providers.

## White-Label

Branding and white-label consolidation exist.

Not included:

- Automatic white-label domain provisioning.
- Full theme builder.
- Reseller portal.
- Automated reseller billing.

## ERP Boundary

Sanfaani Schools Standalone v1 is a school management product, not a full ERP.

Not included:

- HR/payroll suite.
- Procurement/inventory suite.
- General ledger accounting beyond school fee/accounting foundations.
- Multi-branch enterprise ERP workflows unless separately scoped.

## Production Deployment Backlog

Every real deployment still needs:

- Server/domain/SSL setup.
- Production `.env`.
- Production database.
- Storage permissions.
- Mail setup.
- Scheduler/cron.
- Queue/cache/session strategy.
- Backup location and first verified backup.
- Admin account security.
- Demo/test data removal.
- Support handoff and escalation owner.
