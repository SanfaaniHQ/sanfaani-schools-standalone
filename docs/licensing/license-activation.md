# License Activation

The licensing foundation supports these license modes:

- `subscription`
- `annual`
- `lifetime`
- `managed_contract`
- `white_label`
- `trial`
- `demo`

## Current Foundation

The following models exist:

- `License`
- `LicenseActivation`
- `LicenseAuditLog`

The following services exist:

- `LicenseKeyHasher`
- `LicenseActivationService`
- `LicenseValidationService`
- `LicenseEntitlementService`
- `LicenseAuditService`
- `LicenseServerClient`
- `LicenseDiagnosticsService`

Raw license keys are not stored. Stored records use hashes and the UI shows masked keys.

## Validation Behavior

Validation is deployment-mode-aware and license-mode-aware. Suspended and expired licenses fail closed unless offline grace rules allow temporary access.

Remote validation is stubbed for future integration and does not call a real external license server yet.

## Stage 21 Hardening

The admin license pages now show support-safe diagnostics:

- validation status;
- deployment and license mode;
- local license record presence;
- hashed/masked key storage status;
- domain matching status;
- offline grace status;
- activation record count;
- enabled entitlement and feature counts;
- module visibility reasons.

Activation validates that license keys use a safe local format before hashing. Activation attempts, successes, failures, status views, entitlement views, and manual validation checks are written to the main audit log with safe metadata only. Audit payloads must never include raw license keys, app keys, database credentials, mail credentials, provider tokens, raw `.env` values, or private paths.

## Not Implemented Yet

- Payment collection.
- Renewal billing.
- Marketplace license sync.
- Update entitlement delivery.
- Online activation server.
- Automatic remote deactivation.
