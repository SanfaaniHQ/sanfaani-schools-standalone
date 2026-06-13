# Update Runbook

## Purpose

Use this runbook to review a guided update package, update preflight result, update history, or update handoff. The current update system is review and preflight tooling only.

## Access

Super Admin, local owner, or approved Sanfaani support can use update pages. School Admins should be informed about maintenance windows but should not upload packages, review internals, or approve update readiness unless they are also the local owner.

## Normal Workflow

1. Confirm the school, environment, current version, target version, and update package source.
2. Verify a recent backup before update review.
3. Upload or inspect the private update package through the guided update area.
4. Confirm manifest product, edition, deployment mode, version range, PHP/Laravel requirements, extensions, checksum, and changed files.
5. Run preflight and review license, installer, database, migration repository, storage, package directory, backup, and migration-review status.
6. Record manual migration notes, maintenance window, restore-based recovery plan, and owner approval.
7. Do not treat preflight as an applied update.

## Common Issues

- Package manifest targets the wrong edition or deployment mode.
- Checksum, extension, MIME type, or size validation fails.
- Changed-file metadata targets protected or unsafe paths.
- Backup readiness fails.
- Migration notes are missing or misunderstood.
- The school expects automatic download or self-applying updates.

## First Checks

- Confirm Stage 22 update finalization docs match the installed build.
- Confirm update preflight status and history entries.
- Confirm backup verification is recent enough.
- Confirm package remains private and is not extracted by the web UI.
- Confirm audit logs redact secrets, private paths, package payloads, and protected migration details.

## Safe Commands And UI Checks

```bash
php artisan test --filter=Update
php artisan route:list
php artisan standalone:status
php artisan deployment:check-readiness
php artisan release:check-readiness
```

Use the guided update UI for manifest review and preflight only.

## What Support Should Not Do

- Do not promise auto-download, online update servers, destructive auto-apply, or automatic recovery execution.
- Do not extract package contents from the web UI.
- Do not run Git, dependency, migration, or shell update steps from an ordinary support chat.
- Do not continue when backup readiness fails.
- Do not expose raw package payloads, app keys, license keys, database passwords, mail passwords, provider tokens, raw `.env` values, or private paths.

## Escalation Points

Escalate when protected paths are mentioned, preflight blocks a production school, migration risk is unclear, backup readiness is missing, package validation fails unexpectedly, or the update appears to target a different product, edition, or deployment mode.

## Data And Privacy Warnings

Update logs and manifests can reveal file names, version metadata, and environment readiness. They must not include secrets or private server paths.

## Backup And Security Reminders

Updates require a verified backup when backup enforcement is enabled. Recovery means restoring a verified backup manually unless a future tested recovery executor exists.

## Related Docs

- [Update System Finalization](../standalone/update-system-finalization.md)
- [Update System Plan](../updates/update-system-plan.md)
- [Update Manifest Format](../updates/update-manifest-format.md)
- [Update Package Release Workflow](../release/update-package-release-workflow.md)
- [Pre-Update Backup Checklist](../backups/pre-update-backup-checklist.md)
