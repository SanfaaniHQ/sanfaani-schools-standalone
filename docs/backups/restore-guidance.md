# Restore Guidance

This restore guidance is manual. Sanfaani Schools does not execute restore operations from the backup manager foundation.

## Before Restoring

- Confirm the target school or platform environment.
- Confirm PHP, Laravel, database, and storage compatibility.
- Confirm a recent verified backup record exists.
- Create a fresh pre-restore backup of the current production database and uploaded files.
- Place the application in maintenance mode through Laravel CLI or the hosting control panel.
- Keep `.env`, database passwords, API tokens, and license keys out of the backup UI.
- Contact Sanfaani support before production restore work when data loss is possible.

## Database Restore

- On shared hosting, use cPanel Backup Wizard or phpMyAdmin.
- Restore into the intended database only after confirming the target.
- Review migration notes before importing data created by an older release.
- Do not run migrations automatically from the web UI.

## Uploaded Files Restore

- Restore uploaded files only from approved user-uploaded storage paths.
- Do not overwrite `vendor`, `node_modules`, cache, sessions, logs, or temporary folders.
- Do not restore `public/build.zip` from application backup metadata.

## After Restoring

- Confirm login, owner dashboard, school dashboard access, admissions, result workflows, CBT access, branding, report cards, and public result checker pages.
- Confirm uploaded files and public storage links.
- Confirm scheduler, queue, mail, system health, and update preflight.
- Review application logs through the hosting panel.
- Remove maintenance mode after smoke checks pass.

## Restore Drill

Run a restore drill in staging or a local copy before production:

1. Copy the application files and database to the test environment.
2. Import the backup into the test database only.
3. Restore uploaded files into the test storage path.
4. Confirm students, staff/users, classes, subjects, sessions, terms, admissions, results, CBT, branding, report cards, scheduler, queue, mail, and health checks.
5. Document gaps before any production restore.

Never use production as the first restore test.

The backup manager records restore plan metadata only. It does not import databases, copy files, or perform rollback actions.
