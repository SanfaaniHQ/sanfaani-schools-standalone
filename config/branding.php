<?php

return [
    'enabled' => (bool) env('SANFAANI_BRANDING_ENABLED', true),
    'brand_mode' => env('SANFAANI_BRAND_MODE', 'default'),
    'white_label_enabled' => (bool) env('SANFAANI_WHITE_LABEL_ENABLED', false),

    'defaults' => [
        'brand_name' => env('SANFAANI_DEFAULT_BRAND_NAME', 'Sanfaani Schools'),
        'primary_color' => env('SANFAANI_DEFAULT_PRIMARY_COLOR', '#0f766e'),
        'secondary_color' => env('SANFAANI_DEFAULT_SECONDARY_COLOR', '#0f172a'),
        'accent_color' => '#14b8a6',
        'email_footer_text' => 'Powered by Sanfaani Schools.',
        'login_heading' => 'Welcome back',
        'login_subheading' => 'Sign in to continue to your school workspace.',
        'dashboard_heading' => 'School Operations Command Center',
        'report_footer_text' => 'Generated securely by Sanfaani Schools.',
    ],

    'storage' => [
        'disk' => 'public',
        'platform_path' => 'branding/platform',
        'managed_path' => 'branding/managed',
        'school_path' => 'branding/schools',
    ],

    'uploads' => [
        'max_logo_kb' => (int) env('SANFAANI_BRANDING_MAX_LOGO_KB', 512),
        'max_favicon_kb' => (int) env('SANFAANI_BRANDING_MAX_FAVICON_KB', 128),
        'allowed_extensions' => ['png', 'jpg', 'jpeg', 'webp', 'ico'],
        'allowed_mimetypes' => [
            'image/png',
            'image/jpeg',
            'image/webp',
            'image/x-icon',
            'image/vnd.microsoft.icon',
        ],
    ],

    'scopes' => [
        'platform',
        'school',
        'managed_client',
        'white_label',
    ],

    'labels' => [
        'saas' => 'Platform Branding',
        'single_school' => 'Guided Branding',
        'managed' => 'Managed Branding',
    ],
];
