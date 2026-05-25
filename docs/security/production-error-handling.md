# Production Error Handling

Production errors should be useful to administrators without revealing internals to users.

## Requirements

- `APP_DEBUG=false` on all public deployments.
- Use generic user-facing error pages.
- Keep stack traces in protected server logs only.
- Do not render database queries, server paths, `.env` keys, mail configuration, or license keys.
- For cPanel and Namecheap, inspect Laravel logs through protected file manager or SSH where available.
- Diagnostics must stay read-only and must not clear caches or rewrite configuration.
