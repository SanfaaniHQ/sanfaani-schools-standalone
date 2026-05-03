# Namecheap Pilot Deployment Guide

This guide targets a 1-5 school pilot on Namecheap shared hosting, with a future VPS migration path.

## Pre-Deployment Checklist

- Working tree is clean and production branch is ready.
- `php artisan migrate` passes locally.
- `npm.cmd run build` passes locally.
- No debug or test credentials are committed.
- `.env` is prepared with `APP_ENV=production` and `APP_DEBUG=false`.
- Local database and storage are backed up.

## Hosting Assumptions

- cPanel hosting.
- MySQL database.
- PHP 8.2+ or compatible with this Laravel version.
- SSH and Composer are recommended.
- If Node is unavailable, build assets locally and upload `public/build`.

## Recommended Directory Layout

Best option:

- Laravel app outside `public_html`.
- Web root points to the Laravel `public` folder.

Fallback shared-hosting option:

- App path: `/home/username/sanfaani-schools`
- Public files in `public_html`
- Update `public_html/index.php` to point to:
  - `../sanfaani-schools/vendor/autoload.php`
  - `../sanfaani-schools/bootstrap/app.php`

## cPanel Steps

1. Create domain or subdomain.
2. Create MySQL database.
3. Create database user.
4. Assign user to database with required privileges.
5. Set PHP version.
6. Enable required PHP extensions if available.
7. Enable SSH if the hosting plan supports it.

## Deploy With SSH/Git

```bash
git clone <repo-url> sanfaani-schools
cd sanfaani-schools
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set database credentials and `APP_URL` before running migrations.

## Deploy With ZIP Upload

1. Run `composer install --no-dev --optimize-autoloader` locally if Composer is unavailable on the server.
2. Run `npm.cmd run build` locally.
3. Zip the project excluding `.git` and `node_modules`.
4. Include `vendor` only if Composer is unavailable on the server.
5. Upload and extract in cPanel.
6. Configure `.env`.
7. Import database or run migrations through SSH.

## File Permissions

- `storage` must be writable.
- `bootstrap/cache` must be writable.

## Production `.env` Skeleton

```env
APP_NAME="Sanfaani Schools"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

## Pilot Test Checklist

- Login as Super Admin.
- Create a school.
- Create or assign a School Admin.
- Create class, subject, session, term, and grading scale.
- Upload or create students.
- Enter or upload results.
- Publish a result.
- Request scratch cards as School Admin.
- Confirm payment and generate cards as Super Admin.
- Check public result with a valid card.
- Print result.
- Open verification code page.
- Test French and Arabic public labels.
- Test invalid, revoked, expired, and used-limit cards.
- Confirm unpublished results never show publicly.

## When to Move to VPS

Move from shared hosting when you have:

- More than 10 active schools.
- Heavy uploads or large result datasets.
- Queue workers and scheduled jobs.
- Server-side PDF generation at scale.
- Payment webhooks.
- Performance or resource-limit issues.
