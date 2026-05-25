# Backup System Plan

The backup manager is planned and not implemented yet.

## Current State

Some deployment docs describe manual backup discipline. The product does not currently ship a complete backup manager.

## Planned Scope

- Database backup orchestration.
- Storage backup orchestration.
- Backup retention rules.
- Backup download authorization.
- Backup audit logs.
- Managed backup visibility for managed deployments.
- Pre-update backup enforcement.

## Safety Rules

- Backups must never be committed to Git.
- Backups must not be stored in public web roots.
- Backup download routes must be authorized and tenant-safe.
- Managed backup tools must not touch non-target client data.
