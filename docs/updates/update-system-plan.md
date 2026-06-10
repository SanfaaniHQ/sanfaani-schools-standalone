# Update System Plan

The guided update foundation is now implemented as a safe metadata and preflight layer. Real update application, backup orchestration, marketplace packaging, deployment automation, and external update downloads remain planned.

## Current State

The deployment and feature foundations include update-related visibility gates and a guided update manager:

- `update_manager` feature flag.
- deployment behavior entries for platform, standalone, and managed update visibility.
- package metadata storage.
- manifest validation.
- preflight checks.
- update logs.
- rollback plan metadata.
- controller-level audit logs for package upload, preflight, and ready-for-manual-update actions.

These gates exist so future update delivery can be added safely without exposing unfinished behavior.

## Planned Scope

- Version checks.
- Release package validation.
- Pre-update backup requirement.
- Migration readiness checks.
- Maintenance mode guidance.
- Update audit logs.
- Rollback guidance where possible.
- Metadata-only package handling: uploaded packages are stored privately, checksums and manifest metadata are recorded, and archives are not extracted or applied from a web request.

## Out Of Scope For This Foundation

- External update downloads.
- Package extraction or code patching.
- Browser-triggered migrations.
- Browser-triggered shell commands.
- Backup orchestration.
- Marketplace packaging.
- Deployment automation.
- Billing or payment workflow.

## Safety Rules

- Never run destructive updates without explicit confirmation.
- Never expose secrets in update logs.
- Never assume shell access on shared hosting.
- Managed and marketplace update paths must be documented separately.
- Require a recent verified backup before readiness can pass when backup enforcement is enabled.
- Reject package uploads that do not match allowed archive extensions, MIME types, size limits, manifest checksums, or required manifest metadata.
