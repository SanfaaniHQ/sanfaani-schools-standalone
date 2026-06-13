# Support Playbook

This playbook is the high-level support policy for Sanfaani Schools. For practical Stage 23 module procedures, start with [Support Runbooks](support-runbooks.md) and [Issue Triage](issue-triage.md).

## Support Scope

Support teams help schools and managed clients with setup, access, results, scratch cards, SMTP, licensing status, demo sessions, onboarding, troubleshooting, backup readiness, guided update preflight, offline attendance sync, finance reports, LMS/CBT links, live classes, communications, branding, reports, and release handoff.

## Tenant Safety

- Confirm the active school before inspecting school-owned records.
- Do not expose one school's data to another school.
- Use Super Admin global views only for platform support tasks.
- Log sensitive actions through existing audit/support systems where available.
- Collect sanitized screenshots and avoid raw secrets, backup contents, SQL dumps, license keys, provider tokens, and private server paths.

## Product Boundaries

- Laravel is the source of truth.
- Standalone is for one private school installation.
- Offline support is attendance-focused browser capture and sync, not full offline portal support.
- Live classes use manual links and provider abstraction metadata, not real provider API automation.
- Guided updates provide local package review and preflight checks, not auto-download or destructive auto-apply.
- Installer/license tooling is local readiness support, not SaaS billing or a remote license server.
- Branding does not include the future Next.js public website.

## Escalation

Escalate to engineering when:

- tenant boundary behavior is unclear;
- license validation fails unexpectedly;
- installer lock state is inconsistent;
- backup verification is missing before risky maintenance;
- update package validation or preflight fails unexpectedly;
- offline attendance sync conflicts repeat without a clear cause;
- demo expiry or credentials behave incorrectly;
- migrations, queues, scheduler, storage, or database checks fail.

## Support Runbook Map

- [Issue Triage](issue-triage.md)
- [Backup And Restore Runbook](backup-restore-runbook.md)
- [Update Runbook](update-runbook.md)
- [Installer And License Runbook](installer-license-runbook.md)
- [Offline Sync Runbook](offline-sync-runbook.md)
- [Release Handoff Checklist](release-handoff-checklist.md)
