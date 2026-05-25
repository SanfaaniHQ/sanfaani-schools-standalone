# Package Validation Checklist

The validation command checks readiness only. It does not create a ZIP or copy files.

## Automated Checks

- Required marketplace docs exist.
- `.env.marketplace.example` exists.
- The env template does not contain obvious real secrets.
- Installer docs exist.
- License docs exist.
- Update docs exist.
- Backup docs exist.
- Prohibited paths are listed in exclusions.
- `public/build.zip` is excluded.

Run:

```bash
php artisan marketplace:validate-package
```

## Manual Checks

- Confirm screenshots use demo data.
- Confirm listing copy does not exaggerate planned features.
- Confirm no production `.env`, real SMTP, payment keys, backups, logs, local paths, or private uploads are included.
- Confirm buyer docs state hosting, domain, database, SMTP, and license setup responsibilities.
