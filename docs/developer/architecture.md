# Developer Architecture

## Laravel Structure

Sanfaani Schools is a Laravel application using Blade, Tailwind CSS, Vite, Laravel auth, and role-based permissions. Public routes live outside auth middleware. School and admin routes are grouped by role middleware.

## Multi-School Data Scoping

Most operational records include `school_id`. Controllers must scope reads and writes to the authenticated user's school unless the user is a Super Admin. Avoid global queries for school-owned records.

## Identity Architecture

- `students.admission_number` identifies students inside a school.
- `users.staff_code` identifies teachers, result officers, and optionally school admins.
- `schools.school_code` identifies schools for support, billing, and integrations.

Staff login may accept email or staff code. Student portal login is not implemented for the production launch.

## Result Architecture

Student results link to school, class, student, subject, session, term, and result type. Publishing fields control public availability. Result publications and verifications support publication tracking and future verification.

## Student Enrollment and Promotion Architecture

`students.school_class_id` is a quick current-placement pointer for dashboards and lists. Historical placement belongs in `student_class_enrollments`, keyed by student and academic session. Promotion work creates `student_promotion_batches` and `student_promotion_items` so a school can audit who was promoted, repeated, graduated, transferred, withdrawn, or skipped.

Promotion must never update old `student_results`. Result entry, upload, publishing, and public checking should continue to use their selected class/session/term context instead of assuming the student's current class is the historical result class.

## Report Card Architecture

Report-card display settings are intentionally separate from academic data. `report_card_templates` defines available template foundations, `school_report_card_settings` stores each school's display preferences, and `report_card_comment_rules` stores optional average-based comment rules. `ReportCardService` should prepare display data while leaving `student_results` as score records.

## Scratch Card Architecture

Scratch card batches represent school requests and generated card groups. Scratch cards hold serial/PIN data and status. Scratch card usages preserve result access history. Cards should be revoked rather than deleted.

## Plan and Access Architecture

Plans, plan features, school subscriptions, feature overrides, result access policies, and access policy rules provide the foundation for flexible pricing and access models.

## Payment Architecture

Manual payment is active. `PaymentTransaction` supports `payment_gateway`, `gateway_reference`, `payment_reference`, status, proof path, and metadata. Paystack and Flutterwave are configured through `config/payments.php` and `.env`.

## Public Result Checker Architecture

Public result checking validates school, admission number, result context, and scratch card access before rendering a result view. Error messages should remain parent-friendly and avoid exposing internal details.
