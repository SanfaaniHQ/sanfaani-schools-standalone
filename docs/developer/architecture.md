# Developer Architecture

## Laravel Structure

Sanfaani Schools is a Laravel application using Blade, Tailwind CSS, Vite, Laravel auth, and role-based permissions. Public routes live outside auth middleware. School and admin routes are grouped by role middleware.

## Multi-School Data Scoping

Most operational records include `school_id`. Controllers must scope reads and writes to the authenticated user's school unless the user is a Super Admin. Avoid global queries for school-owned records.

## Identity Architecture

- `students.admission_number` identifies students inside a school.
- `users.staff_code` identifies teachers, result officers, and optionally school admins.
- `schools.school_code` identifies schools for support, billing, and integrations.

Staff login may accept email or staff code. Student portal login is not implemented for the pilot.

## Result Architecture

Student results link to school, class, student, subject, session, term, and result type. Publishing fields control public availability. Result publications and verifications support publication tracking and future verification.

## Scratch Card Architecture

Scratch card batches represent school requests and generated card groups. Scratch cards hold serial/PIN data and status. Scratch card usages preserve result access history. Cards should be revoked rather than deleted.

## Plan and Access Architecture

Plans, plan features, school subscriptions, feature overrides, result access policies, and access policy rules provide the foundation for flexible pricing and access models.

## Payment Architecture

Manual payment is active. `PaymentTransaction` supports `payment_gateway`, `gateway_reference`, `payment_reference`, status, proof path, and metadata. Paystack and Flutterwave are configured through `config/payments.php` and `.env`.

## Public Result Checker Architecture

Public result checking validates school, admission number, result context, and scratch card access before rendering a result view. Error messages should remain parent-friendly and avoid exposing internal details.
