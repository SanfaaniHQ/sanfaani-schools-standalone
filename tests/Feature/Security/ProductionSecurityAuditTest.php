<?php

namespace Tests\Feature\Security;

use App\Services\Security\EmailSafetyService;
use App\Services\Security\ProductionReadinessService;
use App\Services\Security\TokenSafetyService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductionSecurityAuditTest extends TestCase
{
    public function test_security_config_loads(): void
    {
        $this->assertTrue(config('security.diagnostics_enabled'));
        $this->assertTrue(config('security.email_safety_enabled'));
        $this->assertSame(60, config('security.token_default_expiry_minutes'));
    }

    public function test_security_diagnostics_feature_is_configured(): void
    {
        $feature = config('features.features.security_diagnostics');

        $this->assertIsArray($feature);
        $this->assertTrue($feature['enabled']);
        $this->assertContains('saas', $feature['deployment_modes']);
        $this->assertContains('single_school', $feature['deployment_modes']);
        $this->assertContains('managed', $feature['deployment_modes']);
    }

    public function test_security_audit_command_exists(): void
    {
        $this->assertArrayHasKey('security:audit', Artisan::all());
    }

    public function test_security_audit_command_is_read_only_and_exits_successfully(): void
    {
        $envPath = base_path('.env');
        $before = File::exists($envPath) ? hash_file('sha256', $envPath) : null;

        $this->artisan('security:audit')
            ->expectsOutputToContain('Security audit complete')
            ->expectsOutputToContain('No files were modified')
            ->expectsOutputToContain('No emails were sent')
            ->assertExitCode(0);

        $after = File::exists($envPath) ? hash_file('sha256', $envPath) : null;

        $this->assertSame($before, $after);
    }

    public function test_app_debug_true_is_flagged_for_production(): void
    {
        config([
            'app.env' => 'production',
            'app.debug' => true,
        ]);

        $checks = collect(app(ProductionReadinessService::class)->checks());
        $check = $checks->firstWhere('key', 'app_debug');

        $this->assertNotNull($check);
        $this->assertSame('fail', $check['status']);
    }

    public function test_mail_configuration_warnings_are_reported_safely(): void
    {
        config(['mail.from.address' => 'not-an-email']);

        $checks = collect(app(EmailSafetyService::class)->checks());
        $check = $checks->firstWhere('key', 'mail_from');

        $this->assertNotNull($check);
        $this->assertSame('warning', $check['status']);
        $this->assertStringNotContainsString('MAIL_PASSWORD', $check['message']);
    }

    public function test_production_error_safe_mode_and_token_expiry_are_reported(): void
    {
        $production = collect(app(ProductionReadinessService::class)->checks())->firstWhere('key', 'production_error_safe_mode');
        $token = collect(app(TokenSafetyService::class)->checks())->firstWhere('key', 'token_expiry');

        $this->assertSame('pass', $production['status']);
        $this->assertSame('pass', $token['status']);
        $this->assertStringContainsString('60 minutes', $token['message']);
    }

    public function test_security_docs_exist(): void
    {
        foreach ((array) config('security.required_docs') as $path) {
            $this->assertFileExists(base_path($path), "Missing security doc [{$path}].");
        }
    }
}
