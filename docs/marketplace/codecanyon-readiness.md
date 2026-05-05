# CodeCanyon Readiness

Sanfaani Schools may be prepared for a future CodeCanyon marketplace release after production stabilization.

## Item Overview

Laravel school management and result access platform with separate Super Admin login, role-based dashboards, student records, student promotion, academic setup, result entry, CSV uploads, result publishing, result access policies, subscriptions/features, scratch card requests, private-card public result checking, platform branding, report card settings, lead requests, system maintenance, safe update logging, and multilingual foundation.

V1.1 includes teacher assignment/result workflow, support threads, dedicated school public pages, and encrypted dashboard-managed payment/mail settings.

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
- Admin login
- Platform settings
- School management
- Plans, subscriptions, and result access policy
- Lead request inbox
- System maintenance and update log
- School Admin dashboard
- Result System
- Student list and Student 360 profile
- Student promotion
- Manual result entry
- CSV upload
- Result publishing
- Scratch card request and approval
- Public result checker
- Result print view

## Support Policy

Define supported installation environments, response windows, excluded customizations, and paid customization boundaries before marketplace submission.

## Versioning and Updates

Use semantic versioning through `APP_VERSION`. Include database migration notes and update steps with every release. Update packages should preserve `.env`, storage uploads, database data, school/student/result records, scratch cards, payments, leads, and audit logs.

## Future Installer Plan

A guided installer can be added later for database setup, admin creation, storage link checks, and environment validation.

## Asset Credits

List all third-party UI assets, icons, fonts, templates, and libraries before packaging.
