# Release Handoff Checklist

Use this checklist before handing a build to the user, support team, buyer, managed client, or release owner. It is documentation and support focused; it does not replace module QA.

## Release Identity

- [ ] Current branch is confirmed.
- [ ] Latest commit is recorded.
- [ ] Working tree is clean or intended docs-only changes are listed.
- [ ] Release notes or changelog expectations are clear.
- [ ] Known issues are listed.
- [ ] Demo and sales limitations are listed.

## Guardrails

- [ ] Protected files are clean: `public/build.zip` and `database/migrations/2026_05_01_173857_create_result_publications_table.php`.
- [ ] `.env` and `.env.local` are untouched.
- [ ] Dependency vulnerabilities are noted as a separate later dependency/security audit task.
- [ ] No documentation claims full offline system support.
- [ ] No documentation claims auto-download, online update server, destructive auto-apply, SaaS billing/payment gateway automation, remote license server, real live-class provider API automation, or Next.js website integration as complete.

## Validation

- [ ] `php artisan route:list` passed.
- [ ] `php artisan test --filter=Standalone` passed.
- [ ] `php artisan test --filter=Installer` passed.
- [ ] `php artisan test --filter=License` passed.
- [ ] `php artisan test --filter=Update` passed.
- [ ] `php artisan test --filter=Backup` passed.
- [ ] `php artisan test --filter=Reports` passed.
- [ ] `php artisan test --filter=Dashboard` passed.
- [ ] `php artisan test --filter=Health` passed.
- [ ] `npm run build` passed when frontend assets are expected to build.
- [ ] `git diff --check` passed.
- [ ] Full `php artisan test` passed when non-doc code changed.

## Operations Readiness

- [ ] Backup is verified before update, migration, import, restore, or risky maintenance.
- [ ] Restore plan is manual, reviewed, and tested outside production where needed.
- [ ] Installer status is reviewed and locked after setup.
- [ ] License status is reviewed with masked key output only.
- [ ] Update preflight is reviewed and recorded.
- [ ] System health is reviewed.
- [ ] Scheduler and queue expectations are reviewed.
- [ ] SMTP/mail posture is reviewed.
- [ ] Storage permissions and public storage links are reviewed.

## Support Readiness

- [ ] Support runbooks are updated.
- [ ] Issue triage priority is clear for known issues.
- [ ] Escalation owner is named.
- [ ] Support contact path is named.
- [ ] Privacy reminders are included in handoff notes.
- [ ] Buyer, school, or managed-client limitations are stated in plain language.

## Product Boundaries To Repeat In Handoff

- Laravel is the source of truth.
- Standalone is for one private school installation.
- Offline support is attendance-focused browser capture/sync, not full offline portal support.
- Live classes use manual links and provider abstraction metadata, not real provider API automation.
- Updates are guided review/preflight only.
- Installer/license are local readiness foundations, not SaaS billing or remote license enforcement.
- Branding is internal product branding, not a public Next.js school website.
- Reports are operational summaries, not a custom BI builder.

## Related Docs

- [Support Runbooks](support-runbooks.md)
- [Issue Triage](issue-triage.md)
- [Release Readiness Checklist](../release/release-readiness-checklist.md)
- [Final Preflight Checklist](../release/final-preflight-checklist.md)
- [Production Launch Readiness](../roadmap/production-launch-readiness.md)
