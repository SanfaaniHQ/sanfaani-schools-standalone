<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sanfaani Marketing Pipeline Foundation
    |--------------------------------------------------------------------------
    |
    | These settings gate lead nurturing, sales task creation, conversion
    | activity tracking, and compliance behavior. Provider-specific WhatsApp,
    | billing, update, backup, and marketplace workflows are intentionally not
    | implemented here.
    |
    */

    'enabled' => (bool) env('SANFAANI_MARKETING_AUTOMATION_ENABLED', true),
    'email_enabled' => (bool) env('SANFAANI_MARKETING_EMAIL_ENABLED', true),
    'whatsapp_enabled' => (bool) env('SANFAANI_MARKETING_WHATSAPP_ENABLED', false),
    'sales_tasks_enabled' => (bool) env('SANFAANI_MARKETING_SALES_TASKS_ENABLED', true),
    'unsubscribe_enabled' => (bool) env('SANFAANI_MARKETING_UNSUBSCRIBE_ENABLED', true),
    'default_sequence_days' => (int) env('SANFAANI_MARKETING_DEFAULT_SEQUENCE_DAYS', 14),

    'queues' => [
        'default' => env('SANFAANI_MARKETING_QUEUE', 'marketing'),
    ],

    'scoring' => [
        'demo_request' => 25,
        'trial_started' => 20,
        'onboarding_step_completed' => 8,
        'onboarding_checklist_completed' => 30,
        'license_expiring' => 15,
        'managed_interest' => 20,
        'white_label_interest' => 20,
    ],
];
