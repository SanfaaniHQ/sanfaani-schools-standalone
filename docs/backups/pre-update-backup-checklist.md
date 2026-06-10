# Pre-Update Backup Checklist

Use this checklist before planning any guided update.

## Required

- Create backup metadata in the backup manager.
- Export the database manually when shell dumps are unavailable.
- Confirm uploaded-file backup scope excludes unsafe folders.
- Verify the backup record.
- Confirm backup request, verification, and restore-plan review entries are present in audit or backup logs.
- Confirm the update preflight backup requirement passes.

## Review

- Read update release notes.
- Read migration notes and database changes.
- Prepare a maintenance window.
- Confirm rollback means restoring a verified backup manually.

## Not Allowed From The Web UI

- Running migrations automatically.
- Extracting update packages into application folders.
- Running destructive shell commands.
- Restoring databases or files.
- Displaying `.env` contents or sensitive absolute paths.
- Serving backup files from public routes or public storage disks.
