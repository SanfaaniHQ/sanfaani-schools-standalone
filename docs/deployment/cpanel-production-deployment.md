# cPanel Production Deployment Preparation

This runbook prepares Sanfaani Schools Standalone for a safe cPanel staging or production-style deployment. It does not replace a security approval gate: if GitHub still reports dependency vulnerabilities, treat the target as staging until dependency remediation is separately approved and validated.

## Hosting Requirements

Use a cPanel account that supports:

- PHP 8.3 or newer. `composer.json` requires `php: ^8.3`.
- Composer 2 on the server, or a reviewed local `composer.phar`.
- MySQL or MariaDB with InnoDB and `utf8mb4` support.
- Apache or LiteSpeed with document root control, `.htaccess`, and HTTPS.
- Cron jobs.
- Writable private storage outside the public document root.
- SMTP access through cPanel email or a trusted external provider.

Required PHP extensions for this application and its locked dependencies:

- `ctype`
- `curl`
- `dom`
- `fileinfo`
- `filter`
- `gd`
- `hash`
- `iconv`
- `json`
- `libxml`
- `mbstring`
- `openssl`
- `pcre`
- `pdo`
- `pdo_mysql` or host-provided MySQL PDO driver
- `phar`
- `session`
- `tokenizer`
- `xml`
- `xmlwriter`
- `zlib`

Recommended extensions:

- `bcmath`
- `intl`
- `redis` only if Redis is explicitly configured
- `zip` for update-package inspection and archive workflows

## Folder Structure

Keep the Laravel application root private. Do not expose `app`, `bootstrap`, `config`, `database`, `routes`, `storage`, `vendor`, `.env`, update packages, logs, backups, SQL dumps, or `node_modules`.

Recommended private app root:

```text
/home/CPANEL_USER/sanfaani-schools/
```

Recommended public document roots:

```text
/home/CPANEL_USER/public_html/
/home/CPANEL_USER/portal.schoolname.com/
```

Preferred cPanel setup when custom document roots are available:

```text
/home/CPANEL_USER/sanfaani-schools/          private Laravel root
/home/CPANEL_USER/sanfaani-schools/public/   domain document root
```

Shared-hosting fallback when the domain must point at `public_html` or a cPanel-created subdomain folder:

```text
/home/CPANEL_USER/sanfaani-schools/          private Laravel root
/home/CPANEL_USER/public_html/               contents of Laravel public/
```

In the fallback layout, update only the deployed copy of `public_html/index.php` so it loads:

```php
require __DIR__.'/../sanfaani-schools/vendor/autoload.php';
$app = require_once __DIR__.'/../sanfaani-schools/bootstrap/app.php';
```

Do not make `.env` public. Confirm these URLs do not render after deployment:

```text
https://domain.example/.env
https://domain.example/vendor/
https://domain.example/storage/logs/
https://domain.example/node_modules/
```

## Package Preparation

Prepare dependencies and assets on a trusted machine before upload:

```bash
composer validate
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan route:list
```

Upload the application without:

- `.env`
- `.env.local`
- `.git`
- `node_modules`
- logs, sessions, caches, backup archives, SQL dumps, and local test databases
- `public/build.zip`

Upload `public/build` from a reviewed `npm run build` output. Do not use `public/build.zip` as a deployment artifact.

If server-side Composer is available, run this in the private Laravel root:

```bash
composer install --no-dev --optimize-autoloader
```

If Composer is only available as a PHAR:

```bash
php composer.phar install --no-dev --optimize-autoloader
```

Do not run `composer update`, `npm update`, or `npm audit fix` during cPanel deployment.

## Database Setup

In cPanel:

1. Create a MySQL or MariaDB database.
2. Create a database user.
3. Assign the user to the database with the required application privileges.
4. Record the cPanel-prefixed database name and username for `.env`.
5. Use `utf8mb4` and InnoDB.

Set production database variables in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Run migrations only after the database and `.env` have been verified:

```bash
php artisan migrate --force
```

Never run `migrate:fresh` on staging or production data.

## Production Environment

Create `.env` on the server from `.env.example`, then set production values outside Git.

Required production variables include:

```dotenv
APP_NAME=
APP_VERSION=
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=

SANFAANI_PRODUCT_EDITION=standalone
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

FILESYSTEM_DISK=public
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=

SANFAANI_LICENSE_KEY=
SANFAANI_LICENSE_SERVER_URL=
SANFAANI_BACKUPS_ENABLED=true
SANFAANI_UPDATES_ENABLED=true
SANFAANI_UPDATE_BACKUP_REQUIRED=true
SANFAANI_SECURITY_DIAGNOSTICS_ENABLED=true
SANFAANI_SECRET_REDACTION_ENABLED=true
```

Generate the app key only if the server `.env` does not already have one:

```bash
php artisan key:generate --force
```

After every `.env` change, clear and rebuild caches:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Storage And Permissions

Set normal permissions first:

