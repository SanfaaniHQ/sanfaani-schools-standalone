# Sanfaani Schools Enterprise Design System

## Principles

Sanfaani Schools should feel like an operational command center for African educational institutions. The interface prioritizes clear status, high trust, fast scanning, and low-bandwidth resilience over decoration.

- Every primary screen answers what needs attention, what changed, what failed, and what is pending.
- Navigation is role-aware and route-safe. Users only see modules they can act on.
- Components use semantic HTML, visible focus states, and WCAG 2.2 AA contrast.
- JavaScript stays light and progressive. Alpine.js is used only for local UI state.

## Color Tokens

| Token | Light | Dark | Usage |
| --- | --- | --- | --- |
| Background primary | `#FFFFFF` | `#0F0F0F` | App and public page base |
| Background secondary | `#F8FAFC` | `#18181B` | Panels, cards, navigation |
| Background tertiary | `#F1F5F9` | `#27272A` | Hover states, nested controls |
| Text primary | `#111827` | `#FAFAFA` | Headings and important data |
| Text secondary | `#475569` | `#D4D4D8` | Body copy and labels |
| Text muted | `#94A3B8` | `#71717A` | Metadata only |
| Brand primary | `#047857` | `#10B981` | Primary actions and active states |
| Brand hover | `#065F46` | `#34D399` | Primary action hover |
| Warning | `#B45309` | `#F59E0B` | Pending, returned, missing |
| Danger | `#BE123C` | `#FB7185` | Failed, revoked, destructive |

## Typography

- Font family: Inter for all product surfaces.
- Display scale is fixed, not viewport-scaled: `12, 14, 16, 18, 20, 24, 30, 36`.
- Letter spacing is `0` across product UI. Uppercase operational labels use font weight and size, not tracking.
- Headings inside panels stay compact. Hero-scale type is reserved for public marketing hero only.

## Spacing And Shape

- Base spacing unit: `4px`.
- Standard panel padding: `16px` mobile, `20px-24px` desktop.
- Cards and panels use `8px` radius. Buttons and inputs use `6px-8px` radius.
- Page sections are unframed full-width bands or constrained content. Cards are for repeated items, modals, and framed tools only.

## Component Specs

### App Shell

- Sidebar width: `256px`, fixed desktop, drawer on mobile.
- Topbar is sticky, wraps safely, and uses icon buttons for notifications, theme, profile, and mobile navigation.
- RTL uses logical spacing and start/end placement.

### Navigation

- Super Admin sees platform modules only.
- School Admin sees school operations only.
- Result Officer sees result and student-review tools only, feature-controlled.
- Teacher sees assigned class, subject, result entry, students, communication, and support tools only.

### Tables

- Sticky header, horizontal scroll region with `tabindex="0"`.
- Search, filters, pagination, export, density, and empty/loading states are the standard for full index pages.
- Mobile views may convert rows to cards only when all table data remains visible.

### Forms

- Labels are always visible.
- Required fields use native validation and server-side errors.
- Buttons expose loading, disabled, hover, and focus-visible states.

## Dark And Light Mode

- Light mode is the default because the product brand is white, emerald, and charcoal.
- Dark mode keeps contrast high and avoids blue/purple dominance.
- Tenant colors may influence accents but must not lower contrast or hide destructive states.

## Accessibility Map

- Skip links are present on public and authenticated layouts.
- Modals use `role="dialog"`, `aria-modal="true"`, labelled headings, Escape close, and focus targets.
- Sidebar, topbar, command palette, and language switchers expose labels and `aria-current` where applicable.
- Focus rings use emerald or tenant primary with at least `2px` outline.

## Language And RTL

- Supported languages: English, Arabic, French, Yoruba, Hausa.
- Locale source order: query string, authenticated user preference, session, school/platform default, app fallback.
- Arabic sets `dir="rtl"` and uses logical spacing utilities.
- Language switchers appear on public, login, and authenticated topbar surfaces.
