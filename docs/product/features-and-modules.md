# Features and Modules

## Auth and Roles

Authentication is Laravel-based. Roles separate Super Admin, School Admin, and Result Officer access. Teachers are prepared as a future staff identity. Staff should use email or `staff_code`, never student admission numbers.

## Super Admin Dashboard

Super Admin users manage schools, plans, subscriptions, feature overrides, result access policies, manual payment confirmations, audit logs, and scratch card approval/generation.

## School Management

Schools have a name, slug, school code, contact details, and language-related fields. `school_code` is for support, billing, school identity, and future integrations.

## School Admin Dashboard

School Admin users manage the operational setup for one school: classes, subjects, sessions, terms, students, grading scales, result workflows, staff users, admission number settings, and scratch card requests.

## Classes, Subjects, Sessions, and Terms

These records define the school structure used by result entry, upload, publishing, and result checking.

## Students

Students have school-specific admission numbers, class links, profile details, soft delete support, bulk upload support, and Student 360 views.

## Student Promotion

School Admin users can promote an entire class or selected students into a new academic session and class. Promotion creates enrollment and promotion history records, so old results remain tied to the original class/session. Students may be promoted, repeated, graduated, transferred, withdrawn, or skipped without deleting academic history.

## Student 360 Profile

The profile brings student identity, class details, class enrollment history, promotion source, and result history together for school staff.

## Result Entry and Upload

Schools can enter results manually or upload CSV/Excel style files by class. The upload flow supports validation and does not require changing the existing template when admission numbers are generated automatically for blank values.

## Grading Scales

Schools can define custom grading ranges and remarks to match conventional, Islamic, madrasah, or training-centre grading patterns.

## Result Publishing

Results can be reviewed, published, and unpublished safely. Public result checking only returns published results that meet access rules.

## Report Card Settings

School Admin users can configure report-card presentation separately from academic scores. Settings include the template foundation, colors, header layout, visible school/student fields, result table style, teacher/head teacher titles and names, optional signatures, and automated comment switches. Super Admin plan features can control availability for report card basics, customization, signatures, auto comments, PDF, QR, and templates.

## Scratch Cards

School Admin users request scratch card batches. Super Admin users confirm payment, approve/generate cards, revoke cards, and download generated card data.

## Public Result Checker

Parents check results using school, admission number, session, term, scratch card serial number, and PIN. Errors are intentionally simple and safe.

## Payments and Plans

Manual payment is active. Payment transactions already support gateway names, gateway references, status, and metadata for future Paystack and Flutterwave integration.

## Multilingual Foundation

The public result checker supports English, French, and Arabic language files, including RTL-ready rendering for Arabic.

## Future Modules

PDF result generation, QR image generation, teacher/class assignment rollover, CBT, assessment/test results, SMS, mobile app, parent portal, student portal, and biometric attendance remain future modules.
