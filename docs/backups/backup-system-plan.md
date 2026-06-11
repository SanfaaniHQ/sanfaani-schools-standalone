# Backup System Plan

The backup manager now has a safe foundation for standalone, managed, and platform deployments. It records backup metadata, preflight results, verification status, retention policy state, logs, and manual restore plans.

## Implemented Foundation

- `config/backups.php` controls backup visibility, scope, retention, max archive metadata size, pre-update requirements, and shared-hosting-safe defaults.
- `backups`, `backup_items`, `backup_logs`, `backup_verifications`, and `backup_restore_plans` tables track backup metadata without exposing contents.
- Admin backup UI is feature-gated, deployment-aware, license-aware, and blocked in demo mode.
- Backup pages and maintenance download routes sit behind authenticated super-admin routes. Unauthorized users are redirected or forbidden before backup metadata or files are reached.
- Manual backup requests create metadata records for database exports, uploaded file roots, and sanitized configuration.
- Verification checks metadata presence, non-zero metadata size, checksum validity, required item records, manifest consistency, and unknown statuses fail closed.
- Retention marks/prunes expired backup records safely.
- Restore plans are manual guidance only and do not execute restore operations.
- Restore-plan views, backup requests, verifications, and retention pruning are audit logged. The restore-plan audit trail records that the plan is manual only.
- Update preflight can satisfy the backup requirement when a recent verified backup exists.
- Backup error messages shown in the UI are generic, while audit metadata is passed through secret and path redaction so full server paths are not exposed to operators.
- SQL backup downloads from system maintenance are private/no-store/nosniff responses and log request, success, and sanitized failure events.

## Not Implemented Yet

- Automated restore execution.
- External backup storage orchestration.
- Full database dump automation for shared hosting.
- Signed backup-download URLs for delegated non-owner access.
- Full backup archive creation from the web UI.
- Marketplace package backups or deployment automation.

## Shared-Hosting Constraints

- Shell access is optional. If `mysqldump` or shell execution is unavailable, use cPanel Backup Wizard or phpMyAdmin export.
- The web UI does not run destructive commands, migrations, restores, or package extraction.
- The default file scope is limited to safe uploaded-file roots.
- Excluded paths include `vendor`, `node_modules`, cache, sessions, logs, update packages, `public/build.zip`, and `.env`.
- Absolute sensitive server paths and secret values must not be displayed in the UI.

## Safety Rules

- Backups must never be committed to Git.
- Backups must not be stored in public web roots.
- Backup contents must not be exposed through public download routes until a signed, tenant-safe pattern exists.
- Managed backup tools must not touch non-target client data.
- Restore plan metadata must not claim a restore was performed.
- Pre-update backup checks must require a recent verified backup or block readiness.
- Download responses must remain private/no-store and must not be added to public admissions, API, or website routes.

## Restore Drill

Before any production restore, run a restore drill in a staging or local copy:

- import the database backup into the test database only;
- restore uploaded files into the test storage path;
- confirm login, owner dashboard, school dashboard, admissions, results, CBT, branding, report cards, scheduler, queue, mail, system health, and update preflight;
- record findings for Sanfaani support;
- never use production as the first restore test.

## Support Escalation

Escalate to Sanfaani support before production restore work when the backup status is not verified, the target database is unclear, the backup belongs to another school, the app version changed, or data loss is possible. Share backup reference IDs and sanitized screenshots only. Do not send `.env`, database passwords, license keys, API tokens, or SQL backup files through ordinary chat.
