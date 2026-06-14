# Marketplace cPanel Installation

This guide describes the marketplace-style cPanel install path for Sanfaani Schools Standalone:

```text
Upload package -> Extract -> Create database -> Configure .env -> Open /install -> Complete school setup -> Log in -> Activate license
```

The installer helps the buyer complete the Sanfaani Schools setup safely after the hosting account has been prepared. It does not create a cPanel database, change the domain document root, create server cron jobs, purchase SSL, or store real secrets in Git.

## What The Installer Handles

The current `/install` flow supports:

- Requirements check for PHP and extensions.
- Permission checks for writable storage and cache paths.
- Database connection and migration-status checks.
- Portal configuration guidance for `.env`, security key, queue, cache, session, mail, scheduler, backups, and updates.
- Security key status guidance.
- Database table readiness guidance.
- First owner account form.
- School profile form.
- Email settings review.
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

Normal buyer-selected cPanel database names are supported, including names such as `swifarpx_fazportal`, `client_school_portal`, and `portal_db`. Do not rename the buyer database just to include `sanfaani_schools`. The optional internal database-name guard is disabled for marketplace installs unless `SANFAANI_DATABASE_NAME_GUARD_ENABLED=true` is explicitly set.

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
SANFAANI_DATABASE_NAME_GUARD_ENABLED=false
SANFAANI_DATABASE_NAME_REQUIRED_FRAGMENT=sanfaani_schools
# Seller-side generator secret. Leave blank on normal customer portals.
SANFAANI_LICENSE_SIGNING_KEY=

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

Before installation is complete, the site root (`https://your-domain.example/`) should send the buyer into this setup flow and `/login` should redirect back to the installer. After installation is complete, the site root should point to the portal login flow.

Installer steps:

1. Welcome.
2. Requirements.
3. Permissions.
4. Database.
5. Portal configuration.
6. Security key.
7. Prepare database.
8. Owner account.
9. School profile.
10. Email settings.
11. Review.
12. Complete.

Run migrations from terminal where available:

```bash
php artisan migrate --force
```

If terminal is unavailable, use the host-approved migration/import path. Do not run destructive migration commands on staging or production.

The standalone sync migrations use shared-hosting-safe string lengths and short index names for older MySQL/cPanel key-length limits.

## License Activation

After the installer completes:

1. Log in as the first admin.
2. Open `Admin -> License`.
3. Open license activation.
4. Enter the license key and license type.
5. Confirm domain and included module values if required.
6. Save and review license status.

Raw license keys are not stored. License records use hashed keys and masked display.

Customer portals do not need `SANFAANI_LICENSE_SIGNING_KEY` to install, log in, or use normal customer activation. Seller license generation remains separate and must happen only in a trusted Sanfaani seller environment. If signed-key verification is used for a customer portal, Sanfaani must configure that verification securely during approved setup; the school should never enter or receive the seller signing secret.

## Admissions Link Handoff

After login, open `Admin -> Admissions`. The admissions dashboard shows the public admission form link, a copy button, a preview button, and website guidance. Copy the form link into the school website, WhatsApp, SMS, email, or printed admission instructions. If website embedding is enabled, the settings page shows the iframe code that a website manager can add to the school website.

## Installer Lock

Successful completion writes:

```text
storage/app/installed.lock
```

After the lock exists, `/install` returns not found. Do not delete the lock to troubleshoot a live installation. Escalate to Sanfaani support if reinstall or recovery is needed.

Do not manually create `storage/app/installed.lock`. Complete the installer so the first school, first admin, and lock are created together.

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
- `/` redirects to the installer before setup and to login after setup.
- `/up` is healthy.
- `/install` is blocked after completion.
- Admin login works.
- License page shows expected status.
- `public/build` assets load.
- Uploaded images render through public storage.
- `.env`, `vendor`, logs, and private storage are not publicly readable.
- Scheduler heartbeat becomes fresh after cron runs.
- Mail test works.
- The admissions form link can be copied from `Admin -> Admissions` and added to the school website.
- First backup is created or documented through the approved backup path.
- Update preflight is reviewed as metadata/preflight only, not automatic update application.

## Troubleshooting

403 errors usually mean the document root does not point to Laravel `public`, `.htaccess` is missing, or permissions block Apache/LiteSpeed.

500 errors usually mean `.env` syntax is invalid, `APP_KEY` is missing, a PHP extension is missing, database credentials are wrong, or `storage` / `bootstrap/cache` is not writable.

Installer unavailable usually means `SANFAANI_INSTALLER_ENABLED=false`, `SANFAANI_INSTALLED=true`, the deployment mode is not `single_school`, or `storage/app/installed.lock` already exists. A fresh single-school install must not depend on school, subscription, license, or feature database rows before `/install` opens.

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
