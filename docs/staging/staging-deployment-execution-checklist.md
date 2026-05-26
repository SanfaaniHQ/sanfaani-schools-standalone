# Staging Deployment Execution Checklist

Use this checklist when executing a real staging deployment for Sanfaani Schools. It is an operator checklist, not deployment automation.

## Scope

- Target repository: `SanfaaniHQ/sanfaani-schools`.
- Target branch or release candidate:
- Target staging URL:
- Target mode: `saas`, `single_school`, or `managed`.
- Execution owner:
- Verification owner:
- Signoff owner:

## Safety Boundaries

- Do not modify `public/build.zip`.
- Do not modify `database/migrations/2026_05_01_173857_create_result_publications_table.php`.
- Do not commit `.env`, real SMTP credentials, license keys, payment keys, backups, logs, database dumps, or private storage.
- Do not generate release ZIPs during staging execution.
- Do not run destructive deployment automation.
- Do not describe staging as complete until the actual validation results are recorded.
- Full billing automation remains planned.
- Real update application remains planned.
- Automated restore remains planned.

## Pre-Execution Checks

- [ ] Confirm the deployment commit is reviewed and approved for staging.
- [ ] Confirm protected files are not staged.
- [ ] Confirm a staging database backup exists if the environment already contains data.
- [ ] Confirm the selected mode has a matching staging env example.
- [ ] Confirm staging SMTP sends only to approved staging recipients.
- [ ] Confirm cron and queue strategy match the host.
- [ ] Confirm storage and `bootstrap/cache` are writable.
- [ ] Confirm the web root points to Laravel `public`, or a reviewed shared-hosting workaround is documented.
- [ ] Confirm `public/build` is the asset target, not `public/build.zip`.

## Execution Checks

- [ ] Run the server command sequence in `docs/staging/staging-server-command-sequence.md`.
- [ ] Apply only reviewed `.env` values from the correct mode example.
- [ ] Run migrations only after backup approval.
- [ ] Rebuild or deploy reviewed frontend assets.
- [ ] Clear and rebuild Laravel caches only after `.env` values are final.
- [ ] Run readiness commands in the documented order.
- [ ] Record warnings separately from failures.

## Post-Deploy Checks

- [ ] Complete `docs/staging/staging-post-deploy-verification.md`.
- [ ] Complete the relevant mode checklist.
- [ ] Complete domain, SSL, storage, queue, cron, SMTP, and first-login checks.
- [ ] Record actual command results in the signoff report.
- [ ] Record known limitations and accepted risks.
- [ ] Confirm no real secrets are present in docs, logs, screenshots, or tickets.

## Hold Conditions

Stop and escalate if any of these occur:

- A validation command exits with failure.
- A protected file is staged.
- Staging `.env` is missing or unsafe.
- The staging site exposes `.env`, logs, backups, dumps, `vendor`, `node_modules`, or private storage.
- The selected mode shows routes that should be hidden for that mode.
- SMTP sends to real users.
- Backup, update, billing, or restore copy claims capabilities that remain planned.

## Completion Record

- Deployment commit:
- Started at:
- Completed at:
- Command sequence record:
- Post-deploy verification record:
- Signoff report:
- Follow-up owner:
