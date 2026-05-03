# CodeCanyon Readiness

Sanfaani Schools may be prepared for a future CodeCanyon marketplace release after production stabilization.

## Item Overview

Laravel school management and result access platform with role-based dashboards, student records, academic setup, result entry, CSV uploads, result publishing, scratch card requests, public result checking, platform branding, and multilingual foundation.

## Requirements

- PHP 8.3+
- MySQL or MariaDB
- Composer
- Node.js/npm for asset builds
- Writable `storage` and `bootstrap/cache`

## Installation

1. Upload package.
2. Run `composer install --no-dev --optimize-autoloader`.
3. Copy `.env.example` to `.env`.
4. Set app, database, mail, and optional payment values.
5. Run `php artisan key:generate`.
6. Run `php artisan migrate --force`.
7. Run `php artisan storage:link`.
8. Build assets or upload `public/build`.

## cPanel Deployment

Document root should point to Laravel `public` where possible. `.env`, app files, storage, logs, database dumps, and backups must not be publicly accessible.

## Demo Data Policy

The marketplace package must not include real school, staff, student, guardian, result, scratch card, payment, SMTP, or production environment data.

## Secrets Policy

Do not package:

- Production `.env`
- SMTP credentials
- Gmail app passwords
- Paystack or Flutterwave secret keys
- Logs
- Backups
- Private uploads
- Real database dumps

## Screenshots Checklist

- Landing page
- Login
- Super Admin dashboard
- Platform settings
- School management
- School Admin dashboard
- Student list and Student 360 profile
- Manual result entry
- CSV upload
- Result publishing
- Scratch card request and approval
- Public result checker
- Result print view

## Support Policy

Define supported installation environments, response windows, excluded customizations, and paid customization boundaries before marketplace submission.

## Versioning and Updates

Use semantic versioning. Include database migration notes and update steps with every release.

## Future Installer Plan

A guided installer can be added later for database setup, admin creation, storage link checks, and environment validation.

## Asset Credits

List all third-party UI assets, icons, fonts, templates, and libraries before packaging.
