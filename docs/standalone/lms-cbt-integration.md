# LMS CBT Integration

Stage 15 connects the Stage 14 LMS foundation to the existing CBT foundation without rebuilding CBT.

## Product Boundary

LMS is the learning hub. CBT remains the assessment engine.

School Admins and assigned Teachers can link existing CBT exams, quizzes, or assessments to:

- an LMS classroom; or
- an LMS material.

The link stores only safe IDs and display metadata. It does not copy CBT questions, options, answers, scores, access codes, invitation tokens, attempt payloads, or result payloads into LMS.

## Link Model

The `lms_cbt_activities` table is school-scoped and points to the existing `cbt_exams` table. Each active target prevents duplicate links for the same LMS classroom/material and CBT item.

The table stores:

- school, LMS classroom, optional LMS material, and CBT exam IDs;
- class, subject, optional session, and optional term scope;
- target type and target ID for duplicate protection;
- optional LMS-facing title and description;
- active or archived status;
- created/updated actor IDs;
- safe metadata only.

## Permission Rules

School Admins can link school-scoped CBT items to school-scoped LMS classrooms or materials.

Teachers can link or unlink CBT items only when:

- their current school role has LMS material access;
- their current school role has CBT question-bank or CBT management access;
- the LMS classroom/material belongs to an assigned class and subject;
- the CBT item belongs to the same school, class, and subject.

Result Officers and Accountants do not receive LMS-CBT management by default. Super Admin support visibility still runs inside the current school context and must not leak cross-school records.

## Student Boundary

Student LMS viewing is still deferred until safe user-to-student identity resolution exists. LMS links point to the existing CBT entry/manage surfaces and do not bypass CBT availability, candidate, access-code, attempt, or result-release rules.

## Audit

The integration writes:

- `lms_cbt_activity_linked`;
- `lms_cbt_activity_unlinked`;
- `lms_cbt_activity_link_failed` for scope mismatch failures handled by the service.

Audit metadata includes safe school, classroom, material, CBT exam, class, subject, session, term, actor, and reason IDs. It does not include question content, answers, scores, codes, tokens, or private attempt payloads.

## Deferred

This stage does not rebuild CBT, replace CBT routes/controllers/results/attempt logic, or create duplicate question-bank/exam systems.

Live class foundation remains available from Stage 16, and live-class provider abstraction metadata is available from Stage 17. Provider API automation, offline LMS, assignment submissions/grading, forums, video hosting/transcoding, advanced LMS analytics, parent LMS portal, and payment-gated content are not implemented.
