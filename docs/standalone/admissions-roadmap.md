# Admissions Roadmap

## Current foundation

- Portal-hosted admission pages for schools without a website
- Link and iframe integration for existing websites
- Disabled-by-default API foundation for existing or Next.js websites
- Admission cycles, applications, guardians, private documents, workflow logs, notes, interviews, and manual payments
- Secure tracking and applicant-to-student conversion
- Email notification foundation through the existing Laravel mailer

## Next phases

- CSV application import and export
- Rich document requirements by cycle and class
- Interview calendars and scoring rubrics
- Configurable applicant email templates
- Optional captcha provider integration
- Staff permissions dedicated to admission roles
- Reporting and conversion analytics
- Optional payment-provider integration after reconciliation and webhook testing
- Admission Bridge for queued external submissions to intermittently connected schools
- Optional Next.js school website package using the secured Laravel API

The website will remain the public front door and the portal will remain the source of truth. Future work must not expose applicant lists, move private records into frontend storage, or claim offline sync before the Admission Bridge is implemented and tested.
