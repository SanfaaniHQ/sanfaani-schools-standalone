# Shared-Hosting Performance Hardening

This guide prepares Sanfaani Schools for Namecheap, cPanel, marketplace, managed, and single-school deployments without requiring shell access or destructive production changes.

## Constraints

- Shared hosting may cap PHP memory, execution time, upload size, cron frequency, and background workers.
- Web requests must stay short. Long imports, bulk messages, exports, backups, updates, and PDF generation should be queued, chunked, or handled manually.
- Do not rely on shell-only workflows for the web wizard. cPanel and Namecheap users may need manual File Manager, phpMyAdmin, and Cron Jobs steps.

## Safe Defaults

- `SANFAANI_PERFORMANCE_MODE=shared_hosting`
- `SANFAANI_SHARED_HOSTING_SAFE_MODE=true`
- `SANFAANI_DEFAULT_PAGE_SIZE=25`
- `SANFAANI_MAX_EXPORT_ROWS=5000`
- `SANFAANI_BULK_OPERATION_CHUNK_SIZE=100`
- `SANFAANI_QUEUE_SYNC_FALLBACK=true`
- `SANFAANI_LOG_RETENTION_DAYS=14`

## Operational Rules

- Paginate dashboards and admin tables.
- Keep exports below the configured row limit unless queued.
- Process bulk communications in chunks.
- Keep update packages and backup archives below hosting upload limits.
- Store backups outside public folders.
- Never package `.env`, `vendor`, `node_modules`, logs, cache, sessions, private storage, or `public/build.zip`.
- Run cache/config/route/view optimization only during reviewed deployment steps.

## Diagnostics

Run:

```bash
php artisan performance:audit
```

The command is read-only. It reports memory, execution time, cache, queue, session, logs, writable paths, assets, backup/update cautions, and database index recommendations. It does not clear cache, run migrations, delete logs, create symlinks, or write files.

## Troubleshooting

- 500 errors after upload: check PHP version, extensions, `.env`, `APP_KEY`, writable `storage`, and `bootstrap/cache`.
- Timeouts during imports or exports: lower chunk size or move work to queue/cron.
- Missing assets: run the build step before upload and confirm `public/build` exists.
- Backup failures: use cPanel/phpMyAdmin manual exports and keep files outside public folders.
- Update preflight warnings: verify a recent backup and review migration notes manually.
