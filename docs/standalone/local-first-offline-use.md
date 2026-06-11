# Local-First Offline Use

The first offline goal is local-first deployment. The school runs Sanfaani Schools on infrastructure it controls, such as a local server on the school LAN, a dedicated computer, a VPS, or a cPanel account. If the internet connection drops but the local server and database are still reachable, staff can continue using the app on the local network.

The local database is the source of truth. School records, student data, classes, subjects, result entries, CBT records, and operational workflows should be treated as local data first.

What is supported in this stage:

- Local server/database operation without requiring a live SaaS connection.
- Installer-enabled single-school setup.
- License activation foundation.
- Optional sync configuration that is disabled by default.
- A sync outbox foundation for future selected-data push.
- An optional attendance-only browser offline capture pilot using IndexedDB and authenticated Laravel validation.

What is not complete yet:

- Full browser offline/PWA is not complete yet; only selected class attendance capture is supported when enabled.
- Real two-way sync is not complete yet.
- Automatic capture of every model change is not enabled yet.
- Conflict resolution between local and cloud records is not implemented yet.

When internet returns, browser attendance records can be posted to the local or hosted Laravel server. Browser storage is temporary, can be lost if cleared, and is not visible to the server before sync. The Laravel database remains authoritative.
