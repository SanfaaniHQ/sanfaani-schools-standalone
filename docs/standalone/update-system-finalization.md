# Update System Finalization

Stage 22 finalizes the existing guided update foundation for standalone and managed installations. It does not create a duplicate updater and does not apply updates automatically.

## Available

- Private update package upload and metadata storage.
- Manifest validation for required fields, checksum, product, edition, deployment mode, target version, current-version range, PHP, Laravel, required extensions, and safe changed-file paths.
- ZIP entry inspection for protected paths when the PHP ZIP extension can read the archive.
- Preflight checks for current version, compatibility, license, installer, database, migration repository, storage, package directory, backup-before-update readiness, and migration review.
- Manual review-plan metadata for support and local owners.
- Audit events for `update_center_viewed`, `update_package_validated`, `update_package_reviewed`, `update_preflight_ran`, `update_plan_generated`, and `update_history_viewed`.
- Standalone dashboard and system-health status showing that update finalization is available.

## Protected Boundaries

- Packages remain private and are not extracted by the web UI.
- The update manager rejects `.env`, `.env.local`, `public/build.zip`, the protected result-publication migration, absolute paths, and traversal paths.
- Diagnostics and logs must not expose app keys, license keys, database passwords, mail passwords, provider tokens, raw `.env` values, private backup paths, raw package payloads, private server paths, or protected migration internals.
- Backup readiness requires a recent verified backup when update backups are required.

## Deferred

Online update servers, auto-download, marketplace delivery, destructive auto-apply, automatic rollback execution, Git/Composer/npm operations, and report/email/SMS/WhatsApp update delivery remain out of scope.
