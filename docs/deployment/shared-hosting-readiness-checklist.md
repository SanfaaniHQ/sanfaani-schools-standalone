# Shared-Hosting Readiness Checklist

Use this before deploying to Namecheap, cPanel, or similar hosting.

## Hosting

- PHP 8.3+ selected.
- Required PHP extensions enabled.
- Domain or subdomain created.
- Document root points to Laravel `public` or the fallback public mapping is applied safely.
- Terminal access confirmed or fallback installation route planned.

## Files

- Clean package uploaded.
- `.env`, logs, backups, private storage, SQL dumps, `vendor`, `node_modules`, and `public/build.zip` excluded.
- `public/build` uploaded only if assets were intentionally built.

## Environment

- `.env` created from safe template.
- `APP_ENV=production`.
- `APP_DEBUG=false`.
- `APP_KEY` generated.
- Database, SMTP, license, update, and backup settings configured.

## Database

- MySQL database and user created.
- User privileges assigned.
- Migrations/import plan reviewed.
- Backup created before migration or import.

## Runtime

- `storage` writable.
- `bootstrap/cache` writable.
- Storage link or workaround ready.
- Cron strategy selected.
- Queue strategy selected.

## Security

- `.env` not public.
- Storage/logs/backups not public.
- HTTPS enabled.
- Admin password unique.
- License validation enabled.
