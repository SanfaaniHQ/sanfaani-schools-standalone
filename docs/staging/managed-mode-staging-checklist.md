# Managed Mode Staging Checklist

## Environment

- [ ] `SANFAANI_DEPLOYMENT_MODE=managed`
- [ ] `SANFAANI_LICENSE_MODE=managed_contract`
- [ ] `SANFAANI_BACKUPS_ENABLED=true`
- [ ] `SANFAANI_UPDATES_ENABLED=true`
- [ ] `SANFAANI_MARKETING_AUTOMATION_ENABLED=true` when managed sales/support workflows are in scope.
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`

## Expected Features

- [ ] Managed support visibility is available.
- [ ] Managed updates and backups are visible as foundation workflows.
- [ ] Managed performance and security diagnostics are visible.
- [ ] Managed branding is visible.
- [ ] White-label controls require license entitlement and contract scope.
- [ ] SaaS billing and unmanaged standalone paths are hidden unless explicitly configured.

## Smoke Tests

- [ ] Super Admin dashboard loads.
- [ ] Managed client school record loads.
- [ ] Support thread list and school support routes load.
- [ ] Managed backup and update dashboards load.
- [ ] Performance and security diagnostic screens load.
- [ ] Branding settings load for platform/managed context.
- [ ] Handover contacts and backup/update ownership are recorded.

## Limitations

- [ ] Managed deployment automation remains planned unless separately implemented.
- [ ] Managed backup orchestration remains foundation-level unless separately implemented.
- [ ] Real update application remains planned.
- [ ] Automated restore remains planned.
