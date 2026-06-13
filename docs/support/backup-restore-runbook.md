# Backup And Restore Runbook

## Purpose

Use this runbook when verifying backup readiness, preparing for an update, reviewing restore plans, or responding to possible data-loss risk.

## Access

Super Admin, local owner, or approved Sanfaani support can review backup metadata and restore plans. School Admins may confirm school workflow smoke tests after a backup or restore drill, but they should not receive raw backups or secrets.

## Normal Workflow

1. Confirm the environment, school, app version or commit, and hosting provider.
2. Confirm whether the task is routine backup review, pre-update readiness, restore drill, or production restore planning.
3. Review backup metadata and verification status in the admin backup area.
4. Confirm the database export and uploaded-file archive are stored outside public web roots.
5. Confirm a recent verified backup satisfies the update preflight window when an update is planned.
6. Create a manual restore plan for review. The backup foundation records plans; it does not execute restores.
7. Test restore steps in staging or a local copy before production restore work.

## Common Issues

- Backup metadata exists but verification is missing or failed.
- Database export is missing, empty, or stored under public web access.
- Uploaded-file backup includes unsafe folders such as dependencies, cache, sessions, logs, update packages, or `public/build.zip`.
- Restore target database is unclear.
- Update preflight fails because a verified backup is too old or missing.

## First Checks

- Open the backup detail page and confirm item status, verification status, checksum status, and warning count.
- Confirm the latest backup belongs to the intended environment and school.
- Confirm no `.env`, raw secrets, private backup paths, or SQL contents are displayed in the UI.
- Confirm `/admin/standalone/status` shows backup readiness when available.

## Safe Commands And UI Checks

```bash
php artisan test --filter=Backup
php artisan route:list
php artisan standalone:status
php artisan deployment:check-readiness
```

Use cPanel Backup Wizard, phpMyAdmin export, or the approved host backup tool when shell dumps are unavailable. Keep generated files private.

## What Support Should Not Do

- Do not treat metadata as proof of a restorable backup until verification passes.
- Do not share SQL files, `.env`, app keys, database passwords, license keys, or full backups through ordinary chat.
- Do not run destructive restore or database operations from a browser support session.
- Do not test a restore directly on production as the first restore drill.
- Do not restore dependency folders, cache, sessions, logs, update packages, or `public/build.zip`.

## Escalation Points

Escalate when backup status is unverified before an urgent update, a restore target is unclear, backup files may belong to another school, data loss is possible, protected files are involved, or a restore must be attempted in production.

## Data And Privacy Warnings

Backups can contain student, staff, payment, academic, and communication data. Store them privately, restrict access, and avoid sending backup contents to support unless an approved secure channel and owner approval exist.

## Backup And Security Reminders

Verify a backup before updates, imports, migrations, mass result publication, restore work, or hosting migration. A restore plan is not the same as a completed restore.

## Related Docs

- [Backup System Plan](../backups/backup-system-plan.md)
- [Pre-Update Backup Checklist](../backups/pre-update-backup-checklist.md)
- [Restore Guidance](../backups/restore-guidance.md)
- [Shared-Hosting Backup Guide](../backups/shared-hosting-backup-guide.md)
- [Backup And Restore Strategy](../deployment/backup-and-restore.md)
