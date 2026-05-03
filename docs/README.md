# Sanfaani Schools Documentation

This folder collects the launch, product, admin, developer, payment, design, deployment, and testing notes for Sanfaani Schools.

Sanfaani Schools is prepared for a 1 to 5 school pilot on `https://schools.sanfaani.net`. The current focus is school setup, student records, result entry/upload, grading, publishing, scratch-card access, and public result checking.

## Sections

- `product/` explains the product, modules, business model, and roadmap.
- `users/` explains roles and permission boundaries.
- `admin/` contains practical guides for Super Admins, School Admins, and Result Officers.
- `developer/` explains architecture, important tables, and future upgrade logic.
- `design/` describes the UI and UX standards.
- `payments/` explains manual payment today and Paystack/Flutterwave readiness.
- `deployment/` contains Namecheap pilot launch instructions.
- `testing/` contains pilot verification checklists.

## Identity Rules

- Students and parents use `admission_number` for result checking.
- Teachers and Result Officers use `staff_code` or email with password.
- School Admins use email or staff code with password.
- Schools use `school_code` and `slug`.

Admission numbers belong to students. Staff identities must stay separate so the platform remains clean and scalable.
