# Update Package Release Workflow

- Validate version and channel metadata.
- Review update manifest fields, including target product, target edition, deployment modes, target version, current-version range, PHP/Laravel requirements, and required extensions.
- Confirm changed-file metadata and ZIP entries do not target `.env`, `public/build.zip`, protected migrations, absolute paths, or traversal paths.
- Confirm update entitlement expectations.
- Run update preflight.
- Confirm database and migration repository readiness.
- Confirm migration warnings are documented and migrations are not run from the web wizard.
- Confirm backup requirement status.
- Confirm rollback plan metadata exists.
- Confirm update and audit logs do not expose secrets, private paths, raw package payloads, or protected migration internals.
- Confirm controller audit logs exist for upload and preflight actions.
- Confirm controller audit logs exist for update center views, package validation, package review, preflight, plan generation, and history views.
- Confirm the package remains metadata-only: not extracted, not applied, and no migrations run from the web wizard.
- Do not download, extract, auto-apply, roll back, run Git/Composer/npm, or deliver updates by report/email/SMS/WhatsApp in this release foundation.
