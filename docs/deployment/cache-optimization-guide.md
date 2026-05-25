# Cache Optimization Guide

This guide documents safe cache choices for shared hosting, VPS, cloud, managed, and marketplace deployments.

## Shared Hosting

- Use `file` cache unless the host provides Redis or Memcached.
- Use `file` or `database` sessions.
- Keep `storage` and `bootstrap/cache` writable.
- Do not clear or rebuild caches from public web requests.
- Run cache commands manually during deployment windows.

## VPS And Cloud

- Redis is recommended where supported.
- Use managed cache services only when credentials are stored securely in environment variables.
- Run optimization as part of a deployment pipeline after `.env` is finalized.

## Safe Commands

Run only during a reviewed deployment window:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Rollback or troubleshooting may require:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

These commands are deployment actions and are intentionally not run by the performance diagnostics UI.

## Route Cache Notes

- Avoid closure routes in production routes that must be cached.
- Re-run route cache after changing routes.
- Validate with `php artisan route:list`.

## Config Cache Notes

- Never cache config before `.env` values are correct.
- Do not expose `.env` values in diagnostics, logs, or screenshots.

## View Cache Notes

- View cache improves shared-hosting response time.
- Clear/rebuild after Blade updates.
