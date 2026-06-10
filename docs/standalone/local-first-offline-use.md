# Local-First Offline Use

The first offline goal is local-first deployment. The school runs Sanfaani Schools on infrastructure it controls, such as a local server on the school LAN, a dedicated computer, a VPS, or a cPanel account. If the internet connection drops but the local server and database are still reachable, staff can continue using the app on the local network.

The local database is the source of truth. School records, student data, classes, subjects, result entries, CBT records, and operational workflows should be treated as local data first.

What is supported in this stage:

- Local server/database operation without requiring a live SaaS connection.
- Installer-enabled single-school setup.
- License activation foundation.
- Optional sync configuration that is disabled by default.
- A sync outbox foundation for future selected-data push.

What is not complete yet:

- Full browser offline/PWA support is not complete yet.
- Real two-way sync is not complete yet.
- Automatic capture of every model change is not enabled yet.
- Conflict resolution between local and cloud records is not implemented yet.

When internet returns, selected data can later be pushed to Sanfaani cloud through the sync foundation. Until that stage is explicitly implemented and tested, the local database remains authoritative.
