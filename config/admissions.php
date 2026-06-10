<?php

return [
    'enabled' => (bool) env('SANFAANI_ADMISSIONS_ENABLED', true),
    'public_enabled' => (bool) env('SANFAANI_PUBLIC_ADMISSIONS_ENABLED', true),
    'embed_enabled' => (bool) env('SANFAANI_ADMISSION_EMBED_ENABLED', true),
    'api_enabled' => (bool) env('SANFAANI_ADMISSION_API_ENABLED', false),
    'require_captcha' => (bool) env('SANFAANI_ADMISSION_REQUIRE_CAPTCHA', false),
    'allow_document_uploads' => (bool) env('SANFAANI_ADMISSION_ALLOW_DOCUMENT_UPLOADS', true),
    'max_upload_mb' => (int) env('SANFAANI_ADMISSION_MAX_UPLOAD_MB', 5),
    'tracking_enabled' => (bool) env('SANFAANI_ADMISSION_TRACKING_ENABLED', true),
    'payments_enabled' => (bool) env('SANFAANI_ADMISSION_PAYMENTS_ENABLED', false),
    'manual_payment_enabled' => (bool) env('SANFAANI_ADMISSION_MANUAL_PAYMENT_ENABLED', true),
    'document_disk' => env('SANFAANI_ADMISSION_DOCUMENT_DISK', 'local'),
    'allowed_document_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
    'submission_throttle' => env('SANFAANI_ADMISSION_SUBMISSION_THROTTLE', '5,1'),
    'tracking_throttle' => env('SANFAANI_ADMISSION_TRACKING_THROTTLE', '10,1'),
    'api_throttle' => env('SANFAANI_ADMISSION_API_THROTTLE', '10,1'),
];
