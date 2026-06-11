# Offline Sync Admin Monitor

The offline sync admin monitor is a read-only Stage 9 support view for the Stage 8 attendance-only browser offline capture pilot.

Open it from:

```text
/school/attendance/offline-sync-monitor
```

The page reuses the existing authenticated school attendance route group, `attendance.view` feature authorization, school context, teacher class-assignment rules, and the existing `attendance_offline_sync_receipts` and `standalone_sync_logs` records.

## Purpose

The monitor helps School Admin users and Sanfaani support understand server-known offline attendance sync health after browser records have been submitted to Laravel.

It shows:

- durable offline attendance sync receipt counts;
- synced receipt count;
- skipped duplicate attempt count from safe browser sync logs;
- conflict count from receipts and safe browser sync logs;
- validation failure count from safe browser sync logs;
- permission failure count from safe browser sync logs;
- latest sync attempt time;
- latest successful receipt time;
- latest failure or conflict time;
- recent server-known receipt rows;
- safe counts by submitting user, class, and server processing date.

## What The Monitor Can See

The monitor can see only records that reached the Laravel server:

- accepted browser offline attendance receipts;
- existing-row conflict receipts created by the attendance duplicate rules;
- safe per-batch sync summaries in `standalone_sync_logs`;
- class/date/user context that is linked to a receipt.

The Laravel database remains the source of truth. Receipt rows are used for durable idempotency and safe monitor details.

## What The Monitor Cannot See

The monitor cannot see browser-local pending records before sync. Pending records stored in IndexedDB on a teacher or admin browser are invisible to Laravel until that browser posts them to the authenticated offline sync endpoint.

The monitor also does not show:

- raw browser payloads;
- payload hashes;
- browser IndexedDB contents;
- stack traces;
- server secrets;
- sync tokens;
- student biodata beyond class/date/receipt context;
- full browser storage state;
- records from other schools.

## Status Meanings

- `synced`: Laravel accepted the record and linked the receipt to an attendance record.
- `skipped_duplicate`: the same client UUID and payload was already processed; this is counted from safe sync logs because duplicate attempts do not create new receipt rows.
- `conflict`: Laravel detected an existing attendance row update or a UUID/payload conflict. Review the attendance record and audit log before deciding whether staff need to refresh their register.
- `failed_validation`: Laravel rejected the record because it did not pass server validation. Check status values, dates, academic context, student/class membership, and payload version.
- `failed_permission`: Laravel rejected the record because the authenticated actor could not sync for that school/class scope.
- `processing`: a receipt was created but not finalized. This should be rare and should be reviewed with the application logs and audit trail.

## Visibility Rules

School Admin users can monitor receipts for their current school.

Teachers can monitor receipts for their assigned attendance classes and their own submitted receipts only. A teacher cannot filter or view another unassigned class.

Super Admin support access must still operate through the current school context. Cross-school receipts are not shown on the school monitor.

## Triage Guidance

For conflicts:

1. Open the attendance report for the class/date.
2. Check whether the existing row was intentionally updated by a later offline sync.
3. Review school audit logs for `attendance_recorded`, `attendance_updated`, and `bulk_class_attendance_submitted` events.
4. Ask the staff member to refresh the class register before retrying unresolved browser records.

For validation failures:

1. Confirm the browser form is still on the supported attendance-only class register.
2. Confirm the status is one of `present`, `absent`, `late`, or `excused`.
3. Confirm the student is active and belongs to the selected class in the current school.
4. Confirm the attendance date and academic context are valid.

For permission failures:

1. Confirm the user is authenticated in the intended school.
2. Confirm `attendance.manage` is enabled for the role when sync is attempted.
3. For teachers, confirm an active class assignment exists for the class/session/term.
4. Confirm the record is not from another school.

## Stage 8 And Stage 9 Boundary

Stage 8 implemented browser offline attendance capture for the existing class register and authenticated sync to Laravel.

Stage 9 adds server-side monitoring for sync receipts, safe attempt summaries, conflicts, failures, and audit trail context.

Stage 9 does not implement full portal offline mode, offline results, offline admissions, offline LMS, offline fees, offline CBT, live classes, browser push queues, destructive cleanup, or two-way cloud sync.

## Future Expansion

Future stages may add richer receipt metadata, reviewed/resolved markers, exportable support reports, device-level diagnostics, or approved retry tooling. Those changes should remain read-only by default, school-scoped, and separate from any future full offline platform design.
