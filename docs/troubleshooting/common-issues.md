# Common Issues

## Installer Is Blocked

Check deployment mode, `standalone_installer` feature, `SANFAANI_INSTALLED`, and `storage/app/installed.lock`.

## License Page Shows Missing or Invalid

Check license mode, license record status, expiry, domain matching, and offline grace.

## Demo Access Expired

Demo sessions and credentials expire. Review demo session status and expiry timestamps.

## Feature Is Hidden

Check `config/features.php`, deployment mode, license mode, school override, subscription entitlement, license entitlement, and user authorization.

## Uploaded Files Do Not Render

Check `APP_URL`, `FILESYSTEM_DISK`, storage link, permissions, and tenant-safe storage paths.

## Marketing Email Did Not Send

Check `SANFAANI_MARKETING_EMAIL_ENABLED`, suppression/unsubscribe records, mail config, queue status, and lead email validity.
