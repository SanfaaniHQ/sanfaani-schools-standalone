# Namecheap Pilot Deployment

## Domain

Launch domain: `https://schools.sanfaani.net`

## Local Pre-Deployment

Run locally before upload:

```powershell
cd C:\laragon\www\sanfaani-schools
git status --short
composer install
php artisan migrate
php artisan optimize:clear
npm.cmd run build
php artisan route:list
```

Check locally:

- Landing page opens.
- Login opens.
- Public result checker opens.
- Scratch card school and Super Admin flows still open.
- `APP_DEBUG=false` is used in production.
- Local database is backed up before major deployment work.

## cPanel Preparation

1. Create subdomain `schools.sanfaani.net`.
2. Point the document root to the Laravel `public` directory if cPanel allows it.
3. Create a MySQL database.
4. Create a MySQL user.
5. Assign the user to the database with required privileges.
6. Set PHP to 8.2 or the project-compatible version.
7. Enable required PHP extensions if available.
8. Enable SSH if the hosting plan supports it.

## Recommended Directory Structure

Best:

- Laravel app outside `public_html`.
- Subdomain document root points to `project/public`.

Fallback:

- Laravel app in `/home/username/sanfaani-schools`.
- Public files in `/home/username/public_html/schools`.
- Update `public/index.php` paths carefully to load the app from the Laravel directory.

## Deployment With Git/SSH

```bash
cd /home/username
git clone REPOSITORY_URL sanfaani-schools
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

Set production `.env` values before caching config.

## Manual ZIP Deployment

1. Run `npm.cmd run build` locally.
2. Run `composer install --no-dev --optimize-autoloader` locally if Composer is unavailable on hosting.
3. Zip the project excluding `node_modules` and `.git`.
4. Upload and extract on hosting.
5. Configure `.env`.
6. Import the database or run migrations if SSH is available.
7. Adjust `public/index.php` only when using the fallback structure.

## Permissions

Ensure these directories are writable:

- `storage`
- `bootstrap/cache`

## Production `.env` Sample

```env
APP_NAME="Sanfaani Schools"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://schools.sanfaani.net

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
MAIL_FROM_ADDRESS="support@sanfaani.net"
MAIL_FROM_NAME="Sanfaani Schools"

PAYMENT_DEFAULT_GATEWAY=manual
PAYSTACK_ENABLED=false
PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
FLUTTERWAVE_ENABLED=false
FLUTTERWAVE_PUBLIC_KEY=
FLUTTERWAVE_SECRET_KEY=
```

## Launch Checklist

- Home page opens.
- Features, pricing, contact, and demo pages open.
- Contact and demo forms submit.
- Login works.
- Super Admin login works.
- School Admin login works.
- Result Officer login works if enabled.
- Create school.
- Create class, subject, session, and term.
- Create student with auto admission number.
- Upload or enter result.
- Publish result.
- Request scratch cards.
- Confirm payment, approve, and generate scratch cards.
- Public result checker works.
- Print result works.
- Invalid, revoked, expired, and unpublished result cases fail safely.
- Arabic/French result checker labels render.
- Mobile view works.

## Move to VPS When

- More than 10 active schools are onboarded.
- Upload volume becomes heavy.
- Queue workers are needed.
- Payment webhooks are active.
- PDF generation becomes heavy.
- Performance issues appear.
- More server control is required.
