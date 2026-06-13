# Buyer-Facing Feature List

Use this feature list for proposals, demo walkthroughs, and buyer explanations. It is intentionally written for buyers, not developers.

## Product Summary

Sanfaani Schools Standalone gives a private school its own Laravel-based management portal for school setup, records, admissions, attendance, fees, learning content, CBT links, communications, reports, branding, installer/license readiness, backup/update review, and support handoff.

## School Administration

- Private school workspace and dashboards.
- School profile and school-facing portal settings.
- Staff/user roles and permissions.
- Academic sessions, terms, classes, and subjects.
- Student records and class assignment history.
- Guided onboarding and support documentation.

## Admissions

- Laravel-owned admissions engine.
- Public admission form and tracking foundation.
- Admin review workflow for applications, documents, notes, interviews, payments, and status changes.
- Future website integration can connect to Laravel, but the public Next.js school website add-on is not completed in this stage.

## Attendance

- Online attendance marking.
- Attendance reports and student history.
- Attendance-focused browser offline capture and sync where enabled.
- Offline sync admin monitor for records that have reached Laravel.
- Pending browser records are not visible to admins until synced.

## Finance And Fees

- Fee items and fee assignments.
- Student/class invoice generation.
- Manual payment recording.
- Finance reports and student balances.
- Finance audit review.
- Selected finance exports.
- This is not a full ERP and does not confirm school-fee payment gateway automation.

## Learning, CBT, And Live Classes

- LMS classrooms and materials.
- Private resource uploads.
- Safe links to existing CBT exams, quizzes, or assessments.
- Manual live-class scheduling with meeting links.
- Provider abstraction metadata for future provider work.
- No real Google Meet, Zoom, or Microsoft Teams API automation is included.

## Communications

- Communication Center for school admins.
- Notification templates.
- Operational notification logs and hooks.
- Existing bulk communication boundary.
- External SMS and WhatsApp provider automation is not completed unless separately implemented and verified.

## Branding And Reports

- School display name, colors, logo, favicon, login wording, email footer, and report footer.
- White-label branding where license terms and entitlement allow.
- Reports Center for operational summaries and links to module reports.
- Not a public website builder, full theme builder, or custom BI builder.
- Optional future Next.js website add-on can be sold as a separate public front door, but it is not built in this Laravel repo.

## Installer, License, Backup, And Updates

- Standalone installer readiness.
- Local license activation/diagnostics with masked key output.
- Backup metadata, verification, retention status, and manual restore planning.
- Guided update package upload, manifest validation, and preflight review.
- No online update server, auto-download, destructive auto-apply, SaaS billing, payment gateway enforcement, or remote license server.

## Support Readiness

- Support runbooks by module.
- Issue triage priorities.
- Release handoff checklist.
- Buyer and implementation handoff guidance.

## Related Docs

- [Product Packaging Review](product-packaging-review.md)
- [Module Boundaries And Limitations](module-boundaries-and-limitations.md)
- [Support And Maintenance Positioning](support-and-maintenance-positioning.md)
