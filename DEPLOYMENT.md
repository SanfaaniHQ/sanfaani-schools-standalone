# Sanfaani Schools Deployment Runbook

This platform is a live multi-tenant school SaaS. The production database name is locked:

```text
sanfaani_schools
```

Never run `php artisan migrate:fresh`, `php artisan migrate:refresh`, `php artisan db:wipe`, or `php artisan db:seed` on this project.

## Pre-Deploy

1. Verify `.env`:
   ```bash
   grep "^DB_CONNECTION=" .env
   grep "^DB_HOST=" .env
   grep "^DB_DATABASE=" .env
   ```
   `DB_DATABASE` must be `sanfaani_schools`.

2. Run deployment verification:
   ```bash
   php artisan sanfaani:deployment-verify
   ```

3. Back up the database outside `public/`:
   ```bash
   mysqldump -u root -p --single-transaction --routines --triggers sanfaani_schools > ../sanfaani-db-backups/backup_sanfaani_$(date +%Y%m%d_%H%M%S).sql
   ```

4. Preview migrations:
   ```bash
   php artisan migrate --pretend > migration_preview.sql
   ```
   Stop if the preview contains `DROP TABLE`, `TRUNCATE`, `DELETE`, or `ALTER TABLE ... DROP`.

5. Run tests:
   ```bash
   php artisan test
   ```

## Deploy

1. Enable maintenance mode:
   ```bash
   php artisan down
   ```

2. Upload or pull code.

3. Re-check `.env` and database target:
   ```bash
   php artisan tinker --execute="echo DB::connection()->getDatabaseName();"
   ```

4. Run safe additive migrations only:
   ```bash
   php artisan migrate --force
   ```

5. Clear and rebuild caches safely:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

6. Disable maintenance mode:
   ```bash
   php artisan up
   ```

## Post-Deploy

1. Confirm login works for Super Admin and a School Admin.
2. Open dashboards for Super Admin, School Admin, Teacher, Result Officer, Student, and Parent-facing result pages where available.
3. Verify scratch-card request, payment confirmation, generation, export, revocation, and result-checker usage.
4. Verify school branding: logo, favicon, colors, motto, login background, email logo, and report header.
5. Verify tenant mail: school mailer, platform fallback, and log fallback.
6. Verify route cache and config cache:
   ```bash
   php artisan route:list
   php artisan config:show database.connections.mysql.database
   ```
7. Check logs:
   ```bash
   tail -n 100 storage/logs/laravel.log
   ```

## Shared Hosting Queue Note

For mail and exports on shared hosting, run a short-lived worker from cron:

```bash
php artisan queue:work --queue=mail,exports --sleep=3 --tries=3 --timeout=60 --stop-when-empty
```

Keep the backup created for the release until the next verified deployment is complete.
