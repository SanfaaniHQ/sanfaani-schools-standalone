# License Workflow

> **Historical/dormant workflow:** This workflow is not active while `SANFAANI_LICENSE_VALIDATION_ENABLED=false`, which is the source-code default. Current standalone installations skip activation and do not require a license or signing key. See [Temporary License Disablement](temporary-license-disablement.md).

This workflow explains how Sanfaani Schools Standalone licensing works for marketplace-style cPanel installations.

## Simple Model

Sanfaani issues a license key for a school and domain. The buyer installs the cPanel package, completes the installer, logs in as the first admin, and activates the license from the admin license page.

The v1 license flow is local-first. It does not contact a remote license server, collect payments, renew subscriptions automatically, or deactivate installations remotely.

## Buyer Flow

1. The school pays Sanfaani or receives an approved trial/demo.
2. Sanfaani generates a license key for the school, domain, type, dates, and included services.
3. The school uploads and extracts the cPanel package.
4. The school creates the database, `.env`, app key, storage permissions, cron, and SMTP settings.
5. The school opens `https://portal.example.com/install` and completes hosting, admin, and school setup.
6. The installer writes the installation lock.
7. The first admin logs in and opens `Admin -> License`.
8. The admin enters the license key and activates it.
9. The app verifies the key, stores the local license record securely, and shows only masked license status afterward.

License activation is after login in v1. The public installer does not collect the license key.

## Seller Flow

Generate a license key from a trusted Sanfaani seller environment:

```bash
php artisan license:generate --type=annual --school="Demo School" --domain=portal.demo-school.test --starts=2026-01-01 --expires=2027-01-01 --entitlements=standard,reports --issued-by="Sanfaani"
```

Copy the generated license key and send it to the customer through an approved support or sales channel. Do not send the signing key.

The seller environment must define:

```dotenv
SANFAANI_LICENSE_SIGNING_KEY=
```

Use a strong secret value outside Git. The deployed customer `.env` must stay private and must never be publicly readable.

Customer cPanel portals do not need `SANFAANI_LICENSE_SIGNING_KEY` to install, log in, or use normal customer activation. Seller license generation remains separate. If signed-key verification is used for a customer portal, Sanfaani must configure that verification securely during approved setup; the school should never enter or receive the seller signing secret.

## License Types

Supported local license modes:

- `trial`
- `annual`
- `lifetime`
- `demo`
- `managed_contract`
- `white_label`

The generator also accepts `--type=managed` as a seller-friendly alias for `managed_contract`.

Trial, annual, and demo licenses require an expiry date. Lifetime licenses may omit expiry. Managed contract and white-label licenses can use expiry when the commercial agreement requires it.

## Domain Binding

Generated licenses include a primary domain and allowed-domain list. Local validation compares the active request host against the license domain when `SANFAANI_LICENSE_REQUIRE_DOMAIN_MATCH=true`.

If the domain does not match, validation fails with a domain mismatch. The buyer should confirm `APP_URL`, cPanel document root, DNS, SSL, and the domain used when the license was issued.

## Expiry Rules

Expired annual, trial, and demo signed keys cannot be activated. Existing local records also fail validation after expiry unless the current installation is inside the configured offline grace window.

Lifetime licenses do not expire unless suspended or revoked by a future support workflow.

## Included Services

Included services are stored as enabled flags on the local license record. Examples:

- `standard`
- `white_label`
- `reports`
- `backup_manager`
- `update_manager`

Feature visibility can use these values through the existing license service. Limits such as maximum users or students are stored as signed metadata for v1; automatic enforcement can be added later if needed.

## Security Notes

- Do not share `SANFAANI_LICENSE_SIGNING_KEY`.
- Do not commit real signing secrets.
- Do not store raw license keys unnecessarily.
- Do not put license keys, app keys, database passwords, SMTP credentials, or payment keys in screenshots or support chat.
- Keep `.env` outside public web access.
- Use HTTPS before activating a production school.
- Raw license keys are hashed before storage and are not shown again in the UI.
- The signed key is verified during activation; normal runtime validation uses the local hashed license record and metadata.

## cPanel Marketplace Relationship

The marketplace package prepares the application for upload-and-install cPanel usage. Licensing is the commercial step after installation:

1. cPanel setup creates the runtime environment.
2. `/install` creates the school and first admin.
3. `Admin -> License` activates the customer license.
4. The installation lock prevents public reinstall.

Before the installer is completed, `/` should guide the buyer into setup and `/login` should redirect to `/install`. After the lock exists, `/` should point to login or the portal flow and `/install` should be blocked.

## Limitations In v1

- No remote license server.
- No online activation API.
- No automatic payment gateway or billing enforcement.
- No automatic renewal charging.
- No remote deactivation.
- No automatic remote revocation.
- Manual license issuance by Sanfaani for v1.
- cPanel domain, database, cron, SMTP, storage, and backup setup remain manual hosting tasks.
