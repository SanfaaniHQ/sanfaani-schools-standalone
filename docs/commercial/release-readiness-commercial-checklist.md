# Release Readiness Commercial Checklist

Use this checklist before publishing sales copy, running a buyer demo, handing over a private installation, or asking the user to commit a commercial-packaging release.

## Product And Repository

- [ ] Product branch is confirmed.
- [ ] Latest release commit is confirmed.
- [ ] Working tree is clean or intended docs-only changes are listed.
- [ ] Protected files are clean.
- [ ] `.env` and `.env.local` are untouched.
- [ ] Tests/build status is known.
- [ ] Dependency vulnerabilities are noted separately and not claimed as fixed.

## Documentation And Sales Assets

- [ ] Commercial packaging docs are updated.
- [ ] Buyer-facing feature list is current.
- [ ] Pricing/package positioning is approved by the business owner.
- [ ] Known limitations are documented.
- [ ] Sales assets do not overclaim full offline, SaaS billing, payment automation, online update server, auto-update, remote license server, real live-class provider API automation, Next.js website integration, custom BI, or dependency vulnerability remediation.
- [ ] Website add-on copy states that the future Next.js website repo is separate and has not been created in this Laravel stage.
- [ ] Support runbooks are updated.
- [ ] Onboarding checklist is ready.
- [ ] Release notes or handoff notes are prepared.

## Demo Readiness

- [ ] Demo uses safe fake data.
- [ ] No real credentials are shared.
- [ ] School branding is configured.
- [ ] Admissions, attendance, finance, LMS/CBT, live class, communication, and reports samples are ready where included.
- [ ] Backup, installer, license, update, and system health statuses are ready to explain.
- [ ] Known limitations are included in the demo talk track.

## Deployment And Handoff

- [ ] Deployment path is defined: cPanel, VPS, local server, managed hosting, or approved equivalent.
- [ ] Support owner is assigned.
- [ ] Implementation owner is assigned.
- [ ] Backup owner is assigned.
- [ ] Update review owner is assigned.
- [ ] Training/handoff date is planned.
- [ ] Support and maintenance scope is approved.

## Business Approval

- [ ] Package tier is selected: Starter School, Growth School, Digital Learning School, or Enterprise Private Installation.
- [ ] Price, legal terms, tax handling, payment method, renewal, and support SLA are approved outside this code stage.
- [ ] Customizations are separately scoped.
- [ ] Hosting/domain/SSL/provider fees are clearly assigned.
- [ ] Website hosting, domain, SSL, content, design, and maintenance scope are clearly assigned when the website add-on is offered.

## Validation Commands

Record the result of the release validation set:

```bash
php artisan route:list
php artisan test --filter=Standalone
php artisan test --filter=Installer
php artisan test --filter=License
php artisan test --filter=Update
php artisan test --filter=Backup
php artisan test --filter=Reports
php artisan test --filter=Dashboard
php artisan test --filter=Health
npm run build
git diff --check
```

Run full `php artisan test` when non-doc code changes.

## Related Docs

- [Product Packaging Review](product-packaging-review.md)
- [Demo Readiness Checklist](demo-readiness-checklist.md)
- [Implementation Handoff Checklist](implementation-handoff-checklist.md)
- [Release Handoff Checklist](../support/release-handoff-checklist.md)
