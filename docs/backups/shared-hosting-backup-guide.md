# Shared-Hosting Backup Guide

This guide is for cPanel, Namecheap, and similar hosts where shell access may be limited.

## Database

- Use cPanel Backup Wizard or phpMyAdmin export.
- Prefer SQL export with structure and data.
- Store the export outside `public_html` when possible.
- Do not paste database passwords or `.env` contents into Sanfaani Schools.
- If using the system-maintenance SQL export, download only on a trusted device and store the file privately. The download route is authenticated, private/no-store, and audited.

## Uploaded Files

- Back up safe uploaded-file folders such as `storage/app/public`, `public/storage`, or approved upload folders.
- Exclude `vendor`, `node_modules`, `storage/framework/cache`, `storage/framework/sessions`, `storage/logs`, update packages, temporary folders, and `public/build.zip`.
- Keep archive names descriptive and store them outside public web roots.

## Configuration

- Record only sanitized configuration metadata.
- Never export raw `.env` through the web UI.
- Keep APP_KEY, database credentials, mail passwords, payment keys, and license keys in your password manager or hosting vault.

## Verification

- Check that the database export exists.
- Confirm the export size is greater than zero.
- Check uploaded-file archives are readable.
- Confirm backup metadata in Sanfaani Schools shows the expected items.
- Use the backup verification action before a guided update preflight.
- Review manifest consistency, checksum status, and warning item counts on the backup detail page.

## Scheduled Backup

Where cron is available, schedule the existing database backup command only after confirming storage is private:

```bash
php artisan backup:database --keep=10
```

For Laravel scheduler monitoring, keep `php artisan schedule:run` configured separately. Do not schedule restore commands.

## What Not To Do

- Do not place SQL backups, uploaded-file archives, `.env`, or private storage under `public_html`.
- Do not test restore directly on production first.
- Do not email SQL backups or paste credentials into support chats.
- Do not restore `vendor`, `node_modules`, cache, sessions, logs, or `public/build.zip`.
