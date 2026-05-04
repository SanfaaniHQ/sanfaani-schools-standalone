# Namecheap Production Deployment

Target domain: https://schools.sanfaani.net

This guide is for production launch preparation on Namecheap/cPanel shared hosting.

## Pre-Deployment

- Back up the current database and files.
- Back up before running migrations or replacing files.
- Confirm `composer.lock` and `package-lock.json` are committed.
- Confirm `.env`, logs, backups, `vendor`, and `node_modules` are not committed.
- Confirm no payment keys, SMTP passwords, storage logs, private uploads, or real database dumps are in Git.
- Confirm the deployment branch is `dev` unless a release branch has been selected.
- Build frontend assets locally if the server cannot run npm.
- Confirm production support contact:
  - Email: sanfaanisaas@gmail.com
  - Phone/WhatsApp: +2349010172138

## cPanel Setup

1. Select PHP 8.3 or newer.
2. Enable required PHP extensions for Laravel, MySQL, mbstring, fileinfo, openssl, tokenizer, xml, ctype, json, pdo, and curl.
3. Create the subdomain `schools.sanfaani.net`.
4. Point document root to the Laravel `public` folder if cPanel allows it.
5. If document root cannot point to `public`, use a safe Laravel shared-hosting public path adjustment and keep `.env`, `storage`, and app code outside public access.
6. Create the production database and database user.
7. Assign the user to the database with required privileges.

## Upload or Clone

Upload the project or clone it into the hosting account. Do not upload local `.env`, `node_modules`, `vendor`, logs, backups, or private database dumps.

Run on server where supported:

```bash
composer install --no-dev --optimize-autoloader
php artisan storage:link
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If npm is unavailable on the server, run locally and upload `public/build`:

```bash
npm install
npm run build
```

## Production .env

Set:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://schools.sanfaani.net
APP_VERSION=1.0.0
FILESYSTEM_DISK=public
MAIL_MAILER=smtp
```

Use `MAIL_MAILER=log` only for temporary testing. SMTP credentials must stay in `.env` and must not be committed.

Payment gateway public keys may be used where required by the frontend, but secret keys and webhook secrets must remain server-side only in `.env`.

Manual payment remains active by default. Keep `PAYSTACK_ENABLED=false` and `FLUTTERWAVE_ENABLED=false` until live callback and webhook handling are reviewed.

## Permissions

Make these writable by the PHP user:

- `storage`
- `bootstrap/cache`

Do not make the whole project world-writable.

## Launch Tests

- Login works for Super Admin.
- Super Admin dashboard loads.
- Platform settings can update text details.
- Platform logo upload renders in navigation/login/public pages.
- Login background and favicon uploads render where configured.
- School creation and edit work.
- School logo upload renders on public result print.
- If uploaded images do not display, run `php artisan storage:link`, confirm `APP_URL=https://schools.sanfaani.net`, confirm `FILESYSTEM_DISK=public`, check file permissions, then clear config/view cache.
- School Admin dashboard loads.
- Result checker rejects invalid details safely.
- Valid published result plus valid scratch card shows the result.
- Scratch card usage increments only after the published result opens.
- Demo and contact requests appear in Super Admin > Lead Requests even if email is not configured.
- SMTP or log mail test succeeds.
- `APP_DEBUG=false` in production.
- `.env` is not publicly accessible.
- Backups are not under public web root.

## Post-Deployment

- Back up database and app files after launch.
- Record deployed commit hash.
- Review `storage/logs` privately for errors.
- Monitor result checker, login, scratch cards, and mail delivery.
- In Super Admin, use System Maintenance > Clear All Cache > Optimize Application after deployment or updates.
- Use System Updates only as a safe package log until a reviewed installer exists.
