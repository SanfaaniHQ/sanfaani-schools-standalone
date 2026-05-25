# Sanfaani Schools Documentation

This directory is the source structure for the future documentation site at `https://doc.sanfaani.net`.

The docs must reflect the current product honestly. The following foundations exist now:

- Deployment mode and license mode foundation.
- Feature flag and module gating foundation.
- SaaS, single-school, and managed deployment behavior separation.
- Tenant isolation audit and security hardening coverage.
- Standalone installer foundation for single-school deployments.
- Licensing activation, validation, entitlement, and audit foundation.
- Demo automation foundation.
- Role-based guided onboarding foundation.
- Lead nurturing, marketing automation, sales task, and unsubscribe foundation.

The following systems are planned and must not be described as complete:

- Update manager.
- Backup manager.
- Marketplace packaging automation.
- Full billing/payment automation.
- Full parent and student portal workflows.
- Full white-label branding storage and reseller tooling.

Use `SUMMARY.md` as the documentation table of contents, `documentation-url-map.md` as the public URL contract, and `documentation-maintenance-workflow.md` as the rule for keeping docs synchronized with code changes.

## Audiences

- SaaS customers.
- Single-school buyers.
- Managed clients.
- Marketplace buyers.
- Developers.
- Deployment engineers.
- Support teams.
- Sales and onboarding teams.
- Resellers.
- White-label buyers.

## Editing Rules

- Do not document planned systems as available.
- Prefer exact config keys, service names, routes, and feature names.
- Update release notes and changelog files when user-visible behavior changes.
- Keep secrets, SMTP credentials, license keys, payment keys, backups, logs, and database dumps out of documentation examples.
