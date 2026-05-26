# Staging Environment Matrix

This matrix defines the expected staging behavior for each commercial mode. It is documentation for validation; it does not change runtime configuration.

## SaaS Mode

Required env values:

- `SANFAANI_DEPLOYMENT_MODE=saas`
- `SANFAANI_LICENSE_MODE=subscription`
- `SANFAANI_DEMO_ENABLED=true`
- `SANFAANI_MARKETING_AUTOMATION_ENABLED=true`

Expected enabled features:

- `saas_billing`
- `demo_system`
- `guided_onboarding`
- `marketing_automation`
- `update_manager`
- `backup_manager`
- `branding_manager`
- `performance_diagnostics`
- `security_diagnostics`

Expected hidden features:

- `standalone_installer`
- `license_activation`
- `managed_backups`
- `managed_white_label`

Expected admin routes:

- `admin.dashboard`
- `admin.schools.index`
- `admin.school-subscriptions.index`
- `admin.demo.index`
- `admin.marketing.index`
- `admin.updates.index`
- `admin.backups.index`
- `admin.branding.edit`

Expected school routes:

- `school.dashboard`
- `school.profile.edit`
- `school.results.publishing.index`
- `school.cbt.dashboard`
- `school.branding.edit`

Expected onboarding/demo/licensing behavior:

- Demo requests and sessions are available where `demo_system` is enabled.
- Guided onboarding is available for platform and school roles.
- Subscription license mode is active; standalone activation is hidden.

Expected backup/update behavior:

- Update and backup dashboards are visible as foundation workflows.
- Real update application remains planned.
- Automated restore remains planned.

Expected branding behavior:

- Platform branding is available through deployment-aware branding controls.
- White-label controls require entitlement and should not be implied for normal SaaS customers.

Known limitations:

- Full billing/payment workflow remains planned.
- Manual billing operations may be required for staging.

## single_school Mode

Required env values:

- `SANFAANI_DEPLOYMENT_MODE=single_school`
- `SANFAANI_LICENSE_MODE=annual`
- `SANFAANI_INSTALLER_ENABLED=true`
- `SANFAANI_LICENSE_VALIDATION_ENABLED=true`

Expected enabled features:

- `standalone_installer`
- `license_activation`
- `guided_onboarding`
- `update_manager`
- `backup_manager`
- `branding_manager`
- `performance_diagnostics`
- `security_diagnostics`

Expected hidden features:

- `saas_billing`
- `platform_marketing`
- `platform_demo`
- `managed_backups`
- `managed_white_label`

Expected admin routes:

- `installer.welcome`
- `admin.license.index`
- `admin.updates.index`
- `admin.backups.index`
- `admin.performance.index`
- `admin.security.index`

Expected school routes:

- `school.dashboard`
- `school.profile.edit`
- `school.mail-settings.edit`
- `school.branding.edit`
- `school.subscription.show`

Expected onboarding/demo/licensing behavior:

- Installer is available before installation lock conditions are satisfied.
- License activation is visible for local license review.
- Guided onboarding is available for school roles.
- Demo may be disabled for buyer installs.

Expected backup/update behavior:

- Guided update and backup foundations are visible.
- Real update application remains planned.
- Automated restore remains planned.

Expected branding behavior:

- Local school branding is available where `branding_manager` is enabled.
- White-label branding requires a white-label entitlement.

Known limitations:

- Buyer deployment remains guided, not one-click automated.
- Marketplace ZIP generation remains planned.

## Managed Mode

Required env values:

- `SANFAANI_DEPLOYMENT_MODE=managed`
- `SANFAANI_LICENSE_MODE=managed_contract`
- `SANFAANI_BACKUPS_ENABLED=true`
- `SANFAANI_UPDATES_ENABLED=true`

Expected enabled features:

- `license_activation`
- `guided_onboarding`
- `marketing_automation`
- `update_manager`
- `backup_manager`
- `managed_backups`
- `branding_manager`
- `white_label_branding`
- `performance_diagnostics`
- `security_diagnostics`

Expected hidden features:

- `saas_billing`
- `platform_demo`
- `standalone_license`

Expected admin routes:

- `admin.dashboard`
- `admin.schools.index`
- `admin.support-threads.index`
- `admin.updates.index`
- `admin.backups.index`
- `admin.branding.edit`

Expected school routes:

- `school.dashboard`
- `school.profile.edit`
- `school.support.index`
- `school.branding.edit`

Expected onboarding/demo/licensing behavior:

- Managed contract license mode is used.
- Guided onboarding remains available.
- Demo visibility depends on configured sales workflow.
- Support and handover ownership must be documented.

Expected backup/update behavior:

- Managed update and backup foundations are visible.
- Managed backup orchestration remains planned unless separately implemented.
- Real update application remains planned.

Expected branding behavior:

- Managed branding is available.
- White-label controls require entitlement and contract scope.

Known limitations:

- Contract-specific deployment automation remains planned.
- Managed backup/update automation remains foundation-level unless separately delivered.

## Demo Mode

Required env values:

- `SANFAANI_DEPLOYMENT_MODE=saas`
- `SANFAANI_LICENSE_MODE=demo`
- `SANFAANI_DEMO_ENABLED=true`
- `SANFAANI_DEMO_RESET_ENABLED=false`

