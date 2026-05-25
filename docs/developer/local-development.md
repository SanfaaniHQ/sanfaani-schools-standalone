# Local Development

## Requirements

- PHP 8.3+.
- Composer.
- Node.js and npm.
- MySQL or MariaDB.
- Required PHP extensions for Laravel and the configured database driver.

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan test
```

Use `MAIL_MAILER=log` locally unless testing mail behavior.

## Commercial Config

Use `.env.example` as the source of available deployment, installer, licensing, demo, onboarding, and marketing keys.

Do not commit real `.env` files, credentials, license keys, payment keys, logs, dumps, or backups.
