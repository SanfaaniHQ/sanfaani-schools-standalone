<?php

return [
    'platform_name' => env('SANFAANI_PLATFORM_NAME', 'Sanfaani Schools'),
    'company_name' => env('SANFAANI_COMPANY_NAME', 'Sanfaani Ltd'),
    'support_email' => env('SANFAANI_SUPPORT_EMAIL', 'sanfaanisaas@gmail.com'),
    'support_phone' => env('SANFAANI_SUPPORT_PHONE', '+2349010172138'),
    'support_whatsapp' => env('SANFAANI_SUPPORT_WHATSAPP', '+2349010172138'),
    'address' => env('SANFAANI_ADDRESS'),
    'website_url' => env('SANFAANI_WEBSITE_URL'),
    'official_email' => env('SANFAANI_OFFICIAL_EMAIL', env('SANFAANI_SUPPORT_EMAIL', 'sanfaanisaas@gmail.com')),
    'timezone' => env('SANFAANI_TIMEZONE', env('APP_TIMEZONE', 'UTC')),
    'default_language' => env('SANFAANI_DEFAULT_LANGUAGE', 'en'),
    'supported_languages' => array_filter(array_map('trim', explode(',', env('SANFAANI_SUPPORTED_LANGUAGES', 'en')))),
    'idle_timeout_minutes' => (int) env('SANFAANI_IDLE_TIMEOUT_MINUTES', 30),
];
