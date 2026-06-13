# Support Runbooks

This page is the support map for Sanfaani Schools Standalone. It connects module runbooks, support triage, release handoff, and the product boundary docs into one support-ready knowledge base.

## Purpose

Use these runbooks when supporting one private school installation, a managed standalone deployment, or a buyer preparing a standalone installation. Laravel remains the source of truth for school data, license state, update readiness, backup metadata, and support diagnostics.

## Access

- School Admins handle daily school operations inside the current school workspace.
- Accountants handle finance only where the finance role feature is enabled.
- Teachers handle assigned attendance, LMS, CBT links, and live classes only where assigned.
- Super Admin or local owner users handle installer, license, update, backup, system health, and global support diagnostics.
- Sanfaani support should view school data only through the intended school context and should not bypass tenant boundaries.

## Runbook Index

- [Issue Triage](issue-triage.md)
- [Backup And Restore Runbook](backup-restore-runbook.md)
- [Update Runbook](update-runbook.md)
- [Installer And License Runbook](installer-license-runbook.md)
- [Offline Sync Runbook](offline-sync-runbook.md)
- [Live Class Runbook](live-class-runbook.md)
- [Finance Support Runbook](finance-support-runbook.md)
- [LMS And CBT Runbook](lms-cbt-runbook.md)
- [Communications Runbook](communications-runbook.md)
- [Branding Runbook](branding-runbook.md)
- [Reports Runbook](reports-runbook.md)
- [Security And Privacy Runbook](security-privacy-runbook.md)
- [Release Handoff Checklist](release-handoff-checklist.md)
- [Standalone Product Support Overview](../standalone/product-support-overview.md)

## Normal Support Workflow

1. Classify the issue with [Issue Triage](issue-triage.md).
2. Confirm the active school, user role, affected module, environment, and time window.
3. Collect screenshots that hide student private data, credentials, license keys, backup paths, and payment secrets.
4. Check the module runbook for first checks and safe commands.
5. Verify backup status before any update, restore, migration, import, or risky maintenance.
6. Escalate to engineering when the issue touches data loss, tenant boundaries, protected files, update package internals, restore plans, or unclear license state.

## Support-Safe Commands

These commands are safe because they inspect state or run tests. They still need normal shell access and the correct project path.

```bash
php artisan route:list
php artisan test
php artisan test --filter=Standalone
php artisan test --filter=Installer
php artisan test --filter=License
php artisan test --filter=Update
php artisan test --filter=Backup
php artisan schedule:list
php artisan standalone:status
php artisan deployment:check-readiness
php artisan performance:audit
php artisan security:audit
php artisan release:check-readiness
```

Use `npm run build` only in a controlled development, staging, or release-prep environment where Node dependencies are already installed and the build output is expected to change.

## Product Boundaries

- Standalone means one private school installation, not the public SaaS customer-acquisition flow.
- Offline support is selected browser/PWA offline capture and sync, currently attendance-focused.
- Pending browser attendance records are invisible to Laravel and support until the browser syncs.
- Live classes support manual provider links and provider abstraction metadata, not Google Meet, Zoom, or Microsoft Teams API automation.
- Updates are guided local package review and preflight checks, not auto-download or destructive auto-apply.
- Installer and license pages are local/commercial readiness foundations, not SaaS billing, payment gateway enforcement, or a remote license server.
- Branding supports white-label school branding inside the Laravel product, not a Next.js public school website.
- Reports provide a Reports Center and linked module reports, not a custom BI builder.
- Dependency vulnerabilities belong to a later dependency/security audit task.

## What Support Should Not Do

- Do not ask for raw `.env` files, database passwords, app keys, license keys, API tokens, SQL dumps, or full backups in normal chat.
- Do not change production data by guesswork.
- Do not run destructive database, Git, filesystem, dependency, or reset operations as part of ordinary support.
- Do not clear browser storage when offline attendance records may still be pending.
- Do not promise automation that is documented as deferred.

## Related Docs

- [Support Playbook](support-playbook.md)
- [Standalone Buyer Support Playbook](standalone-buyer-support-playbook.md)
- [Demo Support Playbook](demo-support-playbook.md)
- [Production Launch Readiness](../roadmap/production-launch-readiness.md)
- [Remaining Work Register](../roadmap/remaining-work-register.md)
