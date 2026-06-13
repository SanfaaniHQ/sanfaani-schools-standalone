# Security And Privacy Boundaries

The future Next.js website must be public-safe. It should not receive or expose private Laravel data.

## Source Of Truth Rule

Laravel remains responsible for:

- Authentication and sessions.
- Admissions records and tracking.
- Student, staff, parent, and guardian data.
- Attendance.
- Fees/accounting.
- LMS and private resources.
- CBT exam data and attempts.
- Communications and notification logs.
- Reports.
- Audit logs.
- Installer, license, update, backup, and system health.

## Never Expose To The Website

- `.env` values.
- Laravel app keys.
- Database credentials.
- SMTP passwords.
- License keys.
- Payment secrets.
- Provider tokens.
- API keys.
- Backup paths or files.
- Private storage paths.
- Audit logs.
- Notification logs.
- Student/staff/parent private data.
- Applicant documents.
- CBT questions, answers, attempt payloads, or result internals.
- Finance records or payment references.

## Link-Only V1 Safety

Link-only V1 is safest because:

- No API key is needed.
- Laravel owns validation and rate limits.
- Laravel owns admission tracking and privacy consent.
- Laravel owns authentication.
- The website can be hosted independently.

## Embed Safety For Later

Embedded admissions must respect:

- Allowed domains.
- `frame-ancestors` policy.
- Submission throttles.
- Honeypot/timing checks.
- Private document storage.
- Privacy consent.
- No public applicant list.

## API Safety For Later

API-based admissions must require:

- `SANFAANI_ADMISSION_API_ENABLED=true`.
- Active hashed API key.
- Server-side key storage in the future Next.js repo.
- Allowed domain policy.
- Rate limits.
- Laravel validation.
- No exposure of application lists, internal notes, documents, staff actions, or private routes.

## Commercial Safety

Do not sell the website add-on as SaaS billing, payment gateway automation, remote license server, online update server, full offline system, real live-class provider automation, or dependency-vulnerability remediation.

## Related Docs

- [Admissions Linking Guide](admissions-linking-guide.md)
- [Portal Login Linking Guide](portal-login-linking-guide.md)
- [Admissions Security And Privacy](../standalone/admissions-security-and-privacy.md)
- [Security And Privacy Runbook](../support/security-privacy-runbook.md)
