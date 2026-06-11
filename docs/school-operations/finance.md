# School Finance Operations

Use **Fees & Finance** in the school workspace to set up fees and record school payments.

## Recommended Workflow

1. Open **Fee Items** and create reusable fee heads.
2. Open **Assignments** and attach fees to the relevant class, student, session, and term.
3. Open **Invoices** and generate a bill for one student or an active class.
4. Open an invoice and record each manual payment.
5. Use the invoice or student finance history to review billed, paid, and outstanding amounts.

Class invoice generation is idempotent for the same student, academic context, and assignment set. Repeating the same generation preserves the matching existing invoice.

## Access

School Admins and Accountants can use the module. Teachers, Result Officers, Students, and Parents cannot manage finance. All records are restricted to the active school workspace.

## Payment Safety

- Amounts must be greater than zero.
- Overpayments are rejected.
- Payment references are optional and limited in length.
- Payment notes are stored on the payment record but are not copied into safe audit metadata.
- No public invoice link is created.
- No payment gateway charge is initiated.

## Current Boundary

This module provides online fee setup, student billing, manual payments, balances, and receipt history. Advanced finance reports and audit views are Stage 11 work. Import/export is Stage 12 work. Payment gateway automation, offline fee capture, and parent/student finance portal access are deferred.

See `docs/standalone/fees-accounting-foundation.md` for architecture, permissions, audit behavior, and deferred scope.
