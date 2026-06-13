# Marketplace cPanel Installation

This guide describes the marketplace-style cPanel install path for Sanfaani Schools Standalone:

```text
Upload package -> Extract -> Open installer -> Requirements check -> Database setup -> Admin setup -> School setup -> License activation -> Finish install
```

The installer helps the buyer complete the Sanfaani Schools setup safely after the hosting account has been prepared. It does not create a cPanel database, change the domain document root, create server cron jobs, purchase SSL, or store real secrets in Git.

## What The Installer Handles

The current `/install` flow supports:

- Requirements check for PHP and extensions.
- Permission checks for writable storage and cache paths.
- Database connection and migration-status checks.
- Environment guidance for `.env`, app key, queue, cache, session, mail, scheduler, backups, and updates.
- App key status guidance.
- Migration readiness guidance.
- First admin form.
- First school form.
- SMTP summary form.
- Final review.
- First school/admin creation.
- Installer lock creation at `storage/app/installed.lock`.
- Reinstall prevention after the app is locked.

License activation is handled after install from the authenticated admin license screen. It is not part of the public installer form.

## What The cPanel User Still Does

Before opening `/install`, the cPanel user or installer must:

1. Select PHP 8.3 or newer.
2. Enable required PHP extensions.
3. Upload and extract the approved ZIP.
4. Keep the Laravel root outside public web access.
5. Point the domain document root to Laravel `public`, or use the documented `public_html` fallback.
6. Create a MySQL or MariaDB database and user.
7. Create the server `.env` from `.env.example` or `.env.marketplace.example`.
8. Generate and set `APP_KEY`.
9. Set writable permissions for `storage` and `bootstrap/cache`.
10. Create the storage link or use the host-approved cPanel workaround.
11. Configure cron for Laravel scheduler.
12. Configure SMTP.
13. Create and verify a first backup after installation.

## Hosting Requirements

Use:

- PHP 8.3 or newer.
- MySQL or MariaDB with InnoDB and `utf8mb4`.
- Apache or LiteSpeed with HTTPS and `.htaccess`.
- Composer-prepared `vendor/` inside the cPanel-ready ZIP, or Composer access on the server.
- Built assets in `public/build`.
- Cron jobs.
- SMTP access.

Required PHP extensions:

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
- `pdo_mysql`
- `phar`
- `session`
- `tokenizer`
- `xml`
- `xmlwriter`
- `zlib`

Recommended extensions:

- `bcmath`
- `intl`
- `zip`
- `redis` only when Redis is configured

## Upload And Extract

Upload the approved cPanel ZIP through cPanel File Manager or a trusted SFTP client. Extract it into a private folder such as:

```text
/home/CPANEL_USER/sanfaani-schools/
```

Do not extract the full Laravel root into a publicly browsable folder.

The ZIP must not contain `.env`, `.git`, `node_modules`, local logs, local cache files, SQL dumps, backups, or `public/build.zip`.

## Folder Structure

Preferred structure:

```text
/home/CPANEL_USER/sanfaani-schools/          private Laravel root
/home/CPANEL_USER/sanfaani-schools/public/   domain document root
```

Fallback when cPanel requires `public_html` or a subdomain folder:

```text
/home/CPANEL_USER/sanfaani-schools/          private Laravel root
/home/CPANEL_USER/public_html/               contents of Laravel public/
```

For the fallback, update only the deployed `public_html/index.php` paths so they point to the private Laravel root:

```php
require __DIR__.'/../sanfaani-schools/vendor/autoload.php';
$app = require_once __DIR__.'/../sanfaani-schools/bootstrap/app.php';
```

Never expose these paths publicly:

- `.env`
- `app`
- `bootstrap`
- `config`
- `database`
- `routes`
- `storage`
- `vendor`
- `node_modules`
- backups
- logs
- SQL dumps
- update packages

## Database Setup

In cPanel:

1. Open MySQL Databases.
2. Create a database.
3. Create a database user.
4. Assign the user to the database.
5. Grant required privileges.
6. Copy the cPanel-prefixed database name and user into `.env`.

