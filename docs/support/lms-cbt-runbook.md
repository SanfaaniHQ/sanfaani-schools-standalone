# LMS And CBT Runbook

## Purpose

Use this runbook when supporting LMS classrooms, topics, materials, private resources, material visibility, LMS-CBT links, and CBT access questions.

## Access

School Admins can manage LMS classrooms and materials in the current school. Teachers can manage assigned class and subject scopes only. CBT remains governed by existing CBT rules. Students and parents do not receive full LMS portal behavior in this stage.

## Normal Workflow

1. Create an LMS classroom tied to an existing class and subject.
2. Add optional topics/modules.
3. Create draft materials and upload approved private resources.
4. Publish materials when ready.
5. Link existing CBT exams, quizzes, or assessments only when the school, class, subject, session, and term scope is safe.
6. Send students to the existing CBT access flow when they need to take an assessment.

## Common Issues

- Teacher lacks assignment for the selected class or subject.
- Duplicate LMS classroom already exists for the same scope.
- Resource file type or size is not allowed.
- Material is still draft or archived.
- CBT item scope conflicts with the LMS classroom/material scope.
- User expects assignment submissions, discussion forums, analytics, video hosting, or parent/student LMS portal features.

## First Checks

- Confirm active school, role, class, subject, session, and term.
- Confirm material status and classroom scope.
- Confirm uploaded file extension, MIME type, and size.
- Confirm CBT item belongs to the same school and compatible class/subject scope.
- Confirm CBT access issue is not caused by exam schedule, candidate rules, access code, attempt limits, or result-release state.

## Safe Commands And UI Checks

```bash
php artisan route:list
php artisan test --filter=Dashboard
php artisan test --filter=Standalone
```

Use LMS classroom/material pages, CBT management pages, and audit logs as primary checks.

## What Support Should Not Do

- Do not copy CBT questions, answers, scores, access codes, tokens, or attempt payloads into LMS support notes.
- Do not expose raw storage paths or file contents.
- Do not bypass teacher assignment or CBT access rules.
- Do not promise offline LMS, submissions/grading, forums, advanced analytics, video hosting, or parent/student LMS portals.

## Escalation Points

Escalate when an assigned teacher is incorrectly blocked, a user can access cross-school LMS/CBT records, private resource paths are exposed, CBT private payloads appear in LMS, or exam access is blocked during a scheduled assessment.

## Data And Privacy Warnings

LMS files may contain school intellectual property and student-facing material. CBT records may contain exam-sensitive data. Keep question content, answers, access codes, and attempt payloads out of support tickets.

## Backup And Security Reminders

LMS private resources and CBT data must be included in backup planning. Confirm storage backup coverage before migration or update work touching LMS files.

## Related Docs

- [LMS Foundation](../standalone/lms-foundation.md)
- [LMS CBT Integration](../standalone/lms-cbt-integration.md)
- [School LMS Operations](../school-operations/lms.md)
- [School CBT Operations](../school-operations/cbt.md)
