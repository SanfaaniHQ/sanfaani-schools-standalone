# Sanfaani Schools

Sanfaani Schools is a Laravel school management and result access platform by Sanfaani Ltd. It supports school onboarding, role-based dashboards, student records, academic setup, result entry, CSV result upload, publishing, scratch card requests, and public result checking.

## Production Launch

- Launch URL: https://schools.sanfaani.net
- Company: Sanfaani Ltd
- Support email: sanfaanisaas@gmail.com
- Phone/WhatsApp: +2349010172138
- Address: Kehinde Shafi Junction, Islamic Village, along Whitefield Hotel, Ilorin, Kwara State, Nigeria

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- MySQL or MariaDB
- Enabled PHP extensions required by Laravel and the configured database driver

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Use `MAIL_MAILER=log` locally unless you are testing a real SMTP provider. Do not commit real `.env` files, SMTP credentials, payment keys, logs, database dumps, or backups.

## Production Notes

Production should run with `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://schools.sanfaani.net`, a secure `.env`, and a writable `storage` and `bootstrap/cache` directory. Run `php artisan storage:link` so uploaded platform and school logos can render.

Before the final Namecheap push, run migrations, clear caches, build assets, list routes, confirm `.env` and private files are not staged, then use the final deployment checklist.

See:

- `docs/deployment/namecheap-production-deployment.md`
- `docs/deployment/backup-and-restore.md`
- `docs/testing/final-deployment-test-checklist.md`
- `docs/notifications/smtp-mail-setup.md`
- `docs/security/pre-deployment-security-checklist.md`
- `docs/marketplace/codecanyon-readiness.md`
