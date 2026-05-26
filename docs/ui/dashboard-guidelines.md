# Dashboard Guidelines

Dashboards should make operational state scannable without changing role behavior.

## Metrics

- Use `x-ui.stat-card` for all top-level counts.
- Keep labels short and plain.
- Put supporting detail in `meta`, not in the value.
- Use tones consistently: success for healthy, warning for attention, danger for failures, info for neutral operational queues.

## Sections

- Use `x-ui.panel` for grouped operational content.
- Use `x-ui.page-header` for dashboard title and deployment or role context.
- Keep CTAs visible but restrained.
- Preserve all feature, deployment, role, and school authorization gates.

## Empty States

Use `x-ui.empty-state` for empty queues, no records, and first-use states. Avoid plain empty table rows unless the table component contains the empty state.
