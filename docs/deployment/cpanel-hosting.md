# cPanel Hosting Deployment

This guide applies to cPanel providers, including Namecheap-style shared hosting, reseller hosting, and marketplace buyer hosting.

## Folder Structure And Public Mapping

Preferred:

- Private app folder: `/home/account/apps/sanfaani-schools`
- Domain document root: `/home/account/apps/sanfaani-schools/public`

Fallback when document root cannot be changed:

- Put only the files from Laravel `public` in `public_html`.
- Update `index.php` to point to private `../apps/sanfaani-schools/vendor/autoload.php` and `../apps/sanfaani-schools/bootstrap/app.php`.
- Keep `.env`, app code, storage, logs, backups, and database dumps outside `public_html`.

See `docs/deployment/public-folder-mapping.md`.

## Upload Checklist

- Upload a clean package.
- Exclude `.env`, `vendor`, `node_modules`, logs, caches, sessions, backups, private storage, test databases, and `public/build.zip`.
- Upload `public/build` only from a reviewed asset build.

## PHP And Extensions

Select PHP 8.3 or newer in MultiPHP Manager. Enable Laravel-required extensions such as `ctype`, `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo`, `tokenizer`, and `xml`. Optional features may need `gd`, `intl`, `redis`, or `zip`.

## Database Setup

- Create a MySQL database.
- Create a database user.
- Assign privileges.
- Configure `.env`.
- Run migrations through terminal, installer, or reviewed SQL import workflow.

## Composer And Build Assets

With terminal:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Without Node on server, build locally and upload `public/build`. Do not rely on `public/build.zip`.

## Installer Flow

- Copy the buyer-safe env template.
- Set `SANFAANI_INSTALLER_ENABLED=true`.
- Set `SANFAANI_INSTALLED=false`.
- Visit the installer route.
- Complete requirements, permissions, database, app key, migration readiness, admin, school, SMTP, and final review.
- Confirm the installation lock is written.

## Cron And Queue

Create this cron command with the correct PHP path:

```bash
* * * * * /usr/local/bin/php /home/account/apps/sanfaani-schools/artisan schedule:run >> /dev/null 2>&1
```

For queues, use `sync` for simple shared hosting or database queue plus cron if the host allows it. Long-running queue workers are better suited to VPS/cloud hosting.

## Permissions

- Directories: `0755`.
- Files: `0644`.
- `storage` and `bootstrap/cache` writable by PHP.
- Avoid `0777` except as a temporary diagnostic under host guidance.

## SMTP Setup

Use cPanel email or an external SMTP provider. Verify port and encryption with the host. Use the mail settings screen only after `.env` values are correct and secrets are protected.

## Cache Commands

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Clear cache after changing `.env`.

## Update And Backup Cautions

- Back up database and uploaded files before updates.
- Do not run migrations automatically from the browser.
- Do not extract update packages into app folders from the web UI.
- Use backup metadata and update preflight as safety checks, not as deployment automation.

## Troubleshooting

See `docs/deployment/deployment-troubleshooting.md` for 403, 500, missing assets, storage links, mail failures, and cache issues.
