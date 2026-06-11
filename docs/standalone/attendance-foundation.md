# Attendance Foundation

The attendance module is the online-first attendance foundation for standalone school operations. It uses the existing schools, classes, students, academic sessions, terms, users, roles, role feature settings, dashboards, reports, and audit logs.

This is not the browser offline attendance capture stage. Attendance is recorded through authenticated web requests against the school database. Offline browser capture and sync remain planned for a later stage.

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

Teachers can view attendance for visible assigned classes. Teachers can mark attendance only for classes assigned through the existing active teacher class assignment workflow. Subject-only visibility does not expand attendance management beyond safe class assignment checks.

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

The foundation includes simple web reports:

- daily class status counts;
- present, absent, late, and excused totals;
- class filter;
- date filter;
- student attendance history.

PDF and spreadsheet exports are intentionally deferred unless a later stage adds them through the existing export helpers.

## Audit Logging

Attendance uses the existing audit log service. The module audits:

- attendance recorded;
- attendance updated;
- bulk class attendance submitted.

Audit records include school scope, actor, class, student, date, and changed values where applicable.

## School Scoping And Privacy

Attendance records are school-scoped. A user from another school cannot view or write a class attendance register. Teachers see only assigned class/student attendance according to existing school authorization and teacher assignment rules.

## Offline Boundary

Implemented in this stage:

- online attendance dashboard;
- online class attendance marking;
- online reports and student history;
- audit logging and authorization.

Not implemented in this stage:

- browser offline attendance capture;
- offline queueing or sync for attendance;
- biometric/device attendance imports;
- parent or student attendance portals.

Offline attendance capture remains planned for a later stage after this online foundation is stable.
