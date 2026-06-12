# Communication And Notification Hardening

Stage 18 adds a safe school-operations communication layer for Sanfaani Schools Standalone. It improves the existing bulk communication, Laravel notification, mail, audit, dashboard, and live-class foundations without rebuilding them.

## What Is Available

- School Admins can open **Communication Center** from the school workspace.
- School Admins can view school-scoped operational notification logs.
- School Admins can create and update school-scoped notification templates.
- Live class scheduled, updated, and cancelled actions create safe notification log entries.
- LMS material publishing, finance invoice generation, finance payment recording, and admission status changes create safe operational log entries.
- The standalone dashboard marks communication notification hardening as available.
- Existing bulk communication remains available and unchanged for school-scoped batch messages.

## Supported Channels

Stage 18 supports these channel labels:

- `database` for local in-app/logged notification records.
- `log` for local operational logging only.
- `email` as an email-ready channel.
- `sms` as an SMS-ready placeholder.
- `whatsapp` as a WhatsApp-ready placeholder.

SMS and WhatsApp provider APIs are not implemented. Email-ready notification logs do not rebuild the existing mail system and do not create a marketing automation engine.

## Notification Logs

Operational logs are stored in `school_notification_logs`. Each record belongs to one school and stores:

- event type;
- channel;
- recipient type and minimized recipient label;
- safe subject and message summary;
- status such as `logged` or `deferred`;
- safe related model identifiers;
- safe metadata.

Logs do not store raw provider payloads, passwords, OAuth tokens, provider secrets, CBT answers, admission documents, private payment notes, or live-class meeting passwords.

## Templates

Templates are stored in `school_notification_templates`. Template keys are unique per school. Templates can be active or inactive and include:

- template key;
- title;
- optional subject;
- body;
- channel;
- audience type.

Templates are escaped when displayed in Blade. They are rendered as plain text by the service layer.

## Recipient Scoping

Recipient resolution is school-scoped. Direct user, student, class, role, and live-class audience recipients are validated against the active school before a log row is created. Cross-school templates and logs are blocked by controller checks and tests.

## Operational Hooks

Implemented hooks:

- live class scheduled, updated, cancelled;
- LMS material published;
- finance invoice generated;
- finance payment recorded;
- admission status changed.

These hooks create logs only. They do not send SMS, WhatsApp, push notifications, or provider API calls.

## Audit Trail

Stage 18 writes safe audit events for:

- `communication_template_created`;
- `communication_template_updated`;
- `communication_notification_logged`.

Audit metadata includes safe IDs, event type, channel, recipient type, status, actor ID, and related model identifiers. It does not include secrets, passwords, tokens, or raw external payloads.

## Deferred Boundaries

Not implemented in Stage 18:

- WhatsApp Business API;
- SMS provider API;
- provider credential storage;
- Mailgun, SendGrid, Twilio, Termii, Zoom, Google Meet, or Microsoft Teams API integration;
- webhooks;
- push notifications;
- AI-generated message sending;
- public marketing campaigns;
- cross-school broadcasts;
- parent/student portal communication.
