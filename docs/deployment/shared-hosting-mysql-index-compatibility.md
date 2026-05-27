# Shared Hosting MySQL Index Compatibility

This note documents the shared-hosting migration compatibility hotfix for Namecheap/cPanel environments that report:

```text
SQLSTATE[42000]: 1071 Specified key was too long; max key length is 1000 bytes
```

## Why This Happens

Some shared-hosting MySQL or MariaDB installations still enforce a 1000-byte index key limit. With `utf8mb4`, each indexed string character can use up to 4 bytes, so wide composite indexes can exceed the limit even when the column list looks reasonable.

Sanfaani Schools sets Laravel's default string length to 191 through `Schema::defaultStringLength(191)` in `AppServiceProvider::boot()`. This keeps default string indexes compatible with older shared-hosting limits while preserving normal application behavior.

## Shortened Indexes

The architecture hardening migration keeps the existing index names but narrows the indexed columns:

- `student_results_public_lookup_idx` now indexes `school_id`, `student_id`, `academic_session_id`, and `term_id`.
- `student_results_publish_scope_idx` now indexes `school_id`, `school_class_id`, `academic_session_id`, and `term_id`.

The CBT architecture migration keeps the existing index name but narrows the pool lookup:

- `cbt_question_banks_pool_idx` now indexes `school_id` and `difficulty`.

## Preserved Functionality

The shortened indexes still preserve the main tenant and academic scoping used by result lookup, publishing lists, and CBT question-bank filtering. The removed columns remain available for query filtering; they are just no longer part of the composite index that exceeded shared-hosting key limits.

This is a shared-hosting compatibility hotfix, not a business workflow change.

## Deployment Guidance

For demo or production-like shared hosting, run normal forward migrations only:

```bash
php artisan migrate --force
```

Do not use `migrate:fresh` on production or demo hosting. The application environment guard blocks destructive commands, and production/demo data must be protected through forward-only migrations and approved backups.

Before running migrations on an existing demo environment:

- Confirm the target database is the demo/staging database.
- Confirm a backup exists.
- Confirm `APP_ENV` and `APP_DEBUG` are production-style values.
- Confirm `public/build.zip` is not used as a deployment artifact.
