# School Admin Manual

School Admin users manage day-to-day school setup within their assigned school.

## Current Responsibilities

- Manage school profile and settings.
- Configure academic sessions, terms, classes, and subjects.
- Manage staff and students where enabled.
- Configure report settings.
- Use the Reports Center for school-scoped summaries and safe links into existing detailed reports.
- Manage school branding: display name, login wording, dashboard heading, colors, logo, favicon, email footer, report footer, and the white-label entitlement boundary.
- View school readiness signals, but leave installer completion, license activation, entitlement diagnostics, backup/update readiness, and global system status to the Super Admin/local owner.
- Review result workflows and publication settings where enabled.
- Review scratch-card tools where enabled.
- Use the Communication Center to review operational notification logs, manage school-scoped templates, and open existing bulk communication tools.
- Use guided onboarding checklist steps.

## Boundaries

School Admins must only see and modify data for their active school. Cross-school data access is blocked by tenant isolation middleware, services, and tests.

Communication logs are operational summaries. They must not contain live class meeting passwords, provider secrets, OAuth tokens, raw provider payloads, CBT answers, admission documents, or private finance notes. SMS and WhatsApp provider delivery remains deferred.

Reports Center summaries are aggregate-only. They must not expose raw CBT answers, admission documents, payment secrets, meeting passwords, notification private payloads, backup paths, update internals, or data from another school.

School Admins do not manage installer or license pages. License keys, app keys, database credentials, mail credentials, raw `.env` values, backup paths, and support diagnostics remain outside the school-admin boundary.

Branding changes are school-scoped. School Admins should upload only safe image assets and must not paste private server paths, secrets, provider credentials, or cross-school assets into branding fields. Powered-by Sanfaani wording may remain unless the deployment is entitled for broader white-label behavior.

## Planned

Full parent/student portal administration is planned and should not be described as complete.
