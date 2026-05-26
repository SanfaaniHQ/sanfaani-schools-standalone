# Table Guidelines

Tables must remain readable on small screens and safe for operational data.

## Standard Wrapper

- Use `x-ui.table-card` around data tables.
- Use `enterprise-table` for table styling.
- Keep the wrapper horizontally scrollable with `overflow-x-auto`.
- Keep table headers short and scannable.

## Row Actions

- Keep destructive actions behind existing forms, CSRF tokens, methods, and confirmations.
- Use text links for low-risk review actions and `x-ui.action-button` for primary actions.
- Do not hide permissions or feature gates inside visual-only logic.

## Empty Tables

Use `x-ui.empty-state` in empty table rows and set a correct `colspan`.
