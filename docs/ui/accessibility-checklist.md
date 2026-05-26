# Accessibility Checklist

Use this checklist before shipping UI changes.

## Contrast

- Body text meets WCAG AA contrast.
- Brand color text is used sparingly and checked against the current background.
- Status badges have readable text in light and dark themes.

## Focus

- Interactive elements have visible focus states.
- Custom controls keep keyboard navigation.
- Page skip link remains available in app layouts.

## Buttons

- Button labels describe the action.
- Loading states do not remove accessible names.
- Mobile touch targets are at least 44px tall.

## Forms

- Every input has a visible label.
- Errors are near the relevant field.
- Required fields are clear from context or validation feedback.

## Tables

- Tables use headers.
- Horizontal overflow is available on mobile.
- Empty states are readable by screen readers.

## Alerts

- Warnings and errors use `role="alert"`.
- Informational and success messages use `role="status"`.
- Alert text does not rely on color alone.
