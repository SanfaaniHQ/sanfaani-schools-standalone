# Public Folder Mapping

Laravel should expose only the `public` folder to the web.

## Preferred Mapping

Set the domain document root to:

```text
/path/to/sanfaani-schools/public
```

The rest of the project remains outside public access.

## Shared-Hosting Fallback

If the host cannot point the domain to `public`:

1. Put Laravel public files in `public_html`.
2. Keep the application in a private folder beside `public_html`.
3. Update `public_html/index.php` to reference the private app `vendor/autoload.php` and `bootstrap/app.php`.
4. Keep `.env`, `storage`, logs, backups, update packages, and app source out of `public_html`.

## Never Public

- `.env`
- `storage/logs`
- `storage/framework`
- `storage/app/private`
- `storage/app/backups`
- SQL dumps
- `vendor`
- `node_modules`
- update packages
- backup archives

## Verification

Open `https://domain/.env` and confirm it does not render. Repeat for `/storage/logs`, `/vendor`, and `/node_modules`; they must not be publicly readable.
