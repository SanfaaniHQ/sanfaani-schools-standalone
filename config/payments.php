<?php

return [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'manual'),

    'paystack' => [
        'enabled' => env('PAYSTACK_ENABLED', false),
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'callback_url' => env('PAYSTACK_CALLBACK_URL'),
        'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
    ],

    'flutterwave' => [
        'enabled' => env('FLUTTERWAVE_ENABLED', false),
        'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
        'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
        'secret_hash' => env('FLUTTERWAVE_SECRET_HASH'),
        'callback_url' => env('FLUTTERWAVE_CALLBACK_URL'),
        'base_url' => env('FLUTTERWAVE_BASE_URL', 'https://api.flutterwave.com'),
    ],
];
