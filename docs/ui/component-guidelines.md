# Component Guidelines

## Component Rules

- Components must be Blade-only and Tailwind-compatible.
- Components must not require JavaScript unless the workflow already requires it.
- Props are escaped with Blade output by default.
- Slots may contain developer-authored HTML, but user-provided text inside slots must be printed with `{{ }}`.
- Components should use token classes such as `bg-bg-secondary`, `text-text-primary`, `border-border-subtle`, and `text-brand-primary`.
- Components should be useful in both admin and school layouts.

## Component Inventory

- `x-ui.page-header`: title, eyebrow, description, badge, and action slot.
- `x-ui.stat-card`: metric label, value, optional meta, and tone.
- `x-ui.panel`: general-purpose surface with optional title and description.
- `x-ui.alert`: success, info, warning, and danger messages with semantic roles.
- `x-ui.badge`: status and tone pills.
- `x-ui.empty-state`: no-data messaging and optional action.
- `x-ui.action-button`: anchor or button with standard variants.
- `x-ui.table-card`: responsive table container with header/action slots.
- `x-ui.form-section`: grouped form content.
- `x-ui.settings-card`: settings summary and controls.

## Migration Guidance

When updating a page, replace one repeated pattern at a time. Keep controller data, form names, route names, authorization checks, and feature gates unchanged.
