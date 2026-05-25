# Deployment Troubleshooting

## 403 Forbidden

- Document root is not mapped to Laravel `public`.
- `.htaccess` is missing on Apache/cPanel.
- File permissions block the web server.
- Public folder fallback paths in `index.php` are wrong.

## 500 Server Error

- `APP_KEY` missing.
- `.env` syntax error.
- Required PHP extension missing.
- `storage` or `bootstrap/cache` not writable.
- Database credentials wrong.
- Stale config cache after `.env` changes.

Run:

```bash
php artisan optimize:clear
```

only when terminal access is available and it is safe.

## Missing Assets

- `public/build` missing.
- Assets built against wrong app URL.
- Browser cache or stale view cache.
- `public/build.zip` was uploaded instead of reviewed built assets.

## Missing Uploads

- `php artisan storage:link` not run.
- Symlinks blocked by shared hosting.
- `FILESYSTEM_DISK` wrong.
- File permissions too restrictive.

## Mail Fails

- SMTP host/port/encryption mismatch.
- Credentials wrong.
- Sender not verified.
- Host blocks outbound SMTP.

## Database Errors

- Database name/user/password wrong.
- User not assigned to database.
- Missing migrations.
- Collation or charset mismatch.

## Rollback

Rollback is manual: restore files, database, `.env`, and uploaded files from verified backups. The web UI does not execute restore operations.
