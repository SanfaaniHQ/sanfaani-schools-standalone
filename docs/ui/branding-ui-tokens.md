# Branding UI Tokens

Branding must work for Sanfaani defaults, school branding, managed clients, and entitled white-label buyers.

## Token Sources

- Default values come from `config/branding.php`.
- Resolved brand values come from the branding service.
- UI defaults and status maps come from `config/ui.php`.
- CSS variables expose only validated color values.

## Color Rules

- Accept only `#RRGGBB` hex values for brand colors.
- Fall back to Sanfaani defaults when a color is missing or invalid.
- Use token classes such as `text-brand-primary`, `bg-brand-primary`, and `border-border-subtle`.
- Do not expose private asset paths in UI output. Render public URLs only after existing asset validation resolves them.

## White Label

White-label controls must remain behind the existing feature and entitlement checks. UI polish must not make locked white-label controls appear available.
