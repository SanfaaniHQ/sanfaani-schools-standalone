# Update Package Release Workflow

- Validate version and channel metadata.
- Review update manifest fields.
- Confirm update entitlement expectations.
- Run update preflight.
- Confirm migration warnings are documented and migrations are not run from the web wizard.
- Confirm backup requirement status.
- Confirm rollback plan metadata exists.
- Confirm update logs do not expose secrets.
- Confirm controller audit logs exist for upload and preflight actions.
- Confirm the package remains metadata-only: not extracted, not applied, and no migrations run from the web wizard.
- Do not download, extract, or apply real external packages in this release foundation.
