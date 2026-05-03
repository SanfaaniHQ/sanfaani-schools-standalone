# UI/UX Guide

## Principles

- Keep pages clean, direct, and professional.
- Prefer white backgrounds, readable typography, clear spacing, and soft shadows.
- Avoid clutter, unnecessary gradients, cartoons, and unfinished placeholder language.
- Use parent-friendly copy on public result checker pages.

## Colors

Use neutral surfaces, dark text, gray borders, and restrained status colors. Avoid making the product feel dominated by one decorative color.

## Layout

Public pages should be mobile-first with clear CTAs: Request Demo, Check Result, Contact Sales, and Login to Portal. Dashboard pages should prioritize scanning and repeated operations.

## Cards

Use rounded cards with subtle borders and soft shadows for repeated items, summaries, and forms. Do not nest cards unnecessarily.

## Buttons

Use clear action labels. Submit buttons should support loading states through `data-loading-text`. Dangerous actions should use `data-confirm`.

## Tables

Tables should use readable spacing, aligned actions, and status badges. Empty states should explain what will appear after setup.

## Status Badges

Use badges for paid, pending, generated, revoked, published, unpublished, active, archived, valid, and invalid states.

## Public Pages

The landing page should show the actual product value immediately: result management, publishing control, scratch cards, and public result checking.

## Dashboard Pages

Dashboard pages should group modules by workflow: setup, students, results, publishing, scratch cards, payments, and settings.

## Student Promotion Pages

Promotion screens should make the source session/class and target session/class obvious. Preview counts should show promoted, repeated, graduated, transferred, withdrawn, and skipped students before the final confirmation. Use clear warnings that published results and historical records are preserved.

## Report Card Settings Pages

Report-card settings should be grouped by Branding, Header Layout, Student Information, Result Table, Signatures, Automated Comments, and Preview. Preview should look close to a real report card while using sample data when no result exists.

## Loading and Confirmation

Global JavaScript prevents double submits, disables submit buttons, applies loading text, and respects confirmation prompts.
