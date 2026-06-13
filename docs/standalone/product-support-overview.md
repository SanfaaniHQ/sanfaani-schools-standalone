# Standalone Product Support Overview

This overview defines the support boundary for Sanfaani Schools Standalone after Stage 23 documentation hardening.

## Product Position

Sanfaani Schools Standalone is a Laravel school management product for one private school installation. Laravel is the source of truth for school operations, user access, academic data, payments recorded inside the app, LMS/CBT links, communications logs, branding settings, backup metadata, update readiness, installer state, and local license state.

## What Is Support-Ready

- Standalone private-school boundary and dashboards.
- Admissions foundations and school operations docs.
- Attendance, attendance reports, and attendance-focused browser offline capture/sync.
- Offline sync monitor for server-known attendance sync receipts and safe attempt summaries.
- Fees/accounting foundation, finance reports, finance audit review, and selected exports.
- Import/export tools for supported CSV workflows.
- LMS classrooms, materials, resources, and safe LMS-CBT links.
- Manual live classes and provider abstraction metadata.
- Communication Center, templates, notification logs, and existing bulk communication boundary.
- Branding and white-label school settings.
- Reports Center with safe links to module reports.
- Installer and local license diagnostics.
- Guided update package review and preflight checks.
- Backup metadata, verification, retention state, and manual restore-plan foundation.
- System health, scheduler, readiness, and support-safe diagnostics.

## Boundaries Support Must Preserve

- Offline support is not full portal offline mode. It is selected browser/PWA offline capture and sync, currently attendance-focused.
- Browser-local pending attendance records are not visible to Laravel, admins, backups, or support until the browser syncs.
- Live classes require internet and use manual meeting links. Google Meet, Zoom, and Microsoft Teams API automation is deferred.
- Communication logs and templates do not mean WhatsApp or SMS provider automation is complete.
- Finance supports fee items, assignments, invoices, manual payments, reports, audit, and selected exports. It is not a full ERP.
- School-fee payment gateway automation is not completed unless a future implementation and QA prove it.
- Installer and license hardening do not provide SaaS billing, payment gateway enforcement, customer billing portal, online activation server, or remote license server.
- Guided updates do not auto-download, extract, apply, run migrations, or run automatic recovery actions.
- Backup foundations do not execute automated production restore.
- Branding does not create a Next.js public school website, DNS provisioning, SSL automation, or full theme builder.
- Reports Center is not a custom BI builder, public report portal, scheduler, or cross-school analytics tool.
- GitHub dependency vulnerabilities are tracked separately as a later dependency/security audit task.

## Support Entry Points

- Support map: [Support Runbooks](../support/support-runbooks.md)
- Triage: [Issue Triage](../support/issue-triage.md)
- Handoff: [Release Handoff Checklist](../support/release-handoff-checklist.md)
- System health: [Standalone System Health And Scheduler Monitoring](system-health-and-scheduler-monitoring.md)
- Update boundary: [Update System Finalization](update-system-finalization.md)
- Installer/license boundary: [Installer License Final Hardening](installer-license-final-hardening.md)
- Offline boundary: [Offline Attendance Capture Pilot](offline-attendance-capture.md)

## Safe Diagnostic Commands

```bash
php artisan route:list
php artisan schedule:list
php artisan standalone:status
php artisan deployment:check-readiness
php artisan performance:audit
php artisan security:audit
php artisan release:check-readiness
```

Use focused test filters during support or release review:

```bash
php artisan test --filter=Standalone
php artisan test --filter=Installer
php artisan test --filter=License
php artisan test --filter=Update
php artisan test --filter=Backup
php artisan test --filter=Reports
php artisan test --filter=Dashboard
php artisan test --filter=Health
```

## Support Evidence Rules

Collect the least evidence needed. Sanitize screenshots and logs. Do not collect raw `.env`, app keys, database passwords, mail passwords, license keys, provider tokens, sync tokens, payment secrets, SQL dumps, full backups, update package payloads, or private server paths in ordinary support channels.

## Recommended Support Path

1. Start with [Issue Triage](../support/issue-triage.md).
2. Use the module runbook for first checks.
3. Verify backup status before risky maintenance.
4. Escalate when a tenant boundary, data-loss risk, protected file, update package, restore plan, or license state is unclear.
