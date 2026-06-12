# School Communications

The school Communication Center is available to School Admins from the school workspace. It reuses existing school context, role-feature authorization, audit logs, Laravel notifications, and bulk communication foundations.

## Communication Center

Use **Communication Center** to review:

- recent operational notification logs;
- active and inactive notification templates;
- existing bulk communication batches;
- provider-ready channel boundaries.

The center is school-scoped. It does not expose public routes and does not show cross-school logs.

## Notification Logs

Notification logs are an operational outbox. They show what the system prepared or logged for school operations. Each log stores a safe summary, event type, channel, recipient type, status, related record identifiers, and sanitized metadata.

Use **Notification Logs** to filter by event type, channel, and status.

## Templates

Use **Templates** to create reusable school-scoped text. Template channels can be:

- database;
- log;
- email-ready;
- SMS-ready;
- WhatsApp-ready.

Templates do not activate provider APIs or store provider credentials. Template bodies are displayed with Blade escaping.

## Bulk Communication

The existing **Bulk Communication** page remains available for school-scoped batch messages. Email remains the active delivery path. SMS-ready and in-app-ready rows are skipped or logged as future channels unless a safe internal dispatcher is added later.

## Operational Hooks

Operational log entries are created for:

- live class scheduled;
- live class updated;
- live class cancelled;
- LMS material published;
- finance invoice generated;
- finance payment recorded;
- admission status changed.

These logs are for school operations, not public marketing campaigns.

## Privacy Rules

Communication logs must not contain:

- live class meeting passwords;
- provider secrets;
- OAuth tokens;
- raw provider payloads;
- CBT answers;
- admission documents;
- private finance notes;
- raw passwords or reset tokens.

Recipient details are minimized. Logs prefer role, class, student, or related-record identifiers and safe labels.

## Role Boundaries

- School Admins can view the Communication Center, notification logs, and templates.
- Teachers do not manage templates by default.
- Accountants and Result Officers do not manage communications by default.
- Students and parents do not receive portal communication in this stage.
- Super Admin support visibility only applies through an active school context.

## Deferred Provider Work

WhatsApp Business API, SMS provider API, provider credential storage, webhooks, push notifications, AI-generated message sending, cross-school broadcasts, and public marketing campaigns are not implemented here.
