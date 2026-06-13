# Implementation Handoff Checklist

Use this checklist when handing a standalone installation from sales or implementation to the school, support team, or release owner.

## Server And Environment

- [ ] Hosting path is selected: cPanel, VPS, local server, managed hosting, or approved equivalent.
- [ ] PHP version and required extensions are confirmed.
- [ ] MySQL or MariaDB is ready.
- [ ] Composer and npm expectations are clear for technical buyers.
- [ ] Web root points to Laravel `public` or uses an approved shared-hosting workaround.
- [ ] `storage` and `bootstrap/cache` are writable.
- [ ] Mail/SMTP intent is recorded.
- [ ] Scheduler/cron and queue expectations are documented.

## Install And License

- [ ] Standalone deployment mode is confirmed.
- [ ] Installer access is ready before setup.
- [ ] Owner/admin account is created through the approved flow.
- [ ] School profile is created.
- [ ] Installer lock/final installed state is reviewed.
- [ ] License activation/status is reviewed with masked key output only.
- [ ] License limitations and renewal/support expectations are stated.

## School Configuration

- [ ] School profile setup is complete.
- [ ] Branding setup is complete.
- [ ] Users and roles are configured.
- [ ] Academic sessions, terms, classes, and subjects are configured.
- [ ] Student records are imported or entered according to scope.
- [ ] Admissions setup is reviewed.
- [ ] Attendance setup is reviewed.
- [ ] Finance setup is reviewed where included.
- [ ] LMS/CBT setup is reviewed where included.
- [ ] Live class setup is reviewed where included.
- [ ] Communication templates and logs are reviewed.
- [ ] Reports Center is reviewed.

## Operations Handoff

- [ ] Backup schedule or manual backup owner is named.
- [ ] Backup verification process is explained.
- [ ] Restore plan is documented as careful support-led work with verified backups.
- [ ] Guided update review process is explained.
- [ ] Update preflight owner is named.
- [ ] System health page is reviewed.
- [ ] Release notes handoff is complete.
- [ ] Support runbooks are shared.
- [ ] Known limitations are listed.
- [ ] Training session or walkthrough is complete.

## Handoff Evidence

- [ ] Sanitized screenshots or notes are stored in the support handoff.
- [ ] No raw `.env`, passwords, license keys, app keys, provider tokens, SQL dumps, or backups are placed in ordinary tickets.
- [ ] Support contact and escalation path are confirmed.
- [ ] Business owner has approved package tier, support scope, and any custom work.

## Related Docs

- [Support And Maintenance Positioning](support-and-maintenance-positioning.md)
- [Release Readiness Commercial Checklist](release-readiness-commercial-checklist.md)
- [Standalone Installer User Guide](../installation/standalone-installer-user-guide.md)
- [Release Handoff Checklist](../support/release-handoff-checklist.md)