```text
directories: 0755
files:       0644
```

Ensure PHP can write to:

```text
storage/
bootstrap/cache/
```

Create the public storage link:

```bash
php artisan storage:link
```

If cPanel blocks symlinks, use the host-approved workaround from `docs/deployment/storage-link-workarounds.md`. Do not expose private storage folders to solve upload rendering.

## Queue, Cache, Sessions, And Cron

For simple shared hosting:

```dotenv
QUEUE_CONNECTION=sync
CACHE_STORE=file
SESSION_DRIVER=file
```

If database queues are explicitly approved, run queue jobs through cron with short-lived workers rather than a long-running background process:

```bash
* * * * * /usr/local/bin/php /home/CPANEL_USER/sanfaani-schools/artisan queue:work --stop-when-empty --tries=3 --timeout=60 >> /dev/null 2>&1
```

Always configure the Laravel scheduler cron:

```bash
* * * * * /usr/local/bin/php /home/CPANEL_USER/sanfaani-schools/artisan schedule:run >> /dev/null 2>&1
```

Use the actual PHP binary path shown by cPanel or hosting support.

## Mail SMTP

Use cPanel email, Namecheap Private Email, or another trusted SMTP provider. Confirm host, port, encryption, username, sender address, and whether outbound SMTP ports are allowed.

Common settings:

```dotenv
MAIL_MAILER=smtp
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

or:

```dotenv
MAIL_MAILER=smtp
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

Keep SMTP credentials only in the server `.env` or approved cPanel secret storage.

## Backup Setup

Before first real use:

1. Confirm cPanel account backups are enabled.
2. Export a database backup through cPanel Backup Wizard or phpMyAdmin.
3. Back up uploaded files from `storage/app/public` or the approved public upload path.
4. Store backups outside the web document root.
5. Create and verify the first app backup metadata record where the backup feature is licensed and enabled.
6. Record retention expectations.

The web backup foundation records metadata, verification, and restore guidance. It does not execute a production restore. Restore work must be manual, approved, and tested in staging first.

## Installer, License, And Update Readiness

Before handoff:

- Visit `/install` only when `SANFAANI_INSTALLER_ENABLED=true` and `SANFAANI_INSTALLED=false`.
- Complete installer requirements, permissions, database, app key, migration readiness, admin, school, SMTP, and review steps.
- Confirm the install lock exists after completion.
- Visit `/admin/license` after login and activate or validate the license.
- Confirm license mode and domain matching expectations.
- Visit `/admin/updates` only as guided-update readiness. The current foundation validates package metadata and preflight state; it does not download, extract, or apply real updates.
- Confirm a recent verified backup is available before any update review.

## Post-Deployment Checks

Run from the private Laravel root:

```bash
php artisan route:list
php artisan about
php artisan standalone:status
php artisan deployment:check-readiness
php artisan performance:audit
APP_ENV=production APP_DEBUG=false php artisan security:audit
```

Then verify in the browser:

- HTTPS loads on the canonical domain.
- `/up` returns healthy.
- Login page loads.
- First admin login works.
- `APP_DEBUG=false` in production.
- `.env`, `vendor`, private storage, logs, and `node_modules` are not public.
- `public/build/manifest.json` and built CSS/JS assets load.
- Uploaded logos or files render through the approved public storage path.
- Mail test succeeds.
- Scheduler heartbeat becomes fresh after cron runs.
- Backup creation or backup metadata verification works.
- License page shows expected status.
- Update preflight does not claim real update application.
- Admissions public pages and public result checker routes load if enabled.

## Troubleshooting

403 errors usually mean the document root is wrong, `.htaccess` is missing, or permissions are too restrictive.

500 errors usually mean `.env` is invalid, `APP_KEY` is missing, a PHP extension is missing, database credentials are wrong, or `storage` / `bootstrap/cache` is not writable.

Missing assets usually mean `public/build` was not uploaded or caches are stale.

Missing uploads usually mean `php artisan storage:link` was not run, symlinks are blocked, `FILESYSTEM_DISK` is wrong, or `APP_URL` is wrong.

Mail failures usually mean SMTP credentials, port, encryption, sender verification, or host firewall rules are wrong.

Database errors usually mean the cPanel-prefixed database name or username is wrong, the database user is not assigned, migrations were not run, or shared-hosting index limits need review.

Run `php artisan optimize:clear` after fixing `.env`, cache, route, or view-cache issues.

## Local Validation Before Upload

Before packaging or upload, run:

```bash
composer validate
npm run build
php artisan route:list
php artisan test --filter=Installer
php artisan test --filter=License
php artisan test --filter=Update
php artisan test --filter=Backup
php artisan test --filter=Health
php artisan test --filter=Standalone
git diff --check
git diff --name-only -- public/build.zip database/migrations/2026_05_01_173857_create_result_publications_table.php .env .env.local
```

Do not deploy from a dirty working tree unless the exact changed files are the approved deployment package inputs.
