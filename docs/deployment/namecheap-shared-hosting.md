# Namecheap Shared Hosting Deployment

This guide covers Sanfaani Schools on Namecheap shared hosting for marketplace buyers, single-school licensed deployments, and small managed clients.

## Folder Structure

Preferred structure:

- `/home/account/sanfaani-schools`: full Laravel project.
- `/home/account/public_html` or the selected domain root: points to `sanfaani-schools/public`.
- `.env`, `storage`, `vendor`, `node_modules`, logs, backups, and database dumps stay outside public access.

If Namecheap cannot point the domain root to `public`, place only the contents of Laravel `public` in the web root and update `index.php` paths to reference the private project folder. Do this carefully and never move `.env` into the web root.

## Upload Safely

- Upload a reviewed release package.
- Do not upload `.env`, `vendor`, `node_modules`, storage logs, storage cache, backups, private uploads, SQL dumps, or `public/build.zip`.
- Upload built frontend assets from `public/build` only when assets are intentionally included.
- Keep the project outside public web root where possible.

## Environment Setup

- Copy `.env.marketplace.example` or `.env.example` to `.env`.
- Set `APP_ENV=production`, `APP_DEBUG=false`, and the real `APP_URL`.
- Set database, SMTP, deployment mode, license mode, update, and backup values.
- Generate `APP_KEY` through terminal if available or the installer/app key step.
- Never paste real secrets into docs, tickets, screenshots, or Git.

## Database Setup

- Create a MySQL database and user in cPanel.
- Assign the user to the database with required privileges.
- Put the database name, user, password, host, and port in `.env`.
- Run migrations through CLI if available: `php artisan migrate --force`.
- If CLI is unavailable, use the installer migration readiness flow and follow host-supported migration/import procedures.
- Do not use `migrate:fresh` on demo or production. `EnvironmentGuard` blocks destructive commands by design.
- If a migration failed halfway on an empty demo database, drop only the partial table that failed after confirming it contains no production/client data.
- Namecheap/cPanel MySQL can enforce a 1000-byte key limit; see `docs/deployment/shared-hosting-mysql-index-compatibility.md`.

## Composer And Assets

Preferred server commands where terminal is available:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

If Node is unavailable, build assets locally in a clean release workspace and upload `public/build`. Do not use `public/build.zip` as the deployment artifact.

If Composer is not globally available, upload or use a local Composer PHAR and run:

```bash
php composer.phar install --no-dev --optimize-autoloader
```

For GitHub checkout, use HTTPS clone for a public repository when SSH keys are not configured. For private repositories, configure a deploy key before using SSH clone.

## Storage Link Workaround

Run `php artisan storage:link` if terminal access is available. If symlinks are blocked, use the manual guidance in `docs/deployment/storage-link-workarounds.md` and keep private storage private.

## Permissions

- `storage` writable by the PHP user.
- `bootstrap/cache` writable by the PHP user.
- Files usually `0644`, directories usually `0755`.
- Do not make the whole project `0777`.

## Cron And Queue

Set cPanel cron where supported:

```bash
* * * * * /usr/local/bin/php /home/account/sanfaani-schools/artisan schedule:run >> /dev/null 2>&1
```

Use `QUEUE_CONNECTION=sync` for simple shared hosting or `database` if the host supports cron-triggered workers. See `docs/deployment/queue-and-cron-strategy.md`.

## Optimization

After deployment, and only after `.env` is correct:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If a 500 error appears after cache changes, run `php artisan optimize:clear`.

## SMTP

Use Namecheap Private Email, cPanel email, or a trusted SMTP provider. Set `MAIL_MAILER=smtp`, host, port, encryption, username, password, from address, and from name. See `docs/deployment/smtp-setup.md`.

## Installer And License Flow

- Use `SANFAANI_DEPLOYMENT_MODE=single_school` for marketplace/single-school installs.
- Use `SANFAANI_INSTALLER_ENABLED=true` and `SANFAANI_INSTALLED=false` until setup is complete.
- Run the installer to check requirements, permissions, database, app key, migrations, admin, school, SMTP, and final review.
- Activate or validate the license after installation using the license foundation.

## Backup And Update Flow

- Create a manual database export from cPanel/phpMyAdmin before launch and before updates.
- Use the backup manager foundation for metadata and verification; it does not run restore.
- Use the update manager only for package metadata, preflight, migration warnings, and rollback planning; it does not apply code patches.

## Common Errors

- 403: document root is not mapped to Laravel `public`, file permissions are too strict, or `.htaccess` is missing.
- 500: missing PHP extension, invalid `.env`, stale config cache, unwritable storage/cache, or database credentials are wrong.
- Migration key length error: cPanel/Namecheap MySQL may reject long `utf8mb4` composite indexes; confirm the shared-hosting compatibility hotfix is present.
- Migration identifier error: long automatic foreign key names may exceed MySQL's 64-character identifier limit; use explicit short names in migrations.
- Assets missing: `APP_URL` wrong, `public/build` missing, or storage link unavailable.
- Uploaded images missing: storage link/workaround not configured.

## Validation Notes

Run security audit with production-style env overrides when shell defaults are local:

```bash
APP_ENV=production APP_DEBUG=false php artisan security:audit
```

Expected readiness warnings are advisory unless the command reports `fail`.

## Rollback Notes

Rollback means restoring verified database and file backups manually through cPanel/phpMyAdmin/file manager. Do not run destructive restore commands from the web UI.

## Security Checklist

- `.env` is not public.
- `storage`, logs, backups, and SQL dumps are not public.
- `APP_DEBUG=false`.
- HTTPS is enabled.
- Admin credentials are unique.
- License validation is enabled.
- Backups are stored outside public web root.
