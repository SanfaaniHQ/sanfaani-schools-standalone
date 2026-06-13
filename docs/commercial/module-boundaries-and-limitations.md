# Module Boundaries And Limitations

This page keeps buyer-facing claims honest. Use it in proposals, demo notes, handoff packs, and release reviews.

## Admissions

Available:

- Laravel-owned admissions engine.
- Public admission form, tracking, admin review, status flow, document/payment/interview notes, and safe notification hooks.

Boundary:

- Public website integration is future work. A Next.js school website add-on is not completed in this stage.

## Attendance

Available:

- Online attendance marking.
- Attendance reports and student history.
- Attendance-focused browser offline capture and sync where enabled.
- Offline sync monitor for server-known receipts and attempts.

Boundary:

- Offline support is not full portal offline mode. Pending browser records are not visible to Laravel until synced.

## Finance

Available:

- Fee items, assignments, invoices, manual payments, reports, audit review, and selected exports.

Boundary:

- Finance is not a full ERP. Payment gateway automation is not confirmed as completed for school fees.

## LMS And CBT

Available:

- LMS classrooms, topics, materials, private resources, and safe links to existing CBT items.
- CBT remains the assessment engine.

Boundary:

- Advanced submissions, discussions, analytics, video hosting, and full parent/student learning portals are future work.

## Live Classes

Available:

- Manual meeting links.
- Live-class scheduling and status workflow.
- Provider abstraction metadata for future provider work.

Boundary:

- Real Google Meet, Zoom, and Microsoft Teams API automation is future work.

## Communication

Available:

- Communication Center, notification templates, operational logs, and module hooks.

Boundary:

- External SMS and WhatsApp provider automation is not completed unless separately implemented and verified.

## Branding

Available:

- School branding inside the Laravel portal: display name, logo, favicon, colors, login wording, dashboard heading, email footer, and report footer.
- White-label branding where license terms and entitlement allow.

Boundary:

- Next.js public website, DNS provisioning, SSL automation, full theme builder, and page builder are future work.

## Reports

Available:

- Reports Center and linked module reports.

Boundary:

- Reports are not a custom BI builder, report scheduler, public reporting portal, or cross-school analytics product.

## Installer And License

Available:

- Local installer readiness.
- Local license activation/diagnostics, masked key display, entitlement visibility, and support-safe status.

Boundary:

- No SaaS billing, payment gateway enforcement, customer billing portal, online activation server, or remote license server.

## Updates

Available:

- Guided local update package review, manifest validation, history, and preflight checks.

Boundary:

- No online update server, auto-download, destructive auto-apply, browser-triggered migrations, or automatic recovery execution.

## Backup And Restore

Available:

- Backup metadata, verification, retention state, pre-update readiness, and manual restore-plan guidance.

Boundary:

- Restore should be handled carefully with support and verified backups. Automated production restore execution is not included.

## Dependency Vulnerabilities

Dependency vulnerabilities are a separate later dependency/security audit task. Do not claim they are fixed by commercial packaging docs.

## Related Docs

- [Buyer-Facing Feature List](buyer-facing-feature-list.md)
- [Product Support Overview](../standalone/product-support-overview.md)
- [Remaining Work Register](../roadmap/remaining-work-register.md)
