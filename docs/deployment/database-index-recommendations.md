# Database Index Recommendations

This document records index review areas for performance diagnostics. It is not a migration plan by itself.

## Rules Before Adding Indexes

- Confirm the table and columns exist in current migrations.
- Confirm the query pattern exists in controllers, services, jobs, or reports.
- Avoid duplicate indexes.
- Keep names short and explicit.
- Run the focused tests and full suite after adding an index.

## Review Areas

| Area | Recommended Review |
| --- | --- |
| Results | `student_results` by `school_id`, `student_id`, `school_class_id`, `academic_session_id`, `term_id`, `result_type`, `status`, publish timestamps |
| CBT | CBT exams, attempts, candidates, question banks, and event logs by school, exam, student/candidate, status, schedule fields |
| Scratch cards | Batches, cards, and usage by school, session, term, status, serial number, student |
| Communication | Logs by school, type, status, sender, recipient, sent/created time |
| Support | Threads by school, status, assignee, last message time |
| Marketing/demo/onboarding | Leads, activities, demo sessions, onboarding progress by status, owner, expiration, school, event |
| Updates | Packages by version, channel, and status |
| Backups | Backup records by school, status, completion time, and expiration |

## Existing Foundation

The codebase already contains targeted hardening indexes for result publication lookups, scratch-card usage, communication logs, CBT tables, update packages, and backup metadata. Treat new index migrations as targeted follow-up work only after a query is verified.

## Diagnostics

`php artisan performance:audit` lists recommended review points. It does not create indexes or run migrations.
