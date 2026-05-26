# Remaining Work Register

This register lists work that remains planned after the commercialization foundation. It should be reviewed before each release and before any sales promise.

## P0 Before Broad Production Launch

| Work | Status | Why it matters | Acceptance signal |
| --- | --- | --- | --- |
| Final validation evidence | Required | Launch decisions need proof that tests, routes, diagnostics, and release checks pass. | Final validation commands pass and results are recorded. |
| Production environment review | Required | Misconfigured `.env`, mail, queue, cache, storage, database, or license values can break launch. | Deployment readiness report is accepted. |
| Manual backup and rollback plan | Required | Update and restore automation are not complete. | Backup exists, restore plan is documented, owner is named. |
| Sales and marketplace claim review | Required | Commercial copy must not overstate billing, updates, backups, marketplace packaging, portals, or automation. | Launch copy matches this roadmap. |
| Support ownership | Required | Production users need a clear response path. | Support owner, escalation owner, and incident owner are named. |

## P1 Commercial Automation

| Work | Status | Why it matters | Acceptance signal |
| --- | --- | --- | --- |
| Full billing/payment workflow | Planned | SaaS self-service needs invoicing, renewal, payment callbacks, reconciliation, dunning, and plan enforcement. | Billing tests and live gateway sandbox validation pass. |
| Remote license server integration | Planned | Standalone and marketplace licensing need trusted activation and renewal checks. | Remote validation succeeds, failure modes are tested, offline grace is documented. |
| Trial-to-paid conversion | Planned | Trial and demo sales need reliable conversion tracking. | Trial conversion events update license/billing state without manual database work. |
| Marketplace license sync | Planned | Marketplace buyers need a reliable entitlement path. | Marketplace purchase metadata maps to license activation. |

## P2 Operations Automation

| Work | Status | Why it matters | Acceptance signal |
| --- | --- | --- | --- |
| Real update download and application | Planned | Current update work is metadata/preflight only. | Update package can be downloaded, verified, applied, migrated, audited, and rolled back in a controlled test. |
| Backup archive creation | Planned | Current backup work tracks metadata and safe file roots. | Database, file, and sanitized config archives can be created safely outside public web roots. |
| Automated restore execution | Planned | Current restore plans are manual guidance only. | Restore flow is tested against backup archives with tenant-safe and secret-safe controls. |
| External backup storage | Planned | Production needs durable storage beyond local disk. | Storage provider is configured, encrypted, rotated, and restore-tested. |
| Managed deployment automation | Planned | Managed clients may need repeatable provisioning. | Contract-specific setup can be run with review and rollback controls. |

## P3 Marketplace And White-Label

| Work | Status | Why it matters | Acceptance signal |
| --- | --- | --- | --- |
| Final marketplace ZIP generation | Planned | Marketplace submission requires a clean package artifact. | Package generation produces a verified archive without prohibited paths. |
| Marketplace API integration | Planned | Manual marketplace operations do not scale. | Listing/package upload and buyer metadata sync are tested in sandbox or approved workflow. |
| White-label domain provisioning | Planned | White-label buyers may expect branded domains. | Domain, SSL, callback URLs, mail identity, and tenant-safe branding are provisioned safely. |
| Reseller tooling | Planned | Resellers need controlled client/package management. | Reseller roles, limits, audit logs, and support boundaries are implemented and tested. |
| Full theme builder | Planned | Current branding is controlled fields and assets. | Theme changes remain accessible, validated, and reversible. |

## P4 Product Experience

| Work | Status | Why it matters | Acceptance signal |
| --- | --- | --- | --- |
| Full parent portal | Planned | Parent onboarding currently includes placeholders where portal work is incomplete. | Parents can authenticate, view children, results, notifications, and supported billing/scratch-card flows. |
| Full student portal | Planned | Student onboarding currently includes placeholders where portal work is incomplete. | Students can authenticate, view assigned academic content, CBT/results, and notifications safely. |
| Onboarding builder UI | Planned | Current checklists are foundation/config driven. | Admins can manage checklist templates without unsafe config edits. |
| Provider-specific WhatsApp sending | Planned | Current WhatsApp marketing hooks are placeholders. | Provider credentials, opt-out, rate limits, and delivery logs are tested. |
| Advanced reporting and analytics | Planned | Commercial tiers may need richer reporting. | Reports are gated, performant, and tenant-safe. |

## P5 Documentation And Support

| Work | Status | Why it matters | Acceptance signal |
| --- | --- | --- | --- |
| Public docs publishing workflow | Planned | `doc.sanfaani.net` structure exists but needs publishing operations. | Docs build, publish, and versioning workflow is documented and tested. |
| Buyer FAQ and video walkthrough | Planned | Marketplace and single-school buyers need self-serve help. | Buyer can complete install from docs without internal notes. |
| Incident response playbook | Planned | Launch support needs predictable handling. | Incident severity, roles, communication, rollback, and postmortem workflow are approved. |
| Post-launch metrics dashboard | Planned | Operators need usage and failure visibility. | Login, result checker, mail, queue, storage, demo, and payment metrics are monitored. |

## Items Not To Claim Until Completed

- Full billing/payment automation.
- Marketplace ZIP generation.
- Real update download, extraction, patching, or application.
- Automated restore execution.
- Full parent/student portals.
- White-label domain provisioning and reseller automation.
