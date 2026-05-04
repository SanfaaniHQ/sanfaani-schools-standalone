# Sanfaani Schools Documentation

This folder collects production launch, product, admin, developer, payment, design, deployment, security, notification, marketplace, and testing notes for Sanfaani Schools.

Sanfaani Schools is being prepared for production launch at `https://schools.sanfaani.net`. The current focus is school setup, student records, result entry/upload, grading, publishing, scratch-card access, public result checking, email readiness, backup discipline, and deployment safety.

## Sections

- `product/` explains the product, modules, business model, and roadmap.
- `users/` explains roles and permission boundaries.
- `admin/` contains practical guides for Super Admins, School Admins, and Result Officers.
- `developer/` explains architecture, important tables, and future upgrade logic.
- `design/` describes the UI and UX standards.
- `payments/` explains manual payment today and Paystack/Flutterwave readiness.
- `notifications/` explains SMTP setup and email notification flow.
- `deployment/` contains Namecheap production launch and backup instructions.
- `security/` contains pre-deployment testing and report templates.
- `marketplace/` contains CodeCanyon readiness and packaging notes.
- `testing/` contains production verification checklists.

For the final Namecheap deployment pass, use `testing/final-deployment-test-checklist.md` together with `deployment/namecheap-production-deployment.md`.

## Identity Rules

- Students and parents use `admission_number` for result checking.
- Teachers and Result Officers use `staff_code` or email with password.
- School Admins use email or staff code with password.
- Schools use `school_code` and `slug`.

Admission numbers belong to students. Staff identities must stay separate so the platform remains clean and scalable.
