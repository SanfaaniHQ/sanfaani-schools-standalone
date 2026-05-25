# Update Manifest Format

Sanfaani update packages use manifest metadata so administrators can review a package before any manual update work starts. The guided update foundation stores and validates this metadata, but it does not download, extract, apply, migrate, or roll back code.

## Required Fields

```json
{
  "version": "1.0.1",
  "channel": "stable",
  "release_date": "2026-05-25",
  "minimum_php": "8.2.0",
  "minimum_laravel": "11.0.0",
  "requires_backup": true,
  "requires_migration": false,
  "migration_notes": "Review migrations manually. The web wizard does not run them.",
  "files_changed": [
    "app/Services/ExampleService.php"
  ],
  "database_changes": [],
  "checksum": "0000000000000000000000000000000000000000000000000000000000000000",
  "signature": "planned-signature-placeholder",
  "rollback_supported": true,
  "release_notes": "Short release notes for administrators.",
  "entitlements_required": [
    "update_manager"
  ]
}
```

## Channels

Supported channels are `stable`, `beta`, and `security`.

## Safety Rules

- The checksum must be the SHA-256 hash of the uploaded package.
- Package upload validates extension and size before storing metadata.
- Packages are stored privately and are not extracted by the wizard.
- Migration requirements are warnings for manual review only.
- Backup requirements remain pending until a backup manager is implemented.
- Rollback plans are metadata only and never claim a rollback was performed.
- Shared-hosting installs should use verified backups, maintenance mode, and manual cPanel or Namecheap file manager steps.
