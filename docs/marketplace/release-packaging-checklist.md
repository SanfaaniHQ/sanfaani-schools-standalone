# Release Packaging Checklist

This checklist should be completed before a marketplace or direct sales package is assembled.

## Code And Assets

- Confirm branch and version.
- Run the full test suite.
- Run `php artisan route:list`.
- Run `git diff --check`.
- Build frontend assets in a clean release workspace when assets are intentionally included.
- Do not modify or include `public/build.zip`.

## Package Safety

- Remove `.env`, local databases, logs, caches, sessions, backups, private uploads, and debug dumps.
- Exclude `vendor` and `node_modules`.
- Exclude generated archives and temporary files.
- Confirm `.env.marketplace.example` contains placeholders only.
- Confirm docs mark planned features clearly.

## Commercial Readiness

- Confirm license terms.
- Confirm support policy.
- Confirm refund/customization boundaries.
- Confirm reseller and white-label terms when applicable.
- Confirm update and backup guidance is included.

## Validation

- Run `php artisan marketplace:validate-package`.
- Review `docs/marketplace/package-validation-checklist.md`.
- Keep a release note with package mode and channel.