Use:

```dotenv
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

The installer checks whether the configured connection works. It does not create the cPanel database for the buyer.

## Environment File

Create `.env` on the server from `.env.marketplace.example` or `.env.example`.

Required production values:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=

SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false

FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
CACHE_STORE=file
```

Add database, mail, license, and school-specific values on the server only. Do not put real credentials in docs or Git.

If the installer cannot write `.env`, create or edit it in cPanel File Manager outside public web access.

Generate `APP_KEY` from terminal when available:

```bash
php artisan key:generate --force
```

If terminal is unavailable, use a support-approved key generation process and paste the generated key into `.env`.

## Open Installer

After the folder structure, `.env`, database, app key, and permissions are ready, open:

```text
https://your-domain.example/install
```

Installer steps:

1. Welcome.
2. Requirements.
3. Permissions.
4. Database.
5. Environment.
6. App key.
7. Migrations.
8. Admin setup.
9. School setup.
10. SMTP summary.
11. Review.
12. Complete.

Run migrations from terminal where available:

```bash
php artisan migrate --force
```

If terminal is unavailable, use the host-approved migration/import path. Do not run destructive migration commands on staging or production.

## License Activation

After the installer completes:

1. Log in as the first admin.
2. Open `Admin -> License`.
3. Open license activation.
4. Enter the license key and license type.
5. Confirm domain and entitlement values if required.
6. Save and review license status.

Raw license keys are not stored. License records use hashed keys and masked display.

## Installer Lock

Successful completion writes:

```text
storage/app/installed.lock
```

After the lock exists, `/install` returns not found. Do not delete the lock to troubleshoot a live installation. Escalate to Sanfaani support if reinstall or recovery is needed.

## Post-Install Checks

Run or verify:

```bash
php artisan route:list
php artisan about
php artisan standalone:status
php artisan deployment:check-readiness
php artisan performance:audit
APP_ENV=production APP_DEBUG=false php artisan security:audit
```

Browser checks:

- HTTPS loads.
- `/up` is healthy.
- `/install` is blocked after completion.
- Admin login works.
- License page shows expected status.
- `public/build` assets load.
- Uploaded images render through public storage.
- `.env`, `vendor`, logs, and private storage are not publicly readable.
- Scheduler heartbeat becomes fresh after cron runs.
- Mail test works.
- First backup is created or documented through the approved backup path.
- Update preflight is reviewed as metadata/preflight only, not automatic update application.

## Troubleshooting

403 errors usually mean the document root does not point to Laravel `public`, `.htaccess` is missing, or permissions block Apache/LiteSpeed.

500 errors usually mean `.env` syntax is invalid, `APP_KEY` is missing, a PHP extension is missing, database credentials are wrong, or `storage` / `bootstrap/cache` is not writable.

Installer unavailable usually means `SANFAANI_INSTALLER_ENABLED=false`, `SANFAANI_INSTALLED=true`, the deployment mode is not `single_school`, or `storage/app/installed.lock` already exists.

Database check failures usually mean the cPanel-prefixed database name or username is wrong, the user is not assigned to the database, or the database server host is not `localhost`.

Missing assets usually mean `public/build` was not uploaded.

Broken uploads usually mean `php artisan storage:link` was not run or cPanel blocks symlinks.

Mail failures usually mean SMTP host, port, encryption, username, password, sender verification, or host firewall rules are wrong.

## Security Warnings

- Do not upload `.env` inside a marketplace ZIP.
- Do not expose the Laravel root publicly.
- Do not expose `vendor`, `storage`, `database`, `routes`, or backups.
- Do not use `APP_DEBUG=true` for a school installation.
- Do not share database passwords, app keys, SMTP credentials, license keys, payment keys, or backup files through ordinary chat.
- Do not run `composer update`, `npm update`, or `npm audit fix` during installation.
- Do not run `migrate:fresh` or destructive database commands on live data.
