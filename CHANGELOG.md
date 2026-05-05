# Changelog

## 2026-05-05

- Completed missing V1.1 support thread routes/controllers/views and dashboard entry cards.
- Added teacher-facing "My Assigned Classes" workflow and route.
- Switched support status/assignment/close actions to PATCH semantics.
- Added `docs/developer/v1-1-completion-audit.md` with completion status, risks, and manual test matrix.

## 2026-05-04

- Finalized deployment audit checks for Namecheap launch on `https://schools.sanfaani.net`.
- Added global production form loading/confirmation coverage, safer result-checker context locking, cache-action confirmations, public copy cleanup, and school-slug notification links.
- Updated final deployment, security, payment, notification, and testing documentation for launch readiness.

## 2026-05-03

- Added production platform settings with logo, favicon, login background, support contacts, URLs, defaults, and storage-backed uploads.
- Added school profile editing for assigned School Admin users, including school logo upload.
- Added public Privacy Policy and Terms pages.
- Added Laravel mail notifications for staff accounts, school creation, student guardian registration, result publishing, and scratch card request status changes.
- Added notification preference foundation for email, SMS, WhatsApp, and in-app channels.
- Updated login, navigation, dashboards, public result checker, and result print views to use production platform and school branding.
- Tightened public result checker access so publication status is not disclosed before scratch card validation.
- Added SMTP setup, notification flow, backup, security checklist, pentest template, Namecheap production deployment, and CodeCanyon readiness documentation.
