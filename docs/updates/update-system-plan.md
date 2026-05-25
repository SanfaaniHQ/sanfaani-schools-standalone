# Update System Plan

The update manager is planned and not implemented yet.

## Current State

The deployment and feature foundations include update-related visibility gates and placeholders:

- `update_manager` feature flag.
- deployment behavior entries for standalone and managed update visibility.

These gates exist so future update screens can be added safely without exposing unfinished behavior.

## Planned Scope

- Version checks.
- Release package validation.
- Pre-update backup requirement.
- Migration readiness checks.
- Maintenance mode guidance.
- Update audit logs.
- Rollback guidance where possible.

## Safety Rules

- Never run destructive updates without explicit confirmation.
- Never expose secrets in update logs.
- Never assume shell access on shared hosting.
- Managed and marketplace update paths must be documented separately.
