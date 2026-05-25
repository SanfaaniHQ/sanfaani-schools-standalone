# Feature Flags

Feature flags live in `config/features.php`.

Each feature may define:

- `enabled`
- `category`
- `deployment_modes`
- `license_modes`
- `requires_school`
- `super_admin_bypass`
- `hidden_when_disabled`
- `description`
- optional entitlement and authorization keys

## Current Commercial Feature Keys

- `saas_billing`
- `standalone_installer`
- `license_activation`
- `demo_system`
- `guided_onboarding`
- `update_manager`
- `managed_backups`
- `white_label_branding`
- `marketing_automation`

The update, backup, and full white-label systems are planned. Their feature gates exist so future screens can be safely hidden until implementation.

## School-Aware Access

School-level decisions reuse existing structures:

- `SchoolFeatureOverride`
- `SchoolSubscription`
- `PlanFeature`
- `License` entitlements

Do not create a second feature or subscription system.

## Middleware

Use:

```php
Route::middleware(['auth', 'feature:marketing_automation'])->group(function () {
    // gated routes
});
```

Unknown features fail safely.
