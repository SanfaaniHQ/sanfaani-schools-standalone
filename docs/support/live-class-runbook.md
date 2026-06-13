# Live Class Runbook

## Purpose

Use this runbook when supporting manual live-class scheduling, links, status changes, provider labels, LMS links, or expectations around Google Meet, Zoom, and Microsoft Teams.

## Access

School Admins can manage live classes in the current school. Teachers can manage only assigned class and subject scopes. Accountants, Result Officers, Students, Parents, and public users do not manage live classes by default.

## Normal Workflow

1. Confirm the class, optional subject, session, term, teacher, and time zone.
2. Confirm the external room was created manually in the provider.
3. Paste a valid `http` or `https` meeting link into Sanfaani Schools.
4. Add optional meeting password and recording URL only when the provider supplies them.
5. Link LMS classroom or material only when the school and academic scope match.
6. Move status from scheduled to live, completed, or cancelled as the session progresses.

## Common Issues

- Teacher is not assigned to the selected class or subject.
- Meeting link is invalid or missing.
- Provider expectation is for generated Google Meet, Zoom, or Microsoft Teams rooms.
- LMS classroom or material scope does not match the live class.
- Cancelled or completed session cannot be started.
- Meeting password was shared in an unsafe channel.

## First Checks

- Confirm user role and teacher assignment.
- Confirm provider is the active manual link provider.
- Confirm meeting and recording URLs are valid.
- Confirm LMS link school, class, subject, session, and term match.
- Review live-class audit logs and communication notification logs for schedule/update/cancel events.

## Safe Commands And UI Checks

```bash
php artisan route:list
php artisan test --filter=Dashboard
php artisan test --filter=Standalone
```

Use the Live Classes list/detail pages and Communication Center logs as primary checks.

## What Support Should Not Do

- Do not promise real Google Meet, Zoom, or Microsoft Teams API automation.
- Do not store provider credentials, OAuth tokens, webhooks, or generated room payloads.
- Do not paste meeting passwords into tickets or public chats.
- Do not bypass teacher assignment or cross-school LMS scope checks.
- Do not describe live classes as offline-capable.

## Escalation Points

Escalate when assigned teachers are wrongly blocked, unauthorized users can access live classes, meeting passwords appear in audit or notification metadata, or provider metadata behaves as if disabled providers are active.

## Data And Privacy Warnings

Meeting passwords and private invite details must be shared only through approved school channels. Audit and notification logs should contain safe IDs and summaries only.

## Backup And Security Reminders

Live-class records and links are database records, so include them in normal backup planning. External provider recordings are outside Sanfaani backup scope unless the school separately stores them.

## Related Docs

- [Live Class Foundation](../standalone/live-class-foundation.md)
- [Live Class Provider Abstraction](../standalone/live-class-provider-abstraction.md)
- [School Live Classes Operations](../school-operations/live-classes.md)
- [School Communications](../school-operations/communications.md)
