# Commercial Architecture

Sanfaani Schools is one Laravel codebase that can operate as a SaaS platform, a single-school licensed installation, or a managed client deployment.

## Current Foundations

- `config/sanfaani.php` stores deployment, license, brand, update, demo, and installed state config.
- `config/features.php` defines global feature availability and feature metadata.
- `config/deployment_modes.php` maps mode-specific route groups, dashboard widgets, and settings sections.
- `DeploymentModeService` answers deployment and license mode questions.
- `FeatureAccessService` evaluates feature access using config, deployment mode, license mode, school context, overrides, subscriptions, and entitlements.
- `DeploymentBehaviorService` decides route group, dashboard widget, and settings visibility.
- Installer, licensing, demo, onboarding, and marketing foundations exist as separate services and tables.

## Commercial Rule

Future commercial behavior must ask:

1. What deployment mode is active?
2. What license mode is active?
3. Which school or tenant is active?
4. Is the feature enabled globally?
5. Is the feature enabled for this school?
6. Is the user authorized?

No client, school, buyer, marketplace, or domain-specific behavior should be hard-coded.

## Not Implemented Yet

- Full billing/payment workflow.
- Update manager.
- Backup manager.
- Marketplace packaging automation.
- Full white-label storage and reseller operations.
