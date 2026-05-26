# Form Guidelines

Forms should be predictable across admin, school, installer, branding, licensing, onboarding, and support surfaces.

## Structure

- Use `x-ui.form-section` to group related fields.
- Keep labels visible and associated with inputs.
- Keep help text close to the field it explains.
- Keep submit actions at the end of the form and use `x-ui.action-button`.

## Validation

- Continue using existing Laravel validation.
- Display field errors through existing input error components.
- Do not change input names, methods, routes, or hidden fields during UI polish.

## Branding Fields

- Color fields must accept validated hex values.
- Do not inline arbitrary user-controlled CSS as a replacement for tokenized color controls.
- Keep white-label controls gated by existing feature and entitlement logic.
