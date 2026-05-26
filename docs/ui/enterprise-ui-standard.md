# Enterprise UI Standard

This standard keeps Sanfaani Schools visually consistent without redesigning the product or changing workflows. It applies to SaaS, standalone, managed, white-label, marketplace, demo, admin, school, teacher, result officer, accountant, and support surfaces.

## Principles

- Preserve existing routes, permissions, feature flags, deployment behavior, and branding behavior.
- Build with Blade components, Tailwind utility classes, and the existing Vite pipeline.
- Prefer `x-ui.*` components for repeated page structure, cards, alerts, badges, table shells, forms, and settings panels.
- Keep pages light, readable, and operational. Avoid marketing-style dashboard layouts inside admin tools.
- Use resolved branding tokens through CSS variables and validated hex color values only.
- Keep layouts responsive by default with stacked mobile actions and horizontally scrollable tables.

## Standard Surfaces

- Page headers use `x-ui.page-header`.
- Dashboard metrics use `x-ui.stat-card`.
- Repeated content groups use `x-ui.panel`.
- Warnings and notices use `x-ui.alert`.
- Status labels use `x-ui.badge`.
- Empty lists use `x-ui.empty-state`.
- Primary and secondary actions use `x-ui.action-button`.
- Data tables use `x-ui.table-card`.
- Forms use `x-ui.form-section`.
- Settings summaries use `x-ui.settings-card`.

## Change Control

Do not convert every Blade file in one pass. Update high-traffic dashboards and operational pages first, then migrate older pages only when they are actively touched for product work.
