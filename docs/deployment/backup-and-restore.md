# Backup and Restore Checklist

Use this before migrations, pilot onboarding, and any production deployment.

## What to Back Up

- MySQL database export from cPanel phpMyAdmin or `mysqldump`.
- Project `.env` file.
- `storage/app/public` and uploaded files.
- `public/storage` symlink target contents.
- Any custom school logos or result assets.

## Before Running Migrations

1. Export the database.
2. Download or copy `storage` and `.env`.
3. Confirm the backup file opens and is not empty.
4. Run migrations locally first.
5. Run `php artisan migrate --force` only after the backup is confirmed.

## Restore Outline

1. Put the app into maintenance mode if available.
2. Import the saved SQL dump into the target database.
3. Restore `storage` files.
4. Restore `.env` values.
5. Run:

```bash
php artisan optimize:clear
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Security Notes

- Keep `APP_DEBUG=false` in production.
- Never place `.env` inside a publicly browsable folder.
- Ensure `storage` and `bootstrap/cache` are writable.
- Preserve payment and audit records.
- Use revoke/archive for sensitive records instead of hard delete.
