# Restore Guidance

This restore guidance is manual. Sanfaani Schools does not execute restore operations from the backup manager foundation.

## Before Restoring

- Confirm the target school or platform environment.
- Confirm PHP, Laravel, database, and storage compatibility.
- Confirm a recent verified backup record exists.
- Place the application in maintenance mode through Laravel CLI or the hosting control panel.
- Keep `.env`, database passwords, API tokens, and license keys out of the backup UI.

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

- Confirm login, school dashboard access, result workflows, CBT access, and public result checker pages.
- Review application logs through the hosting panel.
- Remove maintenance mode after smoke checks pass.

The backup manager records restore plan metadata only. It does not import databases, copy files, or perform rollback actions.
