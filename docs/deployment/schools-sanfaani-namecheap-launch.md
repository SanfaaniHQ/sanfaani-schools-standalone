# Sanfaani Schools Namecheap Launch Guide

Launch domain: `https://schools.sanfaani.net`

This guide is retained as a launch overview. Use `namecheap-production-deployment.md` as the primary production deployment checklist.

## Local Preparation

```bash
git status
composer install
npm.cmd install
php artisan migrate
php artisan optimize:clear
npm.cmd run build
php artisan route:list
```

Test:

- `/`
- `/features`
- `/pricing`
- `/contact`
- `/demo`
- `/privacy-policy`
- `/terms`
- `/result-checker`
- `/login`

## Namecheap/cPanel Preparation

- Create subdomain `schools.sanfaani.net`.
- Use PHP 8.3+.
- Enable common Laravel extensions including fileinfo, mbstring, openssl, pdo_mysql, tokenizer, xml, curl, and zip where available.
- Point document root to Laravel `public` if possible.
- Create the production database and database user.
- Keep `.env`, backups, logs, and private uploads outside public access.

## Production Environment

```dotenv
APP_NAME="Sanfaani Schools"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://schools.sanfaani.net
MAIL_MAILER=smtp
```

Set SMTP and payment values only in the server `.env`. Do not commit real credentials.

## Launch Checklist

- Back up before deployment.
- Back up after deployment.
- Login works.
- Super Admin dashboard works.
- Platform logo upload works.
- School logo upload works.
- Result checker works.
- SMTP or log mail test works.
- `APP_DEBUG=false`.
- `.env` is not accessible.
- `storage` and `bootstrap/cache` are writable.
