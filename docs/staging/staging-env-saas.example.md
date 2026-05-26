# SaaS Staging Env Example

Use these placeholder values for a SaaS-mode staging `.env`. Replace values on the staging host only.

```dotenv
APP_NAME="Sanfaani Schools Staging"
APP_VERSION=1.0.0
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://staging.example.test

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sanfaani_saas_staging
DB_USERNAME=sanfaani_saas_staging_user
DB_PASSWORD=replace_with_staging_database_password

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_HOST=smtp.staging.example.test
MAIL_PORT=587
MAIL_USERNAME=replace_with_staging_smtp_username
MAIL_PASSWORD=replace_with_staging_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@staging.example.test
MAIL_FROM_NAME="${APP_NAME}"

SANFAANI_DEPLOYMENT_MODE=saas
SANFAANI_LICENSE_MODE=subscription
SANFAANI_INSTALLER_ENABLED=false
SANFAANI_INSTALLED=true
SANFAANI_LICENSE_VALIDATION_ENABLED=true

SANFAANI_DEMO_ENABLED=true
SANFAANI_DEMO_RESET_ENABLED=false
SANFAANI_ONBOARDING_ENABLED=true
SANFAANI_ONBOARDING_TRIAL_ENABLED=true

SANFAANI_MARKETING_AUTOMATION_ENABLED=true
SANFAANI_MARKETING_EMAIL_ENABLED=true
SANFAANI_MARKETING_WHATSAPP_ENABLED=false
SANFAANI_MARKETING_SALES_TASKS_ENABLED=true
SANFAANI_MARKETING_UNSUBSCRIBE_ENABLED=true

SANFAANI_UPDATES_ENABLED=true
SANFAANI_BACKUPS_ENABLED=true
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

## SaaS Checks

- [ ] Platform dashboard loads for Super Admin.
- [ ] School management routes are visible.
- [ ] Subscription screens are visible as foundation workflows.
- [ ] Demo, onboarding, marketing, branding, backups, updates, performance, and security diagnostics are visible where expected.
- [ ] Installer routes are not part of the normal SaaS staging path.
- [ ] Full billing automation remains planned.
