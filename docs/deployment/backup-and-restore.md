# Backup and Restore Strategy

Production backups are mandatory before deployment and before any operation that can change large amounts of data.

## When to Back Up

- Before first production deployment.
- Before every `php artisan migrate --force`.
- Before large CSV or Excel student/result imports.
- Before subscription, payment, scratch card, or plan changes.
- Before bulk result publishing or unpublishing.
- After a successful production launch.

## What to Back Up

- MySQL database export from cPanel phpMyAdmin.
- Application files.
- `storage/app/public` uploads, including platform and school logos.
- The production `.env` file, stored securely outside the public web root.
- Any private operational documents needed for restore.

Never expose backups publicly. Do not place SQL dumps, `.env`, compressed project backups, logs, or private uploads under Laravel `public`.

## Retention

- Daily backups for 7 days.
- Weekly backups for 4 weeks.
- Monthly backups for 3 months.

Adjust retention based on storage, legal, and school contract requirements.

## cPanel Backup Process

1. Open cPanel and export the database from phpMyAdmin.
2. Download or archive the app files outside public access.
3. Back up `storage/app/public`.
4. Store `.env` in a secure private location.
5. Record the date, environment, app commit, and database name.

## Restore Process

1. Put the app in maintenance mode if available.
2. Restore application files.
3. Restore the database through phpMyAdmin import.
4. Restore `.env` securely.
5. Restore `storage/app/public`.
6. Run:

```bash
php artisan optimize:clear
php artisan storage:link
```

7. Test login, school dashboard, platform logo, school logo, result checker, scratch card validation, and email/log mail.

## Optional Safe Tooling

An artisan backup command can be added later if the host has `mysqldump`. If implemented, it must store backups under `storage/app/backups`, never under `public`, and fail safely when `mysqldump` is unavailable.
