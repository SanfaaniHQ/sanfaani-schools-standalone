# Cloud Hosting Deployment

This guide covers generic Laravel cloud platforms, container hosts, managed app platforms, and cloud VPS providers.

## Platform Assumptions

- The platform supports PHP 8.3+.
- Environment variables are configured through a secrets manager or platform dashboard.
- Database is managed MySQL/MariaDB or a compatible service.
- Queues and scheduler can run through workers, cron jobs, or platform jobs.

## Environment Variables

Use platform secrets for `.env` values. Never bake secrets into images or Git. Required values include app URL, app key, database, mail, queue, license, update, backup, and deployment mode settings.

## Managed Database

- Create the managed database.
- Restrict network access.
- Store credentials in the platform secret store.
- Run migrations only after a verified backup and release review.

## Object Storage

If using S3-compatible storage later, configure `FILESYSTEM_DISK=s3` and keep credentials in secrets. Confirm public URLs and CORS rules before enabling school uploads. The backup foundation still records metadata only unless external storage backup orchestration is added later.

## Build Pipeline

Typical pipeline:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run migrations as a controlled release step, not automatically on every container boot.

## Queue Worker

Run a separate worker process:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

Use Redis/database queue depending on platform support.

## Scheduler

Use a platform cron job to run:

```bash
php artisan schedule:run
```

every minute.

## Security

- Use platform secrets.
- Keep debug off.
- Use HTTPS-only URLs.
- Lock down database access.
- Keep logs private.
- Do not expose storage, `.env`, backups, update packages, or private uploads.

## Updates And Rollback

Use release artifacts and platform rollback where available, plus verified database/storage backups for data rollback. The update manager foundation does not apply code patches automatically.
