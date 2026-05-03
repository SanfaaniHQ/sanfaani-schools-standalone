<?php

return [
    'platform_name' => env('SANFAANI_PLATFORM_NAME', 'Sanfaani Schools'),
    'company_name' => env('SANFAANI_COMPANY_NAME', 'Sanfaani Ltd'),
    'product_url' => env('SANFAANI_PRODUCT_URL', 'https://schools.sanfaani.net'),
    'main_company_url' => env('SANFAANI_MAIN_URL', 'https://sanfaani.net'),
    'support_email' => env('SANFAANI_SUPPORT_EMAIL', 'sanfaanisaas@gmail.com'),
    'sales_email' => env('SANFAANI_SALES_EMAIL', 'sanfaanisaas@gmail.com'),
    'support_phone' => env('SANFAANI_SUPPORT_PHONE', '09010172138'),
    'whatsapp_number' => env('SANFAANI_WHATSAPP_NUMBER', '+2349010172138'),
    'business_address' => env(
        'SANFAANI_BUSINESS_ADDRESS',
        'Kehinde Shafi Junction, Islamic Village, along Whitefield Hotel, Ilorin, Kwara State, Nigeria'
    ),
    'default_country' => env('SANFAANI_DEFAULT_COUNTRY', 'Nigeria'),
    'default_currency' => env('SANFAANI_DEFAULT_CURRENCY', 'NGN'),
    'default_language' => env('SANFAANI_DEFAULT_LANGUAGE', 'en'),
    'supported_languages' => array_filter(array_map(
        'trim',
        explode(',', env('SANFAANI_SUPPORTED_LANGUAGES', 'en,fr,ar'))
    )),
];
