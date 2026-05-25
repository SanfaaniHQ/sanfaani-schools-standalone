# Marketplace Packaging Plan

This foundation prepares Sanfaani Schools for direct sales, marketplace review, reseller delivery, managed onboarding, single-school licensed buyers, white-label buyers, and demo/trial sales flows.

## Current Foundation

- Package structure and include/exclude documentation.
- Buyer-safe `.env.marketplace.example`.
- Marketplace listing draft.
- Buyer, reseller, white-label, managed handover, screenshot, demo, release, and validation checklists.
- Non-destructive `marketplace:validate-package` readiness command.

## Target Package Modes

- `marketplace_single_school`: public marketplace package for one school.
- `direct_single_school`: direct buyer package with sales-assisted setup.
- `managed_client`: Sanfaani-operated deployment handover package.
- `white_label`: buyer-branded package where license terms allow.
- `demo_sales`: demo/trial package for controlled sales review.

## Required Foundation References

- Installer: `docs/installation/single-school-installer.md`
- Licensing: `docs/licensing/license-activation.md`
- Updates: `docs/updates/update-system-plan.md`
- Backups: `docs/backups/backup-system-plan.md`
- Tenant isolation: `docs/architecture/tenant-isolation.md`
- Deployment modes: `docs/architecture/deployment-modes.md`

## Packaging Boundary

This step does not generate a production ZIP, copy files, integrate marketplace APIs, run billing, or deploy code. It defines the package manifest and readiness checks only.

## Do Not Claim Yet

- One-click marketplace deployment.
- Real marketplace API integration.
- Automated update delivery.
- Automated restore execution.
- Full billing or payment workflow.
