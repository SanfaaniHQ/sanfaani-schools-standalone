# Email Branding Guide

Email branding currently supports:

- Resolved brand name.
- Resolved primary color.
- Resolved logo URL when a safe public branding asset exists.
- Escaped email footer text.

Safety rules:

- Do not include raw license keys, database credentials, or `.env` values in email templates.
- Do not include internal server paths.
- Marketing emails must keep unsubscribe links when required by configuration.
- Demo credential emails should avoid raw temporary passwords in email bodies.

Future work:

- Per-school transactional email themes.
- Advanced white-label sender policy validation.
- Branded email template preview workflow.
