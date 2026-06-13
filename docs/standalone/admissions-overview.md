# Standalone Admissions Overview

## Product boundary

The website is the public front door. The Laravel portal is the admission management engine and source of truth for applications, guardians, documents, review history, decisions, payment status, and student conversion.

This design supports three school situations:

1. An existing website links to or embeds the portal form.
2. A school without a website uses `/admissions`, `/admissions/apply`, and `/admissions/track`.
3. A future separate Next.js school website links to Laravel first, with embed or secured API submission as later options. It never becomes the admission database.

## Workflow

The supported status path includes submitted, under review, missing documents, entrance exam scheduled, interview scheduled, accepted, rejected, waitlisted, payment pending, admitted, and converted to student. Every status change creates an audit log.

Documents are restricted to configured file types and sizes and are stored on the private filesystem disk. Public tracking requires the application number plus either the one-time tracking token or the guardian phone number. There is no public applicant list.

Phase 1 supports manual payment records and confirmation. It does not require online payment and does not include a live payment provider.

Accepted or admitted applicants can be converted into the existing Student model. Conversion is transactional and idempotent, preserves the original application, assigns the requested class when available, and uses the existing student admission-number service.

## School benefit

Schools gain a branded application path, organized review queue, document checklist, auditable decisions, and one-step student conversion without forcing a website rebuild. The same engine can serve portal pages today and a custom website later.
