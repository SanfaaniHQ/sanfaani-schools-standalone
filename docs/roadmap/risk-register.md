# Risk Register

This register tracks commercialization risks that remain after the foundation work.

| ID | Risk | Level | Affected modes | Current mitigation | Next action |
| --- | --- | --- | --- | --- | --- |
| R-001 | Sales copy claims full billing automation before it exists. | High | SaaS, trial, marketplace | Roadmap and checklist mark billing as planned/manual. | Review landing pages, listings, contracts, and demos before launch. |
| R-002 | Buyer expects marketplace ZIP generation or one-click deployment. | High | Marketplace buyer package, `single_school`, white-label | Marketplace docs state validation foundation only. | Build package generator or keep release packaging manual. |
| R-003 | Operator assumes update manager applies code. | High | SaaS, `single_school`, managed, white-label | Update docs state metadata/preflight only. | Build real update pipeline only after backup/rollback is proven. |
| R-004 | Operator assumes restore is automated. | High | SaaS, `single_school`, managed, marketplace | Backup docs state restore plans are manual guidance only. | Build tested restore execution with safe storage and audit logs. |
| R-005 | Protected dirty files are included unintentionally. | High | All | Release readiness config and acceptance checklist name protected files. | Confirm diff before release and exclude protected files unless explicitly resolved. |
| R-006 | Tenant data crosses school boundaries. | High | All | Tenant isolation docs, middleware, authorization services, and tests. | Keep tenant isolation tests in final validation. |
| R-007 | Production `.env` or secrets are exposed. | High | All | Security docs, packaging excludes, deployment readiness checks. | Run deployment and marketplace validation before packaging or launch. |
| R-008 | Shared hosting limits cause queues, storage, or cron failures. | Medium | `single_school`, marketplace buyer package | Namecheap/cPanel docs and performance audit. | Validate hosting before buyer handover. |
| R-009 | Demo credentials remain active too long. | Medium | Demo, trial, SaaS | Demo expiry jobs and credential handling exist. | Monitor demo expiry command and activity logs. |
| R-010 | Marketing messages ignore unsubscribe or suppression. | Medium | SaaS, managed, trial | Unsubscribe and suppression foundations exist. | Validate provider-specific sending before enabling new channels. |
| R-011 | White-label buyer expects domain provisioning. | Medium | White-label, managed, marketplace | White-label docs mark domain provisioning planned. | Add domain/SSL workflow before selling as included. |
| R-012 | Parent/student portal expectations exceed current workflows. | Medium | SaaS, `single_school`, managed, marketplace | Onboarding and docs mark incomplete portal workflows as planned. | Scope portal MVP before including in paid packages. |
| R-013 | Backup metadata is mistaken for full archive safety. | Medium | All | Backup docs state metadata and safe roots only. | Add archive creation and external storage before claiming automated backups. |
| R-014 | Release readiness command is treated as deployment automation. | Medium | All | Release docs describe read-only validation. | Keep deployment steps manual until automation is implemented and tested. |
| R-015 | Branding colors or assets degrade accessibility. | Medium | White-label, managed, `single_school`, SaaS | UI docs and branding upload validation exist. | Run visual review and accessibility checks for each branded launch. |
| R-016 | Marketplace buyer lacks support expectations. | Medium | Marketplace buyer package | Buyer installation and post-purchase checklists exist. | Publish buyer FAQ and support tiers. |
| R-017 | Full test suite is skipped before launch. | High | All | Final validation list includes `php artisan test`. | Require test evidence in release approval. |

## Risk Acceptance Rule

High risks must be resolved, explicitly accepted by the release owner, or removed from launch scope before go/no-go approval.

## Current Top Risks

1. Billing automation is planned, not complete.
2. Update application is planned, not complete.
3. Automated restore is planned, not complete.
4. Marketplace ZIP generation is planned, not complete.
5. Parent/student portals remain planned where incomplete.
