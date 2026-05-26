# Next 30/60/90 Days

This roadmap turns the commercialization foundation into launch work, operating discipline, and later automation.

## Next 30 Days

| Priority | Work | Outcome |
| --- | --- | --- |
| P0 | Run final validation commands and record results. | Release owner has objective go/no-go evidence. |
| P0 | Resolve launch blockers from `production-launch-readiness.md`. | No known blocker remains unowned. |
| P0 | Review sales, website, marketplace, and buyer copy. | Copy does not claim unfinished billing, update, backup, marketplace, portal, or reseller automation. |
| P0 | Prepare production `.env`, SMTP, database, storage, cache, queue, cron, license, update, and backup settings. | Target environment is ready for deployment validation. |
| P0 | Complete manual backup and rollback drill. | Launch can be reversed if deployment fails. |
| P1 | Pilot core school workflows with one internal or friendly school. | Real-world issues are found before broad launch. |
| P1 | Finalize support ownership and incident response. | Users know where to go and operators know who owns issues. |
| P1 | Prepare marketplace screenshots and buyer install walkthrough. | Buyer package can be reviewed without overstating automation. |

## Next 60 Days

| Priority | Work | Outcome |
| --- | --- | --- |
| P0 | Define billing architecture and operating model. | SaaS can either launch with manual billing or wait for automation. |
| P1 | Implement remote license validation plan or formalize manual licensing. | Standalone and marketplace license risk is reduced. |
| P1 | Expand managed client handover process. | Managed deployments have clear backup, update, support, and escalation owners. |
| P1 | Add support FAQ and incident response playbook. | Support can operate predictably after launch. |
| P1 | Expand onboarding and marketing conversion events where safe. | Demo/trial/sales handoff improves without creating false billing promises. |
| P2 | Begin update delivery design. | Real update application has security, backup, and rollback requirements before coding. |
| P2 | Begin backup archive and storage design. | Automated restore is not attempted before archive safety is defined. |

## Next 90 Days

| Priority | Work | Outcome |
| --- | --- | --- |
| P0 | Build full billing/payment automation if required by SaaS launch. | SaaS self-service claims become supportable. |
| P1 | Build remote license server integration. | Licenses can be activated, renewed, suspended, and audited centrally. |
| P1 | Build marketplace ZIP generation after include/exclude rules are enforced. | Marketplace package can be produced safely. |
| P1 | Build real update download/application pipeline after backup/rollback proof. | Update manager moves from guided preflight to controlled delivery. |
| P1 | Build backup archive creation and automated restore only after secure storage is ready. | Backup manager moves from metadata foundation to operational recovery. |
| P2 | Build parent and student portals if included in commercial packages. | Portal promises match shipped workflows. |
| P2 | Build white-label domain and reseller tooling if included in offers. | White-label sales can scale beyond manual branding configuration. |

## Decision Points

- At day 30, decide whether launch is manual-commercial or delayed for billing automation.
- At day 60, decide whether marketplace packaging remains manual or needs generator work before public sale.
- At day 90, decide whether updates/restores can move from guided foundation to automated operations.
