# Offline Sync Runbook

## Purpose

Use this runbook when supporting attendance offline capture, browser sync status, offline sync receipts, conflicts, validation failures, or questions about what offline support means.

## Access

School Admins can monitor offline attendance sync for their school. Assigned Teachers can work only with their assigned class attendance scope. Super Admin support must operate inside the intended school context.

## Normal Workflow

1. Confirm offline capture and sync feature flags are enabled for attendance.
2. Confirm the user is authenticated, in the intended school, and has attendance management access.
3. Ask whether the browser still shows pending, synced, failed, or conflict state.
4. Open `/school/attendance/offline-sync-monitor` for server-known receipts.
5. Compare the monitor with the attendance report for the class/date.
6. Resolve role, class assignment, date, status, or browser connectivity issues before asking the user to retry.

## Common Issues

- Pending browser records are not visible to admin because they have not synced yet.
- Browser storage was cleared before sync.
- Teacher lacks active class assignment.
- The record belongs to a different school, class, student, date, session, or term.
- Duplicate UUID or changed payload creates conflict.
- Status, date, or academic context fails server validation.

## First Checks

- Confirm the class, date, academic session, term, user role, and browser state.
- Check the offline sync monitor for receipt status, latest attempt time, conflicts, validation failures, and permission failures.
- Check the attendance report for the class/date.
- Confirm the browser is online and the user is still logged in before retrying sync.
- Confirm the user has not cleared IndexedDB or changed browser profile.

## Safe Commands And UI Checks

```bash
php artisan test --filter=Standalone
php artisan route:list
php artisan standalone:status
```

Use the attendance report and `/school/attendance/offline-sync-monitor` as the primary support views.

## What Support Should Not Do

- Do not claim full portal offline support.
- Do not clear browser storage while pending attendance records may still exist.
- Do not tell admins that browser-local pending records are visible to Laravel before sync.
- Do not manually mark browser-local records as synced.
- Do not bypass teacher class-assignment rules.

## Escalation Points

Escalate when repeated conflicts cannot be explained, browser records are lost before sync, permission failures look incorrect, server validation rejects valid attendance, or cross-school records appear in a monitor.

## Data And Privacy Warnings

Browser IndexedDB may contain student IDs and notes. Avoid sensitive health or family details in attendance notes. The monitor must not expose raw browser payloads, sync tokens, stack traces, or student biodata beyond allowed class/date context.

## Backup And Security Reminders

Attendance records that reached Laravel are part of the database backup. Browser-local pending records are not backed up by Laravel before sync.

## Related Docs

- [Attendance Foundation](../standalone/attendance-foundation.md)
- [Offline Attendance Capture Pilot](../standalone/offline-attendance-capture.md)
- [Offline Sync Admin Monitor](../standalone/offline-sync-monitor.md)
- [Local-First Offline Use](../standalone/local-first-offline-use.md)
- [Standalone Sync Architecture](../standalone/sync-architecture.md)
