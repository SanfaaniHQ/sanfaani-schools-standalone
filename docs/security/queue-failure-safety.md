# Queue Failure Safety

Queue failures can capture job payloads and exception messages.

## Requirements

- Configure a failed queue driver for production.
- Redact secrets before logging job failures.
- Keep failed jobs access admin-only.
- Do not email raw exception traces to buyers, schools, or demo users.
- On shared hosting, use database queues or sync fallback carefully and avoid long-running workers when unsupported.
- Review failed mail jobs for SMTP usernames, passwords, license keys, or raw tokens before support handover.
