# Managed Staging Env Example

Use these placeholder values for a managed-client staging `.env`. Replace values on the staging host only.

```dotenv
APP_NAME="Managed Sanfaani Staging"
APP_VERSION=1.0.0
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://managed-staging.example.test

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sanfaani_managed_staging
DB_USERNAME=sanfaani_managed_user
DB_PASSWORD=replace_with_staging_database_password

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_HOST=smtp.staging.example.test
MAIL_PORT=587
MAIL_USERNAME=replace_with_staging_smtp_username
MAIL_PASSWORD=replace_with_staging_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@managed-staging.example.test
MAIL_FROM_NAME="${APP_NAME}"

SANFAANI_DEPLOYMENT_MODE=managed
SANFAANI_LICENSE_MODE=managed_contract
SANFAANI_INSTALLER_ENABLED=false
SANFAANI_INSTALLED=true
SANFAANI_LICENSE_VALIDATION_ENABLED=true

SANFAANI_DEMO_ENABLED=false
SANFAANI_ONBOARDING_ENABLED=true
SANFAANI_MARKETING_AUTOMATION_ENABLED=true
SANFAANI_MARKETING_EMAIL_ENABLED=true
SANFAANI_MARKETING_WHATSAPP_ENABLED=false

SANFAANI_UPDATES_ENABLED=true
SANFAANI_BACKUPS_ENABLED=true
SANFAANI_BACKUP_PRE_UPDATE_REQUIRED=true
SANFAANI_BACKUP_VERIFY_AFTER_CREATE=true

SANFAANI_BRANDING_ENABLED=true
SANFAANI_BRAND_MODE=default
SANFAANI_WHITE_LABEL_ENABLED=false

SANFAANI_PERFORMANCE_MODE=shared_hosting
SANFAANI_SHARED_HOSTING_SAFE_MODE=true
SANFAANI_SECURITY_DIAGNOSTICS_ENABLED=true
SANFAANI_EMAIL_SAFETY_ENABLED=true
SANFAANI_SECRET_REDACTION_ENABLED=true
SANFAANI_PRODUCTION_ERROR_SAFE_MODE=true

PAYMENT_DEFAULT_GATEWAY=manual
PAYSTACK_ENABLED=false
FLUTTERWAVE_ENABLED=false
VITE_APP_NAME="${APP_NAME}"
```

## Managed Checks

- [ ] Managed support, backups, updates, branding, performance, and security diagnostics are visible where expected.
- [ ] SaaS billing automation is not represented as complete.
- [ ] Managed backup automation remains subject to contract-specific operations.
- [ ] Real update application remains planned.
- [ ] Automated restore remains planned.
