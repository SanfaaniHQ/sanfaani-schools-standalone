<?php

use App\Services\System\DeploymentModeService;

return [
    'diagnostics_enabled' => (bool) env('SANFAANI_SECURITY_DIAGNOSTICS_ENABLED', true),
    'email_safety_enabled' => (bool) env('SANFAANI_EMAIL_SAFETY_ENABLED', true),
    'email_footer_enabled' => (bool) env('SANFAANI_EMAIL_FOOTER_ENABLED', true),
    'email_unsubscribe_required' => (bool) env('SANFAANI_EMAIL_UNSUBSCRIBE_REQUIRED', true),
    'secret_redaction_enabled' => (bool) env('SANFAANI_SECRET_REDACTION_ENABLED', true),
    'production_error_safe_mode' => (bool) env('SANFAANI_PRODUCTION_ERROR_SAFE_MODE', true),
    'token_default_expiry_minutes' => (int) env('SANFAANI_TOKEN_DEFAULT_EXPIRY_MINUTES', 60),
    'feature' => 'security_diagnostics',

    'sensitive_keys' => [
        'password',
        'passwd',
        'pwd',
        'secret',
        'token',
        'api_key',
        'access_key',
        'private_key',
        'license_key',
        'mail_password',
        'smtp_password',
        'db_password',
        'database_url',
        'authorization',
        'credential',
    ],

    'mail_views' => [
        'resources/views/emails',
    ],

    'required_docs' => [
        'docs/security/production-security-hardening.md',
        'docs/security/email-safety-checklist.md',
        'docs/security/logging-and-secret-redaction.md',
        'docs/security/token-and-signed-url-safety.md',
        'docs/security/queue-failure-safety.md',
        'docs/security/production-error-handling.md',
        'docs/security/shared-hosting-security-checklist.md',
    ],

    'deployment_route_groups' => [
        DeploymentModeService::MODE_SAAS => 'platform_security_diagnostics',
        DeploymentModeService::MODE_SINGLE_SCHOOL => 'standalone_security',
        DeploymentModeService::MODE_MANAGED => 'managed_security',
    ],

    'labels' => [
        DeploymentModeService::MODE_SAAS => 'Platform Security',
        DeploymentModeService::MODE_SINGLE_SCHOOL => 'Security Health',
        DeploymentModeService::MODE_MANAGED => 'Managed Security',
    ],
];
