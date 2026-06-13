# Standalone Installer User Guide

Standalone mode is for technical buyers installing one school on their own hosting. The `/install` flow explains checks and collects safe setup information, but it does not replace hosting configuration.

## Before Opening `/install`

Prepare these items first:

- Domain or subdomain.
- Hosting account with PHP 8.3 or newer.
- Laravel-compatible project upload.
- Domain document root pointing to Laravel `public`, or a safe public-folder mapping.
- MySQL or MariaDB database.
- Database credentials: name, username, password, host, and port.
- `.env` created from `.env.example` and filled with hosting values.
- Writable `storage` and `bootstrap/cache` folders.
- Application key in `.env`.
- SMTP details if email will be enabled.

## The `/install` Flow

1. Open `https://your-domain.example/install`.
2. Read the welcome page to confirm you are in standalone single-school mode.
3. Review PHP requirements.
4. Review folder permissions.
5. Review database connection and migration readiness.
6. Review `.env` values. The installer does not write `.env` for you.
7. Confirm the application key.
8. Confirm migrations are complete or ready to run safely.
9. Create the owner admin account.
10. Enter the local school profile.
11. Capture SMTP intent for review.
12. Review everything.
13. Finalize the installation and write the install lock.

## Shared Hosting Notes

On shared hosting or cPanel, your hosting provider may need to help with document root, PHP extensions, database credentials, file permissions, storage link, cron, and safe migration execution. The installer gives guidance, but hosting panels still control those items.

## What The Installer Does Not Do

- It does not create cPanel databases automatically.
- It does not guarantee shell access.
- It does not write `.env` unless a separate safe mechanism is already supported.
- It does not activate licenses.
- It does not run backups or restores.
- It does not apply updates.
- It does not generate marketplace packages or ZIP files.
- It does not replace Sanfaani managed setup.

## If You Get Stuck

Ask your hosting provider for the document root, PHP version, extension list, database credentials, writable folders, terminal or task-runner access, and SMTP details. If you prefer not to manage hosting setup, ask Sanfaani for managed installation.
