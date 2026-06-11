# Attendance Foundation

The attendance module is the online-first attendance foundation for standalone school operations. It uses the existing schools, classes, students, academic sessions, terms, users, roles, role feature settings, dashboards, reports, and audit logs.

The online workflow remains the default. Stage 8 adds an optional, disabled-by-default browser offline capture pilot for the existing class attendance form. The pilot reuses this service, authorization, duplicate handling, reports, and audit trail.

## Purpose

Attendance supports daily class roll call before any offline or device-based workflow is added. Records are tied to:

- school;
- academic session, when one is active or selected;
- term, when one is active or selected;
- class;
- student;
- attendance date;
- user who recorded the entry.

The supported statuses are:

- `present`
- `absent`
- `late`
- `excused`

## Roles And Permissions

School Admin users can view and manage attendance for their school.

Teachers can view and mark attendance only for classes assigned through the existing active teacher class assignment workflow. Attendance does not use broader subject-only visibility. If a teacher has a subject assignment without an active class assignment, that teacher may still use the normal subject/result tools, but cannot view or manage the class attendance register.

Result Officer, Accountant, Student, Parent, and public users do not receive attendance management access in this stage.

The feature keys are:

- `attendance.view`
- `attendance.manage`

These use the existing school role feature and Gate-based authorization path.

## Daily Class Workflow

1. Open **School > Attendance**.
2. Choose the attendance date.
3. Open a visible class.
4. Select the academic session and term if the active context should be overridden.
5. Mark each active class student as present, absent, late, or excused.
6. Save the class register.

Submitting the same class, student, and date again updates the existing record instead of creating a duplicate.

## Reports And Summaries

The foundation includes web reports for daily and date-range review:

- single date and date-range filters;
- class, student, status, recorder, academic session, and term filters where safe;
- total records;
- present, absent, late, and excused totals;
- attendance percentage;
- daily class summary;
- missing or unmarked students for a single selected class and date;
- detailed record rows with class, student, session, term, recorder, update time, and note.

Attendance percentage is calculated from marked records:

`(present + late + excused) / total marked records * 100`

Absent records reduce the percentage. If there are no marked records, the percentage is `0.0%`.

PDF, Excel, and CSV attendance exports are intentionally deferred to a later reports/import-export stage. Existing audit-log and scratch-card CSV export helpers were inspected, but this attendance stage keeps exports out of scope.

## Class Attendance Report

A class report can be opened from the class register or the report screen. It shows:

- selected class and selected date or date range;
- student list and status records;
- counts by status;
- attendance percentage;
- missing or unmarked students when the report is for one class on one date;
- who recorded the row and when it was last updated.

Missing/unmarked counts are only shown when they are safe to calculate from active students for a single class and day. Date-range reports still show totals and percentages, but do not try to infer expected attendance days.

## Student Attendance History

Student attendance history supports:

- date range;
- class;
- status;
- recorder;
- academic session;
- term.

The history page shows status timeline rows with class, session, term, recorder, and notes. Notes are shown to roles that can already view the student's attendance in the current school scope.

## Audit Logging

Attendance uses the existing audit log service. The module audits:

- attendance recorded;
- attendance updated;
- bulk class attendance submitted.

Audit records include school scope, actor, class ID, student ID, date, source, recorder ID, bulk submission counts, and changed field names where applicable. Update logs include safe before/after values for status, note, session, term, recorder, and source.

Audit metadata intentionally avoids student names, admission numbers, private contact details, or duplicate audit storage.

## School Scoping And Privacy

Attendance records are school-scoped. A user from another school cannot view or write a class attendance register. Reports are constrained to the current school and the current user's allowed attendance classes.

Teachers see attendance only for active class assignments. They cannot view or mark another teacher's class, cannot use subject-only assignment to reach attendance, cannot access cross-school attendance records, and cannot change records outside their allowed class scope.

## Offline Boundary

Implemented in this stage:

- online attendance dashboard;
- online class attendance marking;
- online reports and student history;
- audit logging and authorization;
- optional attendance-only IndexedDB capture;
- authenticated offline attendance sync;
- per-record server validation and idempotency receipts;
- pending, synced, and failed browser states.
- read-only server-side sync monitor for submitted receipts, safe attempt summaries, conflicts, and failures.

Not implemented:

- full portal offline mode;
- offline results, admissions, LMS, fees, CBT, or live classes;
- biometric/device attendance imports;
- parent or student attendance portals.

Browser storage is temporary and can be lost if cleared. The Laravel database remains authoritative, and the server cannot see browser-local pending records before sync. See `docs/standalone/offline-attendance-capture.md`.

School admins and support can review server-known offline attendance sync health at `docs/standalone/offline-sync-monitor.md`. The monitor does not make browser-local pending records visible before sync.
