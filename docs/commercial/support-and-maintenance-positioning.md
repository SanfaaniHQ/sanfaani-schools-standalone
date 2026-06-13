# Support And Maintenance Positioning

Use this guide to explain what support can include, what needs separate scope, and what buyers should not assume.

## Available Support Categories

- Onboarding support: first walkthrough, role orientation, and setup guidance.
- Deployment support: hosting review, installer guidance, storage permissions, scheduler/cron, mail readiness, and system health review.
- Module configuration support: admissions, attendance, finance, LMS/CBT, live classes, communications, branding, and reports.
- Bug support: reproduction, triage, evidence collection, and escalation.
- Backup/update support: backup verification guidance, guided update preflight, update package review, and restore-plan discussion.
- Training support: admin, teacher, accountant, result officer, and support staff orientation.
- Commercial customization support: scoped paid changes after written approval.

## What Support Should Clarify

- Support channel and response goals.
- License term and renewal expectations.
- Hosting owner.
- Backup owner.
- Update review owner.
- Data migration owner.
- Training schedule.
- Escalation path.

## Not Automatically Included

- Unlimited custom feature development.
- Payment gateway integration.
- External provider API credentials, paid provider accounts, or provider contracts.
- Data entry or manual migration unless scoped.
- Hosting cost.
- Domain or SSL cost.
- SMS, WhatsApp, mail, or payment processor fees.
- Legal compliance guarantees.
- Enforcement of the school's internal operational policies.
- Fixing GitHub dependency vulnerabilities as part of packaging docs.

## Maintenance Boundaries

- Updates are guided/manual package review and preflight, not auto-download or destructive auto-apply.
- Backup foundations track metadata and verification; automated production restore is not included.
- License diagnostics are local/commercial readiness foundations, not a remote license server.
- Payment gateway automation and SaaS billing are not included unless separately implemented and contracted.
- Provider API automation for live classes is future work.

## Evidence Rules

Support should collect sanitized screenshots, route names, error text, timestamps, affected role, affected school, and module context. Do not collect raw `.env`, app keys, database passwords, SMTP passwords, license keys, provider tokens, SQL dumps, full backups, payment secrets, or private server paths through ordinary chat.

## Related Docs

- [Support Runbooks](../support/support-runbooks.md)
- [Issue Triage](../support/issue-triage.md)
- [Implementation Handoff Checklist](implementation-handoff-checklist.md)
- [Module Boundaries And Limitations](module-boundaries-and-limitations.md)
