# Standalone Sync Architecture

Standalone sync is optional and disabled by default. The first foundation uses an outbox pattern so future sync work can be explicit, auditable, and non-destructive.

Tables:

- `standalone_sync_devices`: local or remote device records that may participate in sync later.
- `standalone_sync_outbox`: pending local records selected for future push.
- `standalone_sync_logs`: dry-run, refused, skipped, push, pull, or backup sync attempts.

Current behavior:

- `php artisan standalone:sync --dry-run` lists pending outbox records and never contacts an external service.
- `php artisan standalone:sync` refuses real sync when sync is disabled.
- `php artisan standalone:sync` refuses real sync when endpoint or token is missing.
- The attendance-only browser offline pilot uses its authenticated school endpoint and the existing attendance service; it does not use the cloud transport.
- Accepted browser attendance UUIDs are stored in `attendance_offline_sync_receipts` for durable, school-scoped idempotency.
- Browser sync attempts add safe summary rows to `standalone_sync_logs`.
- No remote API call is required or performed in this stage.
- No local school data is deleted by the sync foundation.

Future behavior should stay bounded:

- Only selected entities should be captured and pushed.
- Payloads should be versioned.
- Local records should not be deleted because a cloud record is missing.
- Conflict resolution must be designed before pull or two-way sync is enabled.
- Backups should be separate from operational sync and clearly labeled.

The Laravel database is the source of truth. Browser-local attendance records are temporary and invisible to the server until the authenticated attendance sync endpoint receives them. Full portal offline mode and two-way cloud sync remain outside this pilot.
