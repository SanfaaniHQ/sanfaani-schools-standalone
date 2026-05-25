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

Raw license keys are not stored. Stored records use hashes and the UI shows masked keys.

## Validation Behavior

Validation is deployment-mode-aware and license-mode-aware. Suspended and expired licenses fail closed unless offline grace rules allow temporary access.

Remote validation is stubbed for future integration and does not call a real external license server yet.

## Not Implemented Yet

- Payment collection.
- Renewal billing.
- Marketplace license sync.
- Update entitlement delivery.
