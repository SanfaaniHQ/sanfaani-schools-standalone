# Admissions Security And Privacy

The Laravel portal owns admission records and keeps applicant data private.

## Implemented controls

- Public submission and tracking routes are rate limited.
- Applicant lists and administration actions require authenticated school-admin context.
- Every nested application, document, and payment action is checked against the active school.
- Tracking tokens are random and stored only as SHA-256 hashes.
- API keys are shown once and stored only as SHA-256 hashes.
- API access is disabled by default and supports domain allowlists.
- Documents are limited by type and size and stored on the private `local` disk.
- Status changes are recorded in immutable application history rows.
- The form requires explicit privacy consent.
- Public tracking omits guardian data, documents, internal notes, and other applicants.

## Operating requirements

Use HTTPS for any internet-facing installation. Protect backups because they contain applicant data. Give admission access only to authorized staff. Review document retention and privacy notices under applicable law. Rotate or revoke integration keys when website maintainers change.

Captcha is configurable but no third-party captcha provider is bundled in this stage. Online payment providers, production SMS, and WhatsApp delivery are also not implemented. Email notifications use the existing Laravel mail configuration and can safely fall back to a log mailer.
