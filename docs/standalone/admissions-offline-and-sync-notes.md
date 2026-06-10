# Admissions Offline And Sync Notes

The portal database is the source of truth. This stage does not implement cloud sync, a full offline browser/PWA, or a live external submission queue.

## Online portal

When the Laravel portal is hosted on reachable cPanel or VPS infrastructure, an existing website or future Next.js website can link, embed, or submit directly to it.

## LAN-only portal

When the portal runs only on the school LAN, a public website cannot submit to that local server while the server or internet route is unreachable. Browser code cannot bypass that network boundary.

## Practical fallback

For phase 1 and phase 2, use CSV export/import as the operational fallback for applications collected elsewhere. Files should be transferred through an approved secure process and imported by authorized staff.

## Future Admission Bridge

A future Admission Bridge may accept external submissions in a reachable service, queue encrypted payloads, detect duplicates, and sync to the school portal when connectivity returns. That bridge requires conflict handling, delivery receipts, key rotation, retention rules, and security testing. It is roadmap work, not part of the current implementation.
