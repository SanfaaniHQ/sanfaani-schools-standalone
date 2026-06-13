# Role-Based Documentation Map

| Role | Primary docs | Notes |
| --- | --- | --- |
| Super Admin | `super-admin/platform-admin-manual.md`, `architecture/deployment-modes.md`, `marketing/marketing-automation.md` | Platform-wide access is intentional for SaaS and managed operations. |
| School Admin | `users/school-admin-manual.md`, `onboarding/guided-onboarding.md` | School-scoped operations only. |
| Teacher | `users/teacher-manual.md` | Teacher access depends on assignments and enabled role features. |
| Parent | `users/parent-manual.md` | Parent portal is planned/foundation-level; do not claim full portal availability. |
| Student | `users/student-manual.md` | Student portal is planned/foundation-level; do not claim full portal availability. |
| Result Officer | `users/result-officer-manual.md` | Result workflow access is school-scoped. |
| Accountant | `users/accountant-manual.md` | Accounting docs currently cover payment/scratch-card review foundations. |
| Sales Team | `commercial/product-packaging-review.md`, `commercial/sales-discovery-guide.md`, `commercial/buyer-facing-feature-list.md`, `marketing/marketing-automation.md`, `demo/demo-automation.md`, `onboarding/guided-onboarding.md` | Use commercial packaging, CRM, demo, onboarding, and sales task foundations without overclaiming planned systems. |
| Support Team | `support/support-runbooks.md`, `support/issue-triage.md`, `support/support-playbook.md`, `security/security-overview.md`, `troubleshooting/common-issues.md` | Support must respect tenant boundaries and current product boundaries. |
| Deployment Engineer | `deployment/namecheap-shared-hosting.md`, `deployment/cpanel-hosting.md`, `deployment/vps-hosting.md`, `installation/single-school-installer.md` | Installer exists as a foundation, not full marketplace automation. |
| Developer | `developer/local-development.md`, `developer/contribution-guide.md`, `architecture/feature-flags.md` | New commercial behavior must use mode, feature, license, tenant, and authorization checks. |
| Marketplace Buyer | `commercial/buyer-facing-feature-list.md`, `marketplace/marketplace-packaging-plan.md`, `installation/single-school-installer.md` | Packaging is planned, not complete. Commercial docs clarify buyer-facing features and limits. |
| White-label Buyer | `white-label/white-label-readiness.md`, `licensing/license-activation.md`, `school-operations/branding.md` | White-label license mode exists; school branding fields and assets are supported, while public website/domain automation remains planned. |
| Website Add-On Buyer | `website/nextjs-school-website-add-on.md`, `website/website-laravel-link-contract.md`, `website/website-deployment-positioning.md` | Future Next.js website work belongs in a separate repo and links back to Laravel for portal login and admissions. |
| Managed Client | `architecture/deployment-modes.md`, `support/support-runbooks.md`, `licensing/license-activation.md` | Managed deployment behavior exists; backup and update foundations are guided/manual unless a managed contract adds more. |
