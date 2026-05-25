# Backup System Plan

The backup manager now has a safe foundation for standalone, managed, and platform deployments. It records backup metadata, preflight results, verification status, retention policy state, logs, and manual restore plans.

## Implemented Foundation

- `config/backups.php` controls backup visibility, scope, retention, max archive metadata size, pre-update requirements, and shared-hosting-safe defaults.
- `backups`, `backup_items`, `backup_logs`, `backup_verifications`, and `backup_restore_plans` tables track backup metadata without exposing contents.
- Admin backup UI is feature-gated, deployment-aware, license-aware, and blocked in demo mode.
- Manual backup requests create metadata records for database exports, uploaded file roots, and sanitized configuration.
- Verification checks metadata presence, checksum where available, required item records, and unknown statuses fail closed.
- Retention marks/prunes expired backup records safely.
- Restore plans are manual guidance only and do not execute restore operations.
- Update preflight can satisfy the backup requirement when a recent verified backup exists.

## Not Implemented Yet

- Automated restore execution.
- External backup storage orchestration.
- Full database dump automation for shared hosting.
- Secure signed backup downloads.
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
