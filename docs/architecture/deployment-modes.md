# Deployment Modes

Sanfaani Schools currently supports three deployment modes through config and services.

## SaaS Mode

Config value: `SANFAANI_DEPLOYMENT_MODE=saas`

SaaS mode supports the central Sanfaani-hosted multi-school platform. It exposes platform school management, subscription visibility, feature overrides, demo sessions, marketing tools, and central settings where feature flags allow.

## Single-School Mode

Config value: `SANFAANI_DEPLOYMENT_MODE=single_school`

Single-school mode supports a licensed local installation for one school. It exposes local school settings, license visibility, installer access before installation, and update placeholders when feature flags allow.

It must not expose central SaaS school onboarding or SaaS billing unless explicitly configured later.

## Managed Mode

Config value: `SANFAANI_DEPLOYMENT_MODE=managed`

Managed mode supports Sanfaani-operated client deployments. It exposes managed support, managed update visibility, managed backup placeholders, and optional white-label visibility through config and feature flags.

## Enforcement Points

- `DeploymentModeService`
- `DeploymentBehaviorService`
- `feature` middleware
- `deployment.behavior` middleware
- Dashboard/sidebar visibility checks

Unknown deployment modes fail closed with clear errors.
