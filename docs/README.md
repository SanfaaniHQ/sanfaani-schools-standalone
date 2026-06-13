# Sanfaani Schools Documentation

This directory is the source structure for the future documentation site at `https://doc.sanfaani.net`.

The docs must reflect the current product honestly. The following foundations exist now:

- Deployment mode and license mode foundation.
- Feature flag and module gating foundation.
- SaaS, single-school, and managed deployment behavior separation.
- Tenant isolation audit and security hardening coverage.
- Standalone installer foundation for single-school deployments.
- Licensing activation, validation, entitlement, and audit foundation.
- Demo automation foundation.
- Role-based guided onboarding foundation.
- Lead nurturing, marketing automation, sales task, and unsubscribe foundation.
- Guided update package, preflight, and recovery-plan metadata foundation.
- Backup metadata and pre-update backup workflow foundation.
- Marketplace packaging workflow foundation.
- Namecheap, cPanel, VPS, and cloud deployment readiness guides.
- Shared-hosting performance diagnostics and hardening foundation.
- Production security and outbound email hardening diagnostics.
- Deployment-aware branding and white-label foundation.
- Enterprise testing and release readiness workflow foundation.
- Enterprise UI components and dashboard standardization foundation.
- Final commercialization roadmap and acceptance documentation.
- Staging release candidate validation and launch preparation workflow.
- Staging deployment execution checklists, command sequence, env examples, and signoff templates.
- Stage 23 support runbooks, issue triage, release handoff, and standalone product support overview.
- Stage 24 commercial packaging review, buyer-facing feature list, pricing/plan positioning, demo readiness, sales discovery, implementation handoff, module boundaries, standalone-vs-SaaS positioning, support/maintenance positioning, and commercial release readiness.
- Stage 25 Next.js school website add-on foundation documents the future separate website repo strategy and Laravel link contract only. It does not create a Next.js app in this Laravel repo.
- Stage 26 final QA release-candidate report and v1 release-candidate checklist consolidate regression validation, manual QA, protected-file checks, deployment readiness, and deferred launch limitations.
- Stage 27 v1 production-readiness docs consolidate release notes, go-live conditions, known limitations/backlog, and deployment handoff for `v1.0.0-rc1`.

The following systems are planned and must not be described as complete:

- Real update download and application.
- Automated restore execution.
- Marketplace ZIP generation and marketplace API integration.
- Full billing/payment automation.
- Full parent and student portal workflows.
- White-label domain provisioning, full theme builder, and reseller tooling.

## Roadmap And Acceptance

- `roadmap/final-commercialization-roadmap.md`
- `roadmap/commercialization-acceptance-checklist.md`
- `roadmap/product-mode-capability-matrix.md`
- `roadmap/remaining-work-register.md`
- `roadmap/production-launch-readiness.md`
- `roadmap/risk-register.md`
- `roadmap/next-30-60-90-days.md`
- `roadmap/final-executive-summary.md`

## Staging

- `staging/staging-release-candidate-plan.md`
- `staging/staging-validation-checklist.md`
- `staging/staging-environment-matrix.md`
- `staging/staging-mode-test-plan.md`
- `staging/staging-smoke-test-results-template.md`
- `staging/staging-go-no-go-checklist.md`
- `staging/staging-known-issues.md`
- `staging/staging-handover-notes.md`
- `staging/real-staging-deployment-runbook.md`
- `staging/staging-env-template.md`
- `staging/saas-mode-staging-checklist.md`
- `staging/single-school-mode-staging-checklist.md`
- `staging/managed-mode-staging-checklist.md`
- `staging/white-label-staging-checklist.md`
- `staging/marketplace-buyer-staging-checklist.md`
- `staging/demo-trial-staging-checklist.md`
- `staging/staging-smoke-test-checklist.md`
- `staging/staging-go-no-go-report-template.md`
- `staging/staging-incident-rollback-checklist.md`
- `staging/staging-deployment-execution-checklist.md`
- `staging/staging-server-command-sequence.md`
- `staging/staging-env-saas.example.md`
- `staging/staging-env-single-school.example.md`
- `staging/staging-env-managed.example.md`
- `staging/staging-post-deploy-verification.md`
- `staging/staging-mode-switching-guide.md`
- `staging/staging-database-migration-checklist.md`
- `staging/staging-seed-and-demo-data-checklist.md`
- `staging/staging-mail-smtp-checklist.md`
- `staging/staging-queue-cron-checklist.md`
- `staging/staging-storage-permissions-checklist.md`
- `staging/staging-domain-ssl-checklist.md`
- `staging/staging-first-login-checklist.md`
- `staging/staging-signoff-report-template.md`

Use `SUMMARY.md` as the documentation table of contents, `documentation-url-map.md` as the public URL contract, and `documentation-maintenance-workflow.md` as the rule for keeping docs synchronized with code changes.

## Audiences

- SaaS customers.
- Single-school buyers.
- Managed clients.
- Marketplace buyers.
- Developers.
- Deployment engineers.
- Support teams.
- Sales and onboarding teams.
- Resellers.
- White-label buyers.

## Editing Rules

- Do not document planned systems as available.
- Prefer exact config keys, service names, routes, and feature names.
- Update release notes and changelog files when user-visible behavior changes.
- Keep secrets, SMTP credentials, license keys, payment keys, backups, logs, and database dumps out of documentation examples.

## Support Runbooks

- `support/support-runbooks.md`
- `support/issue-triage.md`
- `support/release-handoff-checklist.md`
- `standalone/product-support-overview.md`

## Commercial Packaging

- `commercial/product-packaging-review.md`
- `commercial/buyer-facing-feature-list.md`
- `commercial/pricing-and-plan-positioning.md`
- `commercial/demo-readiness-checklist.md`
- `commercial/sales-discovery-guide.md`
- `commercial/implementation-handoff-checklist.md`
- `commercial/module-boundaries-and-limitations.md`
- `commercial/standalone-vs-saas-positioning.md`
- `commercial/support-and-maintenance-positioning.md`
- `commercial/release-readiness-commercial-checklist.md`

## Website Add-On

- `website/nextjs-school-website-add-on.md`
- `website/separate-repo-strategy.md`
- `website/website-laravel-link-contract.md`
- `website/admissions-linking-guide.md`
- `website/portal-login-linking-guide.md`
- `website/branding-content-guidance.md`
- `website/security-and-privacy-boundaries.md`
- `website/future-nextjs-repo-checklist.md`
- `website/website-deployment-positioning.md`

## Release Candidate

- `release/release-candidate-qa-report.md`
- `release/v1-release-candidate-checklist.md`
- `release/v1-release-notes.md`
- `release/v1-production-readiness-report.md`
- `release/v1-go-live-checklist.md`
- `release/v1-known-limitations-and-backlog.md`
- `release/v1-deployment-handoff.md`
