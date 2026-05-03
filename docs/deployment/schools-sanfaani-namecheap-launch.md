# Sanfaani Schools Namecheap Launch Guide

Launch domain: `https://schools.sanfaani.net`

This guide is for deploying the Laravel project to Namecheap/cPanel for the first public pilot.

## A. Local Preparation

1. Confirm the working tree is ready:

   ```bash
   git status
   ```

2. Run migrations locally:

   ```bash
   php artisan migrate
   ```

3. Clear local caches:

   ```bash
   php artisan optimize:clear
   ```

4. Build frontend assets:

   ```bash
   npm.cmd run build
   ```

5. Review routes:

   ```bash
   php artisan route:list
   ```

6. Test the key public and auth URLs locally:

   - `/`
   - `/features`
   - `/pricing`
   - `/contact`
   - `/demo`
   - `/result-checker`
   - `/login`

7. Prepare production safety:

   - Set `APP_DEBUG=false` in production.
   - Back up the local database before deployment.
   - Keep the production `.env` out of Git.

## B. Namecheap/cPanel Preparation

1. Create the subdomain:

   - Subdomain: `schools`
   - Full domain: `schools.sanfaani.net`

2. Set the document root:

   - Best option: point the subdomain document root directly to the Laravel `public` folder.
   - Example: `/home/username/sanfaani-schools/public`

3. Create database credentials:

   - Create a MySQL database.
   - Create a MySQL user.
   - Assign the user to the database with all needed privileges.

4. Set platform requirements:

   - PHP version: 8.2+ or the exact version required by the project.
   - Enable common Laravel extensions if available: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`.
   - Enable SSH if your hosting package supports it.

## C. Recommended Laravel Directory Structure

Best structure:

```text
/home/username/sanfaani-schools
/home/username/sanfaani-schools/app
/home/username/sanfaani-schools/bootstrap
/home/username/sanfaani-schools/public
```

Then point the `schools.sanfaani.net` document root to:

```text
/home/username/sanfaani-schools/public
```

Fallback structure if cPanel cannot point the subdomain to Laravel `public`:

```text
/home/username/sanfaani-schools
/home/username/public_html/schools
```

In this fallback:

1. Put Laravel app files in `/home/username/sanfaani-schools`.
2. Put the contents of Laravel `public` in `/home/username/public_html/schools`.
3. Update `/home/username/public_html/schools/index.php` paths so it loads:

   ```php
   require __DIR__.'/../../sanfaani-schools/vendor/autoload.php';
   $app = require_once __DIR__.'/../../sanfaani-schools/bootstrap/app.php';
   ```

Adjust the path depth if your cPanel directory structure differs.

## D. Upload Method 1: Git/SSH

1. SSH into the hosting account.
2. Move to the target folder:

   ```bash
   cd /home/username
   ```

3. Clone the repository:

   ```bash
   git clone REPOSITORY_URL sanfaani-schools
   cd sanfaani-schools
   ```

4. Install production PHP dependencies:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

5. Upload or create `.env`.
6. Generate an app key if this is a fresh production install:

   ```bash
   php artisan key:generate
   ```

7. Set database credentials in `.env`.
8. Run production migrations:

   ```bash
   php artisan migrate --force
   ```

9. Link storage:

   ```bash
   php artisan storage:link
   ```

10. Cache production configuration:

   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## E. Upload Method 2: ZIP/Manual

1. Build assets locally:

   ```bash
   npm.cmd run build
   ```

2. If Composer is unavailable on hosting, run production Composer locally:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. ZIP the project, excluding:

   - `.git`
   - `node_modules`
   - local logs/cache files if not needed

4. Upload the ZIP through cPanel File Manager.
5. Extract into the target Laravel folder.
6. Create or upload `.env`.
7. Import the database manually, or run migrations through SSH if available:

   ```bash
   php artisan migrate --force
   ```

8. If using the fallback public folder structure, update `public/index.php` paths as described above.
9. Run cache commands if SSH is available:

   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## F. Production `.env` Sample

```env
APP_NAME="Sanfaani Schools"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://schools.sanfaani.net

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
```

## G. Launch Checklist

- Home page opens.
- Features, pricing, contact, and demo pages open.
- Contact form submits.
- Demo form submits.
- Login works.
- Super Admin login works.
- School Admin login works.
- Result Officer login works if implemented.
- Create school.
- School code is generated or saved manually.
- Create class, subject, session, and term.
- Create student with auto admission number.
- Upload students with blank admission number cells.
- Enter result manually.
- Upload result CSV.
- Publish result.
- Request scratch card.
- Approve or confirm scratch card payment.
- Generate scratch cards.
- Public result checker works.
- Print result works.
- Mobile view works.
- Arabic/French labels work.
- Invalid card fails safely.
- Revoked card fails safely.
- Unpublished result does not show.

## H. Move To VPS When

- More than 10 active schools are using the platform.
- Uploads become heavy.
- Queues are needed.
- Payment webhooks become active.
- PDF generation becomes heavy.
- Performance issues appear.
- More server control is needed.
