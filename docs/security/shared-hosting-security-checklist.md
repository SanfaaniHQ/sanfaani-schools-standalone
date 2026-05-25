# Shared-Hosting Security Checklist

This checklist targets Namecheap, cPanel, and similar shared-hosting deployments.

## Checklist

- Point the domain document root to Laravel `public`.
- If document root cannot be changed, keep `.env`, `storage`, `vendor`, and application source outside web-accessible folders.
- Disable directory listing.
- Protect `storage/app/private`, `storage/logs`, `storage/framework`, backups, and uploaded package metadata.
- Use HTTPS and secure cookies.
- Keep SMTP credentials in `.env`.
- Do not rely on shell-only maintenance workflows.
- Do not expose `public/build.zip`.
- Run backups and updates manually with the guided admin checklists.
- Keep diagnostics read-only.
