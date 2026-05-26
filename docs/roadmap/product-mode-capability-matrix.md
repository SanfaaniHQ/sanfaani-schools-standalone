# Product Mode Capability Matrix

This matrix records the commercial modes Sanfaani Schools can now describe and validate. It distinguishes current production use, foundation-ready behavior, and planned automation.

## Mode Definitions

| Mode | Config or package path | Current capability | Current boundary |
| --- | --- | --- | --- |
| SaaS | `SANFAANI_DEPLOYMENT_MODE=saas`, `SANFAANI_LICENSE_MODE=subscription` | Central Sanfaani-hosted platform behavior, school management, feature overrides, subscriptions visibility, demo, onboarding, marketing, diagnostics, release readiness. | Full billing/payment automation remains planned. |
| `single_school` | `SANFAANI_DEPLOYMENT_MODE=single_school`, annual or lifetime license | Licensed local installation, installer foundation, license activation, local school settings, guided updates/backups, diagnostics, branding. | One-click deployment, marketplace ZIP generation, real update application, and automated restore remain planned. |
| Managed | `SANFAANI_DEPLOYMENT_MODE=managed`, `SANFAANI_LICENSE_MODE=managed_contract` | Sanfaani-operated or partner-operated deployment behavior, managed support, managed updates/backups visibility, diagnostics, branding, white-label gates. | Managed automation depends on contract scope and remains manual/foundation-level by default. |
| White-label | `SANFAANI_LICENSE_MODE=white_label` and `white_label_branding` entitlement | School or managed-client identity controls, safe branding assets, white-label docs and checklists. | Domain provisioning, reseller tooling, full theme builder, and branded package generation remain planned. |
| Demo | `SANFAANI_LICENSE_MODE=demo`, `demo_system` feature | Demo requests, role-based demo sessions, credentials, activity tracking, expiry, and sales handoff foundation. | Safe automated reset and full conversion automation remain planned. |
| Trial | `SANFAANI_LICENSE_MODE=trial` | Trial-aware feature visibility, onboarding, demo, and marketing lead-scoring foundations. | Trial-to-paid billing conversion remains planned. |
| Marketplace buyer package | Marketplace package docs, `.env.marketplace.example`, `marketplace:validate-package` | Buyer package structure, include/exclude rules, buyer install checklist, validation command, listing copy foundation. | Final marketplace ZIP generation and marketplace API integration remain planned. |

## Capability Matrix

| Capability | SaaS | `single_school` | Managed | White-label | Demo | Trial | Marketplace buyer package |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Deployment mode separation | Ready | Ready | Ready | Via license and feature gates | Ready | Ready | Documented for buyer package |
| Feature and module gating | Ready | Ready | Ready | Ready | Ready | Ready | Ready through package config |
| Tenant isolation | Ready | Ready | Ready | Ready | Ready | Ready | Must be validated after install |
| Installer | Not primary | Foundation-ready | Foundation-ready where enabled | Foundation-ready where packaged | Not primary | Optional | Foundation-ready |
| License activation | Subscription mode foundation | Foundation-ready | Foundation-ready | Foundation-ready | Demo mode foundation | Trial mode foundation | Buyer activates manually |
| Billing/payment automation | Planned | Planned/manual | Planned/manual | Planned/manual | Not applicable | Planned conversion | Planned/manual |
| Demo automation | Foundation-ready | Foundation-ready where enabled | Foundation-ready | Foundation-ready | Foundation-ready | Foundation-ready | Demo script documented |
| Guided onboarding | Foundation-ready | Foundation-ready | Foundation-ready | Foundation-ready | Foundation-ready | Foundation-ready | Buyer docs include onboarding |
| Marketing automation | Foundation-ready | Limited/manual by default | Foundation-ready | Foundation-ready where offered | Foundation-ready | Foundation-ready | Planned buyer automation |
| Update management | Metadata/preflight foundation | Metadata/preflight foundation | Metadata/preflight foundation | Metadata/preflight foundation | Not primary | Not primary | Planned after purchase |
| Real update application | Planned | Planned | Planned | Planned | Planned | Planned | Planned |
| Backup management | Metadata/verification foundation | Metadata/verification foundation | Metadata/verification foundation | Metadata/verification foundation | Not primary | Not primary | Manual buyer backup |
| Automated restore | Planned | Planned | Planned | Planned | Planned | Planned | Planned |
| Branding | Ready | Foundation-ready | Foundation-ready | Foundation-ready | Foundation-ready | Foundation-ready | Buyer follows branding docs |
| White-label branding | Entitlement gated | Entitlement gated | Entitlement gated | Foundation-ready | Not primary | Entitlement gated | Foundation-ready where licensed |
| Marketplace packaging | Validation foundation | Validation foundation | Validation foundation | Validation foundation | Demo checklist | Trial docs | Validation foundation |
| Marketplace ZIP generation | Planned | Planned | Planned | Planned | Planned | Planned | Planned |
| Parent/student portals | Planned where incomplete | Planned where incomplete | Planned where incomplete | Planned where incomplete | Placeholder orientation | Placeholder orientation | Planned where incomplete |
| Release readiness | Ready | Ready | Ready | Ready | Ready | Ready | Ready |

## Production Positioning Guidance

- Position SaaS as ready for controlled launch with manual billing operations unless full billing automation is implemented later.
- Position `single_school` as a guided licensed installation, not a fully automated marketplace installer.
- Position managed deployments as service-backed installations with documented support, backup, and update responsibilities.
- Position white-label as branding and entitlement foundation-ready, not a reseller/domain automation suite.
- Position demo and trial as acquisition and onboarding foundations, not automatic paid conversion flows.
- Position marketplace buyer package as documentation and validation-ready, not ZIP-generation-ready.
