# Branding And Content Guidance

The future Next.js website can carry public school branding, but it must not replace Laravel's school operations source of truth.

## Public Content The Website Can Own

- School name.
- Public logo, colors, and photography approved for marketing use.
- Home page copy.
- About page copy.
- Academics/programmes page copy.
- Admissions marketing copy that links to Laravel.
- Contact email, phone, address, and map links.
- Public privacy and terms text.
- Optional news or announcements content that contains no private student/staff data.

## Content The Website Must Not Own

- Student records.
- Staff private records.
- Parent private records.
- Admissions applications as source of truth.
- Payment records.
- Attendance records.
- LMS private materials.
- CBT private exam data.
- Reports.
- Notification logs.
- Audit logs.
- Backup, update, license, installer, or system-health data.

## Branding Relationship

Laravel already supports school branding inside the portal. The future website can use matching public branding assets and colors, but these should be copied or configured intentionally in the future website repo. Do not read private Laravel storage paths or raw branding internals from the public website.

## Content Approval

Before launch, approve:

- School name spelling.
- Logo and favicon.
- Primary and accent colors.
- Admissions call to action.
- Contact details.
- Privacy/terms wording.
- Portal login URL.
- Admissions URL.
- Application tracking URL.

## Related Docs

- [Website Deployment Positioning](website-deployment-positioning.md)
- [Security And Privacy Boundaries](security-and-privacy-boundaries.md)
- [School Branding Operations](../school-operations/branding.md)
- [Branding Runbook](../support/branding-runbook.md)
