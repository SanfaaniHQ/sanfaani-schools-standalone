<?php

return [
    'resource_disk' => env('SANFAANI_LMS_RESOURCE_DISK', 'local'),
    'max_upload_mb' => (int) env('SANFAANI_LMS_MAX_UPLOAD_MB', 10),
    'malware_scanning' => [
        'enabled' => false,
        'provider' => env('SANFAANI_LMS_MALWARE_SCANNER'),
    ],
];
