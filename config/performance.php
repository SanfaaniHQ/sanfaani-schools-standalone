<?php

use App\Services\System\DeploymentModeService;

return [
    /*
    |--------------------------------------------------------------------------
    | Shared-Hosting And Performance Readiness Foundation
    |--------------------------------------------------------------------------
    |
    | These settings power read-only diagnostics, guidance, and safe limits for
    | marketplace, shared-hosting, VPS, cloud, managed, and single-school
    | deployments. They do not clear caches, run migrations, prune logs, or
    | optimize production state from a web request.
    |
    */

    'performance_mode' => env('SANFAANI_PERFORMANCE_MODE', 'shared_hosting'),

    'shared_hosting_safe_mode' => (bool) env('SANFAANI_SHARED_HOSTING_SAFE_MODE', true),

    'default_page_size' => (int) env('SANFAANI_DEFAULT_PAGE_SIZE', 25),

    'max_export_rows' => (int) env('SANFAANI_MAX_EXPORT_ROWS', 5000),

    'bulk_operation_chunk_size' => (int) env('SANFAANI_BULK_OPERATION_CHUNK_SIZE', 100),

    'queue_sync_fallback' => (bool) env('SANFAANI_QUEUE_SYNC_FALLBACK', true),

    'diagnostics_enabled' => (bool) env('SANFAANI_PERFORMANCE_DIAGNOSTICS_ENABLED', true),

    'log_retention_days' => (int) env('SANFAANI_LOG_RETENTION_DAYS', 14),

    'feature' => 'performance_diagnostics',

    'cache_recommendations' => [
        'config' => 'Use php artisan config:cache during controlled deployments after environment values are final.',
        'routes' => 'Use php artisan route:cache only after confirming all routes are cache-compatible.',
        'views' => 'Use php artisan view:cache on production deployments and clear it during release rollbacks.',
        'application' => 'Prefer file/database cache on shared hosting unless Redis is explicitly available.',
    ],

    'shared_hosting_limits' => [
        'minimum_memory_mb' => 128,
        'recommended_memory_mb' => 256,
        'minimum_max_execution_seconds' => 30,
        'recommended_max_execution_seconds' => 120,
        'upload_size_warning_mb' => 64,
        'post_size_warning_mb' => 64,
    ],

    'safe_paths' => [
        'storage',
        'bootstrap/cache',
        'public/build',
        'storage/app/public',
    ],

    'excluded_heavy_paths' => [
        'vendor',
        'node_modules',
        'storage/logs',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/app/backups',
        'storage/app/private',
        'public/build.zip',
        '.env',
    ],

    'recommended_indexes' => [
        'student_results' => [
            ['columns' => ['school_id', 'student_id', 'academic_session_id', 'term_id', 'result_type', 'status'], 'reason' => 'Student result workspace, public result lookup, and publishing filters.'],
            ['columns' => ['school_id', 'school_class_id', 'academic_session_id', 'term_id', 'status'], 'reason' => 'Class result publishing and result review lists.'],
        ],
        'scratch_cards' => [
            ['columns' => ['school_id', 'academic_session_id', 'term_id', 'status'], 'reason' => 'Scratch-card result access and card management.'],
            ['columns' => ['serial_number', 'status'], 'reason' => 'Public card lookup and status checks.'],
        ],
        'communication_logs' => [
            ['columns' => ['school_id', 'type', 'created_at'], 'reason' => 'Communication center filtering and exports.'],
            ['columns' => ['status', 'created_at'], 'reason' => 'Retry queues and failure dashboards.'],
        ],
        'support_threads' => [
            ['columns' => ['school_id', 'status', 'last_message_at'], 'reason' => 'School support inbox and escalation queues.'],
            ['columns' => ['assigned_to', 'status'], 'reason' => 'Managed support ownership lists.'],
        ],
        'demo_sessions' => [
            ['columns' => ['status', 'expires_at'], 'reason' => 'Demo expiry command and admin session dashboard.'],
        ],
        'user_onboarding_progress' => [
            ['columns' => ['user_id', 'school_id', 'status'], 'reason' => 'Role-based onboarding progress widgets.'],
        ],
        'marketing_lead_activities' => [
            ['columns' => ['lead_request_id', 'event', 'created_at'], 'reason' => 'Lead timeline and sales automation diagnostics.'],
            ['columns' => ['school_id', 'event', 'created_at'], 'reason' => 'Managed client activity review.'],
        ],
        'update_packages' => [
            ['columns' => ['status', 'channel'], 'reason' => 'Update dashboard and package review.'],
            ['columns' => ['version', 'channel'], 'reason' => 'Package manifest lookup.'],
        ],
        'backups' => [
            ['columns' => ['school_id', 'status', 'completed_at'], 'reason' => 'Pre-update backup readiness and school-specific backup lists.'],
            ['columns' => ['expires_at'], 'reason' => 'Retention pruning.'],
        ],
    ],

    'deployment_route_groups' => [
        DeploymentModeService::MODE_SAAS => 'platform_performance',
        DeploymentModeService::MODE_SINGLE_SCHOOL => 'standalone_performance',
        DeploymentModeService::MODE_MANAGED => 'managed_performance',
    ],

    'labels' => [
        DeploymentModeService::MODE_SAAS => 'Platform Performance',
        DeploymentModeService::MODE_SINGLE_SCHOOL => 'Hosting Health',
        DeploymentModeService::MODE_MANAGED => 'Managed Performance',
    ],
];
