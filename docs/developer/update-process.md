# Update Process

Sanfaani Schools uses a safe manual update foundation for production launch.

## Current Version

The product version is read from `APP_VERSION` through `config/version.php`.

## Safe Update Rules

- Back up database and files before every update.
- Never replace `.env`.
- Never delete `storage/app/public` uploads.
- Preserve school, student, result, scratch card, payment, audit, and lead data.
- Upload update packages only through Super Admin > System Updates when needed.
- Uploaded packages are stored in `storage/app/updates`, not public storage.
- The app does not automatically extract or apply uploaded ZIP packages.

## After Update

Run:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

On shared hosting, Super Admin can use System Maintenance to clear cache, optimize the app, and repair the storage link.
