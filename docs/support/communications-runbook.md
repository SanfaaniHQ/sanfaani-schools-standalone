# Communications Runbook

## Purpose

Use this runbook when supporting the Communication Center, notification logs, templates, operational hooks, and bulk communication boundaries.

## Access

School Admins can review school-scoped communication logs and manage templates. Other school roles do not manage communication templates by default. Super Admin support must operate inside the intended school context for school communication records.

## Normal Workflow

1. Open the Communication Center from the school workspace.
2. Review operational notification logs by event type, channel, status, and related record.
3. Create or update school-scoped templates.
4. Use existing bulk communication tools where enabled.
5. Confirm operational hooks are creating logs for live classes, LMS material publishing, finance invoices/payments, and admission status changes.

## Common Issues

- Expected SMS or WhatsApp provider delivery did not occur.
- Template is inactive, wrong channel, or wrong audience.
- Log row is deferred or logged rather than sent.
- Bulk communication skipped a future channel.
- Private details were entered into a template body.

## First Checks

- Confirm the active school and School Admin access.
- Check the notification log event type, channel, status, recipient type, and related record.
- Check the template key, active state, channel, audience, and escaped display.
- Confirm email configuration separately when email delivery is expected.
- Confirm the module event actually occurred, such as live class scheduled or finance invoice generated.

## Safe Commands And UI Checks

```bash
php artisan route:list
php artisan standalone:status
php artisan deployment:check-readiness
```

Use Communication Center, Notification Logs, Templates, and existing Bulk Communication pages as primary checks.

## What Support Should Not Do

- Do not claim WhatsApp Business API, SMS provider API, push notifications, webhooks, or provider credential storage are implemented.
- Do not paste provider secrets, OAuth tokens, meeting passwords, CBT answers, admission documents, private finance notes, raw passwords, or reset tokens into templates.
- Do not send cross-school broadcasts from a school-scoped support request.
- Do not treat operational logs as proof of external delivery unless a delivery provider is actually implemented and verified.

## Escalation Points

Escalate when communication logs cross school boundaries, private payloads appear in logs, email delivery fails after SMTP is verified, template rendering is unsafe, or a production communication was sent to the wrong audience.

## Data And Privacy Warnings

Communication logs should contain minimized recipient labels and safe summaries only. Never store meeting passwords, provider payloads, secrets, CBT private data, admission documents, or private finance notes in logs.

## Backup And Security Reminders

Templates and logs are database records and should be covered by normal backups. Review communication templates after restore or migration to confirm no private data was introduced.

## Related Docs

- [Communication And Notification Hardening](../standalone/communication-notification-hardening.md)
- [School Communications](../school-operations/communications.md)
- [Email And Notification Flow](../notifications/email-and-notification-flow.md)
- [SMTP Mail Setup](../notifications/smtp-mail-setup.md)
