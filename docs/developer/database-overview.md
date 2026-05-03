# Database Overview

## Core Tables

- `users`: authenticated users, school assignment, staff code, and password state.
- `schools`: school profile, slug, school code, language fields, and archive state.
- `roles`, `permissions`, and related pivot tables: role-based access control.

## Academic Setup

- `school_classes`: classes owned by a school.
- `subjects`: subjects owned by a school.
- `academic_sessions`: academic years/sessions.
- `terms`: terms linked to sessions.
- `grading_scales`: school-specific grade ranges and remarks.

## Students and Results

- `students`: student identity, admission number, class, contact/profile data, and soft delete state.
- `student_class_enrollments`: class placement history per student and academic session.
- `student_promotion_batches`: promotion operations created by a school user.
- `student_promotion_items`: per-student promotion actions and outcomes.
- `admission_number_settings`: school-specific admission number pattern, next number, and reset cycle.
- `student_results`: scores, grading, teacher remarks, publication status, result type, session, and term.
- `result_publications`: publication batches and tracking.
- `result_verifications`: verification codes and links for public result authenticity.

## Report Cards

- `report_card_templates`: reusable report-card template definitions and preview metadata.
- `school_report_card_settings`: school-specific report-card display, branding, signatures, and comment preferences.
- `report_card_comment_rules`: optional average-based comments for class teacher or head teacher remarks.

## Plans and Access

- `subscription_plans`: commercial plan definitions.
- `plan_features`: feature definitions per plan.
- `school_subscriptions`: school plan assignments.
- `school_feature_overrides`: school-specific feature overrides.
- `school_result_access_policies`: result access models for schools.
- `school_result_access_policy_rules`: detailed rules for access policies.

## Payments and Scratch Cards

- `payment_transactions`: manual and future gateway payment tracking.
- `scratch_card_batches`: card requests and generated batches.
- `scratch_cards`: serial/PIN access records and revoke status.
- `scratch_card_usages`: public result access usage history.

## Leads and Audit

- `lead_requests`: landing page contact and demo requests.
- `audit_logs`: important administrative and security events.

## Safety Notes

Use soft delete or archive where available. Preserve payment transactions, audit logs, card usage, and verification records.
