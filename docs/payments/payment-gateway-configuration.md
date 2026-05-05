# Payment Gateway Configuration

## Safety Rule

Payment keys must never be hardcoded in PHP, Blade, JavaScript, or documentation examples. Put keys only in environment variables on the target server.

## Current Production Launch Flow

Manual payment is active. Admin users can confirm manual payments and preserve the payment transaction record.

## Configuration Files

Payment settings live in `config/payments.php`. The file reads only from `.env`.

V1.1 adds admin dashboard payment settings with encrypted storage and masked key display for supported gateways and modes.

## Environment Variables

Use `.env.example` as the safe template:

```env
PAYMENT_DEFAULT_GATEWAY=manual

PAYSTACK_ENABLED=false
PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
PAYSTACK_CALLBACK_URL="${APP_URL}/payments/paystack/callback"
PAYSTACK_WEBHOOK_SECRET=
PAYSTACK_BASE_URL=https://api.paystack.co

FLUTTERWAVE_ENABLED=false
FLUTTERWAVE_PUBLIC_KEY=
FLUTTERWAVE_SECRET_KEY=
FLUTTERWAVE_ENCRYPTION_KEY=
FLUTTERWAVE_SECRET_HASH=
FLUTTERWAVE_CALLBACK_URL="${APP_URL}/payments/flutterwave/callback"
FLUTTERWAVE_BASE_URL=https://api.flutterwave.com
```

## Paystack

Paystack public keys are safe only for frontend initialization when the gateway is implemented. Paystack secret keys and webhook secrets must remain server-side.

## Flutterwave

Flutterwave public keys are for frontend initialization when implemented. Secret keys, encryption keys, and secret hashes must remain server-side.

## Test and Live Keys

Use payment provider dashboards to obtain sandbox keys for development and live keys for production. Do not commit either key type. Put live keys only in the production `.env`.

## Webhook Preparation

Before enabling auto payments:

1. Add webhook routes.
2. Verify webhook signatures or hashes.
3. Store gateway references.
4. Make callbacks idempotent.
5. Confirm payment amounts and currency.
6. Update `PaymentTransaction` status only after verification.

## Production Checklist

- `PAYSTACK_ENABLED=false` until Paystack callback/webhook is complete.
- `FLUTTERWAVE_ENABLED=false` until Flutterwave callback/webhook is complete.
- Manual payments still work.
- No secret key appears in Blade or JavaScript.
- `APP_URL` is `https://schools.sanfaani.net`.
