# Tenant Isolation

The tenant boundary is `schools.id`.

Operational school data must be queried through the active school context or an explicit `school_id` relationship. Super Admin platform visibility is intentionally global where platform workflows require it.

## Existing Protections

- `CurrentSchoolService` resolves active school and role context.
- `EnsureValidSchoolContext` protects school workspace routes.
- `EnsureActiveRole` validates active role context.
- `SchoolAuthorizationService` evaluates school role-feature access.
- Tenant isolation tests cover high-risk cross-school access paths.

## Platform-Global Data

The following are platform-scoped by design:

- `School`
- subscription plans and plan features
- platform marketing and CRM records
- support escalation views
- platform audit/security/status views

## School-Scoped Data

Student, result, teacher assignment, scratch-card, support, communication, CBT, and school setting records must remain school-scoped.

## Reference Audit

See `docs/security/tenant-isolation-audit.md` for the detailed tenant isolation audit and patch record.
