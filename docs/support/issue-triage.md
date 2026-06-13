# Issue Triage

This guide assigns support priority before a module runbook is used. The priority can change as evidence improves.

## Purpose

Use triage to decide response urgency, evidence collection, escalation, and what support must avoid while protecting school data.

## Access

Support staff, local owners, and Sanfaani operators can use this guide. School Admins can provide module evidence, but installer, license, update, backup, and system-health checks belong to the local owner or Super Admin support role.

## P0 Critical

Examples:

- Login completely blocked for all required admins.
- System unavailable.
- Database connection failure.
- Data-loss risk.
- Failed update or preflight with the production school blocked.
- Backup missing before an urgent update.

Response goal: acknowledge immediately during support hours and keep an owner assigned until service is stable or a clear containment plan exists.

First checks:

- Confirm the exact URL, environment, and affected school.
- Confirm whether one user, one role, or everyone is blocked.
- Check hosting control panel status, PHP errors, database availability, disk space, and SSL status.
- Open `/admin/standalone/status` when the owner can access it.
- Run `php artisan route:list`, `php artisan schedule:list`, `php artisan standalone:status`, `php artisan deployment:check-readiness`, and `php artisan security:audit` where shell access is available.

Evidence to collect:

- Time the issue started.
- Last deployment, update preflight, import, restore attempt, hosting change, or `.env` change.
- Sanitized screenshots and exact error text.
- Recent backup verification status.
- Affected user roles and school context.

Escalation condition: escalate to engineering or the deployment owner when there is data-loss risk, database failure, tenant-boundary concern, missing backup before risky work, protected-file change, or unclear update/license/installer state.

What not to do: do not make production changes by guesswork, do not delete data, do not expose secrets, and do not continue update work until backup readiness is verified.

## P1 High

Examples:

- Finance, payment, invoice, or balance issue affecting operations.
- Attendance sync failures.
- CBT exam access issue.
- Result or report generation issue.
- License, installer, or update readiness issue.

Response goal: respond the same business day and keep the school informed until there is a workaround, fix, or escalation.

First checks:

- Confirm module, user role, school, class/session/term, and date range.
- Review the relevant module page and audit/log view.
- Run the focused filter when available, such as `php artisan test --filter=Update`, `php artisan test --filter=Installer`, `php artisan test --filter=License`, `php artisan test --filter=Backup`, or `php artisan test --filter=Reports`.
- For sync issues, check the offline sync monitor and whether records are still browser-local.

Evidence to collect:

- Affected record identifiers, not private secrets.
- Role and permission state.
- Screenshots of status pages with private fields hidden.
- Browser/network state for offline attendance.
- Recent changes to assignments, academic context, license, update package, or backup status.

Escalation condition: escalate when money totals conflict with records, CBT access is blocked near an exam time, sync has repeated conflicts, result/report output may be wrong, or license/update/installer state cannot be explained by diagnostics.

What not to do: do not manually edit balances, mark browser-local pending records as synced, bypass CBT rules, share license keys, or run maintenance actions without a backup.

## P2 Medium

Examples:

- Branding issue.
- Report display mismatch.
- Notification or template issue.
- Live class link or configuration issue.
- LMS material visibility issue.

Response goal: respond within two business days and resolve through configuration, role checks, or planned release work.

First checks:

- Confirm the user role and active school.
- Confirm whether the issue is visibility, content, permissions, file upload, or display.
- Review the relevant support runbook and module doc.
- Run `php artisan route:list` or a focused test filter only when the issue looks route or permission related.

Evidence to collect:

- Screenshots with private data hidden.
- Asset file type/size for branding issues.
- Live-class provider label and meeting URL status, without meeting passwords in chat.
- LMS class, subject, session, and term scope.
- Report filters used.

Escalation condition: escalate when the issue crosses school boundaries, exposes private data, blocks a scheduled class or exam, or requires code changes.

What not to do: do not upload unsafe assets, paste secrets into templates, change another school's data, or promise provider integrations that are deferred.

## P3 Low

Examples:

- Wording or UI help.
- Docs clarification.
- Training request.

Response goal: acknowledge within the normal support queue and route to docs, training, or product backlog.

First checks:

- Confirm the requested wording, screen, workflow, or training topic.
- Link the user to the closest module guide.
- Check whether the request is already documented as planned or deferred.

Evidence to collect:

- Page name or URL path.
- User role and module.
- Exact unclear wording or training goal.

Escalation condition: escalate only when the request reveals a support-risk claim, a misleading product boundary, or a docs gap that could affect sales or handoff.

What not to do: do not treat a training request as approval for production changes and do not overstate future features.

## Privacy Rules

Collect the least evidence needed. Hide student biodata, payment references, raw notification payloads, meeting passwords, backup paths, license keys, API tokens, app keys, and database credentials.

## Related Docs

- [Support Runbooks](support-runbooks.md)
- [Security And Privacy Runbook](security-privacy-runbook.md)
- [Standalone Product Support Overview](../standalone/product-support-overview.md)
