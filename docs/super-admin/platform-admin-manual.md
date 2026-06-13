# Platform Admin Manual

Super Admin users operate the platform across schools where global access is intentional.

## Current Responsibilities

- Manage schools.
- Manage subscriptions and feature overrides.
- Review lead requests and CRM workflows.
- Review demo sessions.
- Review marketing pipeline and sales tasks.
- Review platform support, audit, security, and system status pages.
- Use deployment behavior and feature flags to control visibility.
- In standalone mode, review installer readiness, activate or validate the local license, inspect entitlement/module visibility, and use support-safe diagnostics without exposing secrets.

## Deployment Modes

In SaaS mode, Super Admin represents the platform operator.

In single-school mode, Super Admin should behave as local owner/admin, not marketplace platform operator.

The local owner can open the standalone status page and license status page to verify installer completion, app key presence, queue/cache/session/mail readiness, backup/update readiness, local license status, redacted key display, and entitlement visibility. These pages must not reveal app keys, database passwords, mail credentials, license keys, raw `.env` values, sync tokens, or private backup/server paths.

In managed mode, Super Admin supports Sanfaani-operated client deployments.

## Boundaries

Global visibility is intentional only for platform workflows. School operational data must still respect tenant boundaries where the workflow is school-scoped.

Installer/license hardening does not add SaaS billing, payment gateway enforcement, online activation server, automatic remote deactivation, destructive reinstall/reset tools, or a customer billing portal.
