# Buyer Installation Checklist

Use this checklist for marketplace and direct single-school buyers.

## Before Upload

- Confirm hosting supports PHP 8.3+, Composer, MySQL or MariaDB, and required PHP extensions.
- Create a database and database user in the hosting panel.
- Prepare SMTP details if email sending is required.
- Confirm the domain or subdomain points to the Laravel `public` directory where possible.
- Keep license details ready.

## Upload And Configure

- Upload the package without `vendor`, `node_modules`, `.env`, logs, backups, or local databases.
- Copy `.env.marketplace.example` to `.env`.
- Set `APP_URL`, database values, SMTP values, and license mode.
- Run `php artisan key:generate`.
- Run the installer while `SANFAANI_INSTALLER_ENABLED=true` and `SANFAANI_INSTALLED=false`.
- Run migrations through the installer or CLI after review.
- Run `php artisan storage:link`.

## After Installation

- Activate or validate the license.
- Confirm school profile, admin login, result workflows, CBT access, and public result checker.
- Create an initial backup record and manual database export.
- Review update and backup docs before any future update.

## Buyer Responsibilities

The buyer must configure hosting, domain, database, SMTP, storage permissions, cron or queue settings, and license values.
