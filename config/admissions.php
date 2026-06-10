<?php

return [
    'enabled' => (bool) env('SANFAANI_ADMISSIONS_ENABLED', true),
    'public_enabled' => (bool) env('SANFAANI_PUBLIC_ADMISSIONS_ENABLED', true),
    'embed_enabled' => (bool) env('SANFAANI_ADMISSION_EMBED_ENABLED', true),
    'api_enabled' => (bool) env('SANFAANI_ADMISSION_API_ENABLED', false),
    'require_captcha' => (bool) env('SANFAANI_ADMISSION_REQUIRE_CAPTCHA', false),
    'honeypot_field' => env('SANFAANI_ADMISSION_HONEYPOT_FIELD', 'admission_website'),
    'form_timestamp_field' => env('SANFAANI_ADMISSION_FORM_TIMESTAMP_FIELD', 'admission_started_at'),
    'minimum_submission_seconds' => (int) env('SANFAANI_ADMISSION_MINIMUM_SUBMISSION_SECONDS', 3),
    'allow_document_uploads' => (bool) env('SANFAANI_ADMISSION_ALLOW_DOCUMENT_UPLOADS', true),
    'max_upload_mb' => (int) env('SANFAANI_ADMISSION_MAX_UPLOAD_MB', 5),
    'tracking_enabled' => (bool) env('SANFAANI_ADMISSION_TRACKING_ENABLED', true),
    'guardian_tracking_fallback_enabled' => (bool) env('SANFAANI_ADMISSION_GUARDIAN_TRACKING_FALLBACK_ENABLED', false),
    'guardian_tracking_requires_date_of_birth' => (bool) env('SANFAANI_ADMISSION_GUARDIAN_TRACKING_REQUIRES_DOB', true),
    'payments_enabled' => (bool) env('SANFAANI_ADMISSION_PAYMENTS_ENABLED', false),
    'manual_payment_enabled' => (bool) env('SANFAANI_ADMISSION_MANUAL_PAYMENT_ENABLED', true),
    'document_disk' => env('SANFAANI_ADMISSION_DOCUMENT_DISK', 'local'),
    'private_document_disks' => ['local'],
    'allowed_document_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
    'allowed_document_types' => [
        'birth_certificate',
        'passport_photo',
        'previous_result',
        'transfer_certificate',
        'supporting_document',
    ],
    'embed_allowed_domains' => array_values(array_filter(array_map(
        fn ($domain) => trim((string) $domain),
        explode(',', (string) env('SANFAANI_ADMISSION_EMBED_ALLOWED_DOMAINS', ''))
    ))),
    'malware_scanning' => [
        'enabled' => false,
        'provider' => env('SANFAANI_ADMISSION_MALWARE_SCANNER'),
    ],
    'submission_throttle' => env('SANFAANI_ADMISSION_SUBMISSION_THROTTLE', '5,1'),
    'tracking_throttle' => env('SANFAANI_ADMISSION_TRACKING_THROTTLE', '10,1'),
    'api_throttle' => env('SANFAANI_ADMISSION_API_THROTTLE', '10,1'),
];
