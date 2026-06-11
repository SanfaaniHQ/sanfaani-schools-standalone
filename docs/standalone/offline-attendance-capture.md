# Offline Attendance Capture Pilot

The browser offline attendance capture pilot lets an authorized School Admin or assigned class teacher capture a selected class register while the browser is offline. It is attendance-only and disabled by default.

Enable the pilot with:

```env
SANFAANI_PWA_OFFLINE_CAPTURE_ENABLED=true
SANFAANI_PWA_OFFLINE_SYNC_ENABLED=true
SANFAANI_PWA_OFFLINE_ALLOWED_MODULES=attendance
```

Both capture and sync must be enabled, and `attendance` must remain in the allowed module list. The normal online attendance form is unchanged when the pilot is disabled.

## What Works Offline

- The existing class attendance form can store present, absent, late, or excused selections.
- Pending records are stored in browser IndexedDB with a client UUID.
- The class register shows pending, recently synced, and failed states.
- Pending records retry when the browser reports that internet access has returned.
- An authorized user can also trigger sync manually.

The browser stores IDs, attendance date, status, optional note, academic context IDs, capture time, and sync state. It does not cache dashboards, student names, admissions, results, fees, CBT, LMS, or the full portal.

## Browser Storage Warning

IndexedDB is temporary browser storage. Pending attendance can be lost if the user clears browser data, uses a private browsing session that is discarded, changes browser profiles, or loses the device before sync.

Do not clear browser storage until pending attendance is confirmed as synced. The Laravel server cannot see or back up browser-local pending records before they sync.

## Sync And Server Validation

The browser posts pending records to the authenticated `POST /school/attendance/offline-sync` endpoint. The endpoint is unavailable unless the feature flags are enabled and the current role has `attendance.manage`.

Every record is validated again by Laravel. The server enforces:

- current authenticated school scope;
- attendance feature access;
- teacher class-assignment rules;
- class ownership by the current school;
- active student membership in the selected class and school;
- supported attendance status;
- valid attendance date and academic context;
- `source=browser_offline`;
- duplicate attendance and client UUID rules.

Browser data is never treated as authoritative. The hosted Laravel database remains the source of truth.

## Idempotency And Conflicts

Each browser record has a `client_uuid`. A durable attendance offline sync receipt records accepted UUIDs per school.

- Repeating the same UUID and payload returns `skipped_duplicate`.
- Reusing a UUID for a different payload returns `conflict` and does not overwrite attendance.
- A new UUID for an existing school, class, student, and date updates the existing attendance row through the normal attendance service and returns an accepted `conflict`.
- The attendance table's existing unique key prevents duplicate rows for the same student, class, school, and date.

Per-record responses use `synced`, `skipped_duplicate`, `failed_validation`, `failed_permission`, or `conflict`, plus an `accepted` flag so the browser knows whether it can mark the item as synced.

## Admin Monitor

Accepted offline attendance uses the existing attendance audit events. Audit metadata includes `source=browser_offline`, client UUID, capture time, class ID, student ID, date, actor, and safe status context.

Stage 9 adds a read-only school monitor at:

```text
/school/attendance/offline-sync-monitor
```

The monitor shows server-known receipts, safe sync attempt counts, conflicts, validation failures, permission failures, class/date/user filters, and high-level sync health. It cannot show browser-local pending records before sync because those records still exist only in the browser.

## Audit And Privacy

The browser database still contains operational student IDs and optional attendance notes. Devices and browser profiles must be access-controlled. Avoid sensitive health or family details in notes.

The admin monitor does not expose raw browser payloads, payload hashes, secrets, stack traces, student biodata, or browser IndexedDB contents.

## Offline Boundary

This pilot does not implement full portal offline mode. Results, admissions, LMS, fees, CBT, live classes, dashboards, and other modules are not available through this browser outbox.

Stage 9 provides server-side monitoring for submitted sync attempts and receipts. It still does not provide a server dashboard for browser-local pending items because the server cannot know they exist until the browser syncs.
