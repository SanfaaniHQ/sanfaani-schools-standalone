# Future Upgrade Logic

## Assessment Results

Assessment/test results should use the existing result type direction while keeping term results stable. Add dedicated views only when the workflow differs meaningfully.

## CBT

CBT should be a separate module with question banks, assessments, attempts, scoring, and publication into the result architecture where appropriate.

## PDF Result

PDF generation can build from the print-friendly result view. Move to queued generation when schools or result volume grow.

## QR Verification

The system already exposes verification codes and URLs. QR image generation can later render the verification URL as an image on result slips.

## Paystack and Flutterwave

Gateway work should read from `config/payments.php`. Never hardcode public or secret keys. Add callbacks, webhook verification, idempotency checks, and gateway references before enabling live payments.

## Parent Direct Payment

Parent direct payment should create a payment transaction, redirect to a gateway, validate webhook/callback status, then grant result access according to school policy.

## School-Paid Access

School-paid access should be controlled by result access policies and billing agreements. Parents should not need PINs when a school-paid policy is active.

## Mobile App

Use API routes with token authentication and school scoping. Keep student admission numbers separate from staff logins.

## SMS

SMS should be event-driven and queued. Use it for publication alerts, payment confirmations, and card distribution only after consent and cost rules are defined.

## Biometric Attendance

Biometric attendance should be a separate attendance module with device imports and manual correction workflows.

## VPS Migration

Move from Namecheap shared hosting to VPS when queue workers, payment webhooks, PDF generation, heavy uploads, or performance requirements need process control.
