# White-Label Readiness

This foundation adds deployment-aware branding settings for platform, school, managed client, and white-label scopes.

Implemented now:

- Branding settings metadata in `branding_settings`.
- Safe logo and favicon upload paths under public branding storage.
- Config defaults for brand name, colors, login copy, email footer text, and report footer text.
- Feature gates for `branding_manager` and `white_label_branding`.
- White-label access checks through feature and license entitlement foundations.
- Admin and school branding forms.

Planned later:

- Full visual theme builder.
- Automated marketplace packaging of branded assets.
- White-label domain provisioning.
- Advanced multi-brand design system controls.

White-label fields should only be enabled when the deployment has the `white_label_branding` feature and a matching license entitlement.
