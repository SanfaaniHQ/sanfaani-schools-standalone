# Shared Hosting MySQL Index Compatibility

This note documents the shared-hosting migration compatibility hotfix for Namecheap/cPanel environments such as `demo.sanfaani.net` that report:

```text
SQLSTATE[42000]: 1071 Specified key was too long; max key length is 1000 bytes
```

## Why This Happens

Some shared-hosting MySQL or MariaDB installations still enforce a 1000-byte index key limit. With `utf8mb4`, each indexed string character can use up to 4 bytes, so wide composite indexes can exceed the limit even when the column list looks reasonable.

Sanfaani Schools sets Laravel's default string length to 191 through `Schema::defaultStringLength(191)` in `AppServiceProvider::boot()`. This keeps default string indexes compatible with older shared-hosting limits while preserving normal application behavior.

Composite indexes with multiple string columns remain risky even with a 191-character default because two or more indexed strings can still exceed the shared-hosting key budget. Long automatic foreign key names can also fail because MySQL identifiers are limited to 64 characters.

## Shortened Indexes

The architecture hardening migration keeps the existing index names but narrows the indexed columns:

- `student_results_public_lookup_idx` now indexes `school_id`, `student_id`, `academic_session_id`, and `term_id`.
- `student_results_publish_scope_idx` now indexes `school_id`, `school_class_id`, `academic_session_id`, and `term_id`.

The CBT architecture migration keeps the existing index name but narrows the pool lookup:

- `cbt_question_banks_pool_idx` now indexes `school_id` and `difficulty`.

The marketing automation sequence migration keeps the existing index name but narrows the trigger lookup:

- `marketing_sequences_status_trigger_idx` now indexes `status`.

Two early composite uniqueness rules were kept but made safer by limiting column lengths:

- Spatie permission and role `name`/`guard_name` columns are constrained to shared-hosting-safe lengths.
- School class `name`/`section` columns are constrained to shared-hosting-safe lengths while keeping per-school uniqueness.

## Shortened Foreign Key Names

The marketing automation step and enrollment migrations use explicit short constraint names:

- `marketing_steps_sequence_fk`
- `marketing_enrollments_sequence_fk`

These replace the long generated names that can exceed MySQL's 64-character identifier limit:

- `marketing_automation_steps_marketing_automation_sequence_id_foreign`
- `marketing_automation_enrollments_marketing_automation_sequence_id_foreign`

## Preserved Functionality

The shortened indexes still preserve the main tenant, academic, status, and pool scoping used by result lookup, publishing lists, CBT question-bank filtering, and marketing automation lists. The removed columns remain available for query filtering; they are just no longer part of composite indexes that exceeded shared-hosting key limits.

This is a shared-hosting compatibility hotfix, not a business workflow change.

## Deployment Guidance

For demo or production-like shared hosting, run normal forward migrations only:

```bash
php artisan migrate --force
```

Do not use `migrate:fresh` on production or demo hosting. `EnvironmentGuard` blocks destructive commands, and production/demo data must be protected through forward-only migrations and approved backups.

If a migration failed halfway on an empty demo database and left a partial table, drop only the partial table that failed after confirming the database is disposable demo data. Do not drop the full database and do not run destructive commands against production or client data.

Before running migrations on an existing demo environment:

- Confirm the target database is the demo/staging database.
- Confirm a backup exists.
- Confirm `APP_ENV` and `APP_DEBUG` are production-style values.
- Confirm `public/build.zip` is not used as a deployment artifact.

## Shared Hosting Operations Notes

- Composer may not be globally available on cPanel. If a local Composer PHAR exists, use `php composer.phar install --no-dev --optimize-autoloader`.
- For public GitHub repositories, HTTPS clone is an acceptable fallback when SSH public-key access is not configured.
- For private repositories, configure a deploy key before using SSH clone.
- The domain document root must point to Laravel `public`, or use the reviewed shared-hosting public-folder workaround.
- Do not use `public/build.zip` as a runtime artifact; deploy reviewed `public/build` assets only.
- Use `QUEUE_CONNECTION=sync` or `QUEUE_CONNECTION=database` on shared hosting.
- Configure the scheduler through cPanel cron, for example: `* * * * * /usr/local/bin/php /home/account/sanfaani-schools/artisan schedule:run >> /dev/null 2>&1`.
- Run `php artisan security:audit` with production-style env overrides when local shell values are not production-safe.
- Readiness warnings are advisory unless a command marks them as `fail`.