Expected enabled features:

- `demo_system`
- `guided_onboarding`
- `marketing_automation`
- `branding_manager`

Expected hidden features:

- `backup_manager`
- `update_manager`
- `license_activation`

Expected admin routes:

- `landing.demo`
- `landing.demo.submit`
- `admin.demo.index`
- `admin.demo.show`
- `admin.marketing.index`

Expected school routes:

- `school.dashboard`
- `school.results.publishing.index`
- `school.cbt.dashboard`

Expected onboarding/demo/licensing behavior:

- Demo requests, role-based credentials, activity tracking, and expiry are available.
- Demo passwords are not stored in plain text.
- Demo reset remains disabled by default.
- Demo-to-paid billing conversion remains planned.

Expected backup/update behavior:

- Backup and update workflows should not be positioned as demo operations.

Expected branding behavior:

- Branding may be used to present a realistic demo identity.

Known limitations:

- Full sales conversion automation remains planned.
- Demo reset remains disabled unless a safe demo-only pattern exists.

## Trial Mode

Required env values:

- `SANFAANI_DEPLOYMENT_MODE=saas`
- `SANFAANI_LICENSE_MODE=trial`
- `SANFAANI_ONBOARDING_TRIAL_ENABLED=true`
- `SANFAANI_MARKETING_AUTOMATION_ENABLED=true`

Expected enabled features:

- `saas_billing`
- `demo_system`
- `guided_onboarding`
- `marketing_automation`
- `branding_manager`
- `performance_diagnostics`
- `security_diagnostics`

Expected hidden features:

- `standalone_installer`
- `managed_backups`
- `managed_white_label`

Expected admin routes:

- `admin.dashboard`
- `admin.schools.index`
- `admin.onboarding.progress`
- `admin.marketing.index`
- `admin.sales.tasks.index`

Expected school routes:

- `school.dashboard`
- `school.profile.edit`
- `school.subscription.show`

Expected onboarding/demo/licensing behavior:

- Trial-aware onboarding is visible.
- Marketing lead scoring and sales tasks can support manual conversion.
- Trial-to-paid billing conversion remains planned.

Expected backup/update behavior:

- Update and backup features should not be sold as automated trial operations.

Expected branding behavior:

- Branding manager can support realistic trial identity.

Known limitations:

- Full billing/payment workflow remains planned.
- Manual sales follow-up may be required.

## white_label Mode

Required env values:

- `SANFAANI_DEPLOYMENT_MODE=managed`
- `SANFAANI_LICENSE_MODE=white_label`
- `SANFAANI_WHITE_LABEL_ENABLED=true`
- `SANFAANI_BRAND_MODE=white_label`

Expected enabled features:

- `branding_manager`
- `white_label_branding`
- `guided_onboarding`
- `license_activation`
- `update_manager`
- `backup_manager`
- `performance_diagnostics`
- `security_diagnostics`

Expected hidden features:

- `saas_billing`
- `platform_demo`

Expected admin routes:

- `admin.branding.edit`
- `admin.license.index`
- `admin.updates.index`
- `admin.backups.index`

Expected school routes:

- `school.branding.edit`
- `school.profile.edit`
- `school.public-page.edit`

Expected onboarding/demo/licensing behavior:

- White-label entitlement must be confirmed.
- Guided onboarding is available for the buyer/operator roles.
- Demo behavior depends on contract scope.

Expected backup/update behavior:

- Backup and update foundations are available where licensed.
- Automated restore remains planned.
- Real update application remains planned.

Expected branding behavior:

- Logo, favicon, colors, email footer, report footer, and public identity should be reviewed.
- White-label domain provisioning remains planned.

Known limitations:

- Reseller tooling remains planned.
- Full theme builder remains planned.

## Marketplace Buyer Package Mode

Required env values:

- `SANFAANI_DEPLOYMENT_MODE=single_school`
- `SANFAANI_LICENSE_MODE=annual`
- `SANFAANI_INSTALLER_ENABLED=true`
- `SANFAANI_MARKETING_AUTOMATION_ENABLED=false`

Expected enabled features:

- `standalone_installer`
- `license_activation`
- `guided_onboarding`
- `update_manager`
- `backup_manager`
- `branding_manager`
- `performance_diagnostics`
- `security_diagnostics`

Expected hidden features:

- `saas_billing`
- `marketing_automation`
- `managed_backups`
- `managed_white_label`

Expected admin routes:

- `installer.welcome`
- `admin.license.index`
- `admin.updates.index`
- `admin.backups.index`
- `admin.performance.index`

Expected school routes:

- `school.dashboard`
- `school.profile.edit`
- `school.mail-settings.edit`
- `school.branding.edit`

Expected onboarding/demo/licensing behavior:

- Buyer follows installer and license activation docs.
- Demo and marketing features should be disabled unless explicitly packaged for sales review.

Expected backup/update behavior:

- Buyer gets guided backup and update foundations.
- Automated restore remains planned.
- Real update application remains planned.

Expected branding behavior:

- Buyer can configure local branding where licensed.
- White-label behavior requires entitlement.

Known limitations:

- Marketplace ZIP generation remains planned.
- Marketplace API integration remains planned.
- Buyer deployment remains guided, not one-click automated.
