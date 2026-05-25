# Shared-Hosting Backup Guide

This guide is for cPanel, Namecheap, and similar hosts where shell access may be limited.

## Database

- Use cPanel Backup Wizard or phpMyAdmin export.
- Prefer SQL export with structure and data.
- Store the export outside `public_html` when possible.
- Do not paste database passwords or `.env` contents into Sanfaani Schools.

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
- Check uploaded-file archives are readable.
- Confirm backup metadata in Sanfaani Schools shows the expected items.
- Use the backup verification action before a guided update preflight.
