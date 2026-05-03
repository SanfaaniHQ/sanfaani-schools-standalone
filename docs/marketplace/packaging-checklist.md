# Packaging Checklist

Before creating a marketplace or client package:

- Remove `.env`.
- Remove `storage/logs/*`.
- Remove `storage/app/backups/*`.
- Remove private uploads.
- Remove real school, student, guardian, result, scratch card, payment, and support data.
- Remove local database dumps.
- Remove `node_modules`.
- Remove `vendor` unless the target package intentionally includes dependencies.
- Keep `composer.lock`.
- Keep `package-lock.json`.
- Run `composer install --no-dev --optimize-autoloader` in release verification.
- Run `npm install` and `npm run build`.
- Run `php artisan route:list`.
- Run syntax checks.
- Review docs for production wording.
- Include installation, update, support, and changelog documentation.
- Include asset credits.

This project should not be packaged with real school/student data, real SMTP credentials, payment keys, or production `.env`.
