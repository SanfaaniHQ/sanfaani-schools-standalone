# Standalone Roadmap

Completed in the local-first foundation:

- Standalone product config.
- Safe environment defaults for single-school deployment.
- Admin standalone status page.
- Read-only `standalone:status` command.
- Safe sync outbox schema.
- `standalone:sync --dry-run`.
- Refusal of real sync when disabled or missing endpoint/token.
- Online attendance foundation for class registers and summaries.
- Disabled-by-default attendance-only browser offline capture and validated sync pilot.
- Documentation that the local database is the source of truth.

Next phases:

- Choose the first entity set for selected-data sync.
- Add explicit model capture only for approved entities.
- Design payload versioning and idempotency.
- Add a cloud transport abstraction with tests and timeouts.
- Add conflict detection before any pull/two-way sync.
- Add backup sync rules separate from operational sync.
- Add a Stage 9 administrator monitor for attendance sync receipts and server-visible attempts.
- Add browser offline/PWA support as a later phase.

Non-goals for this foundation:

- Full browser offline/PWA is not complete yet.
- Offline capture for results, admissions, LMS, fees, CBT, and other portal modules is not implemented.
- Real two-way sync is not complete yet.
- SaaS billing/signup are not the main standalone flow.
- Marketplace package builder is not the main standalone user flow.
- Local school data must never be deleted by sync foundation code.
