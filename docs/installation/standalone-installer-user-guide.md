# Standalone Installer User Guide

Standalone mode is for one school portal installed on its own hosting. The `/install` flow explains checks and collects safe setup information, but it does not replace hosting configuration.

## Before Opening `/install`

Prepare these items first:

- Domain or subdomain.
- Hosting account with PHP 8.3 or newer.
- Laravel-compatible project upload.
- Domain document root pointing to Laravel `public`, or a safe public-folder mapping.
- MySQL or MariaDB database.
- Database credentials: name, username, password, host, and port.
- `.env` created from `.env.example` and filled with hosting values.
- `SANFAANI_DEPLOYMENT_MODE=single_school`, `SANFAANI_INSTALLER_ENABLED=true`, and `SANFAANI_INSTALLED=false`.
- `SANFAANI_DATABASE_NAME_GUARD_ENABLED=false` for cPanel/marketplace installs so normal database names such as `swifarpx_fazportal`, `client_school_portal`, or `portal_db` are accepted.
- Writable `storage` and `bootstrap/cache` folders.
- Security key in `.env`.
- Email settings if school email will be enabled.

## The `/install` Flow

1. Open `https://your-domain.example/install`.
2. Read the welcome page to confirm you are setting up the correct school portal.
3. Review PHP requirements.
4. Review folder permissions.
5. Review database connection and table readiness.
6. Review portal configuration values. The installer does not write `.env` for you.
7. Confirm the security key.
8. Confirm database tables are complete or ready to prepare safely.
9. Create the owner account.
10. Enter the school profile.
11. Review email settings.
12. Review everything.
13. Finalize the installation and write the install lock.

Before the install is complete, `https://your-domain.example/` should point to the setup flow and `/login` should redirect to `/install`. After completion, `/` should point to login or the portal flow, and `/install` should no longer be reusable.

After login, open `Admin -> Admissions` to copy the public admission form link, preview the form, and get website guidance. Add the form link to the school website, WhatsApp, SMS, email, or printed admission instructions.

## Shared Hosting Notes

On shared hosting or cPanel, your hosting provider may need to help with document root, PHP extensions, database credentials, file permissions, storage link, cron, and safe migration execution. The installer gives guidance, but hosting panels still control those items.

Standalone sync migrations use limited indexed string lengths and short index names so older cPanel MySQL key-length limits are supported.

## What The Installer Does Not Do

- It does not create cPanel databases automatically.
- It does not guarantee shell access.
- It does not write `.env` unless a separate safe mechanism is already supported.
- It does not activate licenses.
- It does not generate seller license keys or require `SANFAANI_LICENSE_SIGNING_KEY` to install, log in, or use normal customer activation.
- It does not run backups or restores.
- It does not apply updates.
- It does not generate marketplace packages or ZIP files.
- It does not replace Sanfaani managed setup.

## If You Get Stuck

Ask your hosting provider for the document root, PHP version, extension list, database credentials, writable folders, terminal or task-runner access, and SMTP details. If you prefer not to manage hosting setup, ask Sanfaani for managed installation.
