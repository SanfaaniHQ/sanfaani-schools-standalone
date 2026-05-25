# Managed Client Deployment

Managed client hosting is for Sanfaani-operated or partner-operated deployments.

## Mode And License

Use:

```dotenv
SANFAANI_DEPLOYMENT_MODE=managed
SANFAANI_LICENSE_MODE=managed_contract
```

Confirm license validation, support responsibilities, backup policy, update channel, and handover contacts.

## Hosting

- VPS or cloud hosting is preferred.
- Shared hosting is allowed only when queues, cron, backups, and support expectations are realistic.
- Keep secrets in `.env` or a secret manager.

## Deployment Flow

- Validate package readiness.
- Configure hosting, domain, database, SMTP, queues, cron, and storage.
- Run installer or managed setup flow.
- Activate/validate license.
- Create initial backup metadata and manual export.
- Run deployment readiness report.

## Handover

- Record hosting provider and access boundaries.
- Record support contacts.
- Record backup and update responsibilities.
- Record rollback plan.

Managed updates and backups remain safe foundations unless a separate managed automation contract is implemented.
