# Standalone Roadmap

Completed in the local-first foundation:

- Standalone product config.
- Safe environment defaults for single-school deployment.
- Admin standalone status page.
- Read-only `standalone:status` command.
- Safe sync outbox schema.
- `standalone:sync --dry-run`.
- Refusal of real sync when disabled or missing endpoint/token.
- Online attendance foundation for class registers and summaries.
- Disabled-by-default attendance-only browser offline capture and validated sync pilot.
- Administrator monitor for server-known offline attendance sync receipts and attempts.
- Online fees/accounting foundation for fee setup, student invoices, manual payments, balances, permissions, and audit logs.
- School-scoped finance reports and finance audit review for admins/accountants.
- School-scoped CSV import/export tools for selected student, attendance, and finance data.
- Online LMS foundation for class/subject classrooms, topics, teacher materials, private resources, publish workflow, permissions, and audit logs.
- LMS-CBT activity links so existing CBT assessments can be attached to LMS classrooms/materials while CBT remains the assessment engine.
- Live class foundation for manual internet meeting links, class/subject scheduling, LMS context links, recording URLs, status workflow, permissions, and audit logs.
- Live class provider abstraction for manual provider support, provider registry metadata, provider labels, and future provider boundaries without external API calls.
- Communication and notification hardening for school-scoped templates, operational notification logs, safe live-class reminders, LMS/finance/admission hooks, audit logging, and deferred provider channels.
- Branding and white-label consolidation for resolved school display name, logo, colors, portal/module/report branding hooks, safe uploads, audit logging, and powered-by boundaries.
- Reports Pack for a school-scoped Reports Center with safe cross-module summaries, links to existing detailed reports, existing export links, privacy boundaries, and audit logging.
- Documentation that the local database is the source of truth.

Next phases:

- Choose the first entity set for selected-data sync.
- Add explicit model capture only for approved entities.
- Design payload versioning and idempotency.
- Add a cloud transport abstraction with tests and timeouts.
- Add conflict detection before any pull/two-way sync.
- Add backup sync rules separate from operational sync.
- Add deeper debt analytics/export packs after the existing view-only reports.
- Add browser offline/PWA support as a later phase.

Non-goals for this foundation:

- Full browser offline/PWA is not complete yet.
- Offline capture for results, admissions, LMS, fees, CBT, and other portal modules is not implemented. The LMS foundation is online-first only.
- Online payment gateway automation and parent/student finance portal access are not implemented by the school-fees foundation.
- Live-class provider API automation, generated meeting rooms, OAuth/API integration, provider credential storage, webhooks, offline live class, live-class attendance tracking, LMS submissions/grading, parent/student live-class portals, forums, analytics, video hosting, and payment-gated content are not implemented.
- WhatsApp Business API, SMS provider API, provider credential storage, webhooks, push notifications, public marketing campaigns, AI-generated message sending, cross-school broadcasts, and parent/student portal communication are not implemented by the communication hardening foundation.
- Next.js public school websites, DNS/domain provisioning, SSL automation, full theme builders, drag-and-drop page builders, email provider branding automation, advanced PDF redesign, and cross-school theme sharing are not implemented by the branding consolidation foundation.
- BI analytics, custom report builders, public report URLs, report delivery, and cross-school reports are not implemented by the Reports Pack.
- Double-entry accounting, bank reconciliation, payroll, procurement, and tax accounting are not implemented.
- Real two-way sync is not complete yet.
- SaaS billing/signup are not the main standalone flow.
- Marketplace package builder is not the main standalone user flow.
- Local school data must never be deleted by sync foundation code.
