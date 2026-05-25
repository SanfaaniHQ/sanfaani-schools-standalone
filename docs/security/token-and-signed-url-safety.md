# Token And Signed URL Safety

Public token flows should be short-lived, signed, encrypted, or both.

## Guidance

- Use `SANFAANI_TOKEN_DEFAULT_EXPIRY_MINUTES=60` as baseline expiry guidance.
- Use Laravel signed URLs for public verification and marketing tracking routes.
- Use encrypted unsubscribe tokens that do not reveal contact existence.
- Do not include raw access tokens or license keys in email templates.
- Prefer signed download routes for private files.
- Do not expose `storage/app/private` or backup paths publicly.
