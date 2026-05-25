# Performance Audit Checklist

Use this checklist before marketplace handover, single-school launch, managed client launch, update packages, and large backup windows.

## Hosting Limits

- PHP memory limit is at least 128 MB; 256 MB is preferred.
- `max_execution_time` is enough for short requests.
- Upload and post limits can accept planned package, image, and document uploads.
- Cron is available for `php artisan schedule:run`.
- Queue strategy is selected: sync/database for shared hosting, worker process for VPS/cloud.

## Laravel Runtime

- `APP_ENV=production` and `APP_DEBUG=false`.
- `storage` and `bootstrap/cache` are writable.
- `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`, and `LOG_CHANNEL` are intentional.
- Config, route, and view caches are generated only through controlled deployment steps.

## Application Workflows

- Dashboard lists use pagination.
- Exports have row limits or queue/chunk handling.
- Bulk communication is chunked.
- CBT and result workflows use indexed school/session/term/status columns.
- Backup and update package screens show metadata only.

## Files And Assets

- Built assets exist in `public/build`.
- `public/build.zip` is not treated as a runtime artifact.
- Uploaded files are size-limited.
- Logs, cache, sessions, backups, private storage, `.env`, `vendor`, and `node_modules` are excluded from packaging.

## Database

- Review school-scoped query filters.
- Review result, CBT, scratch card, communication, support, marketing, update, and backup indexes.
- Add indexes only after verifying query patterns and existing migrations.

## Commands

```bash
php artisan deployment:check-readiness
php artisan performance:audit
```

Both commands are read-only readiness checks.
