<?php

namespace Tests\Feature\Demo;

use App\Mail\DemoCredentialsMail;
use App\Models\DemoRequest;
use App\Services\Demo\DemoCredentialService;
use App\Services\Demo\DemoEnvironmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DemoCredentialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureDemo();
    }

    public function test_demo_credentials_are_not_stored_in_plain_text(): void
    {
        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());
        $credential = $session->credentials()->firstOrFail();
        $temporaryPassword = $credential->temporary_password_encrypted;
        $storedValue = DB::table('demo_credentials')->where('id', $credential->id)->value('temporary_password_encrypted');

        $this->assertNotSame($temporaryPassword, $storedValue);
        $this->assertStringNotContainsString($temporaryPassword, $storedValue);
    }

    public function test_demo_credentials_can_be_shown_only_once(): void
    {
        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());
        $credential = $session->credentials()->firstOrFail();

        $password = app(DemoCredentialService::class)->revealOnce($credential);

        $this->assertNotNull($password);
        $this->assertNull(app(DemoCredentialService::class)->revealOnce($credential->fresh('demoSession')));
        $this->assertDatabaseHas('demo_activities', [
            'demo_session_id' => $session->id,
            'event' => 'demo.credentials_viewed',
        ]);
    }

    public function test_demo_credential_email_does_not_expose_temporary_passwords(): void
    {
        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());
        $session->load('credentials.user', 'school');
        $credential = $session->credentials->first();
        $temporaryPassword = $credential->temporary_password_encrypted;

        $html = (new DemoCredentialsMail($session))->render();

        $this->assertStringNotContainsString($temporaryPassword, $html);
        $this->assertStringContainsString($credential->email, $html);
        $this->assertStringNotContainsString(base_path(), $html);
    }

    public function test_demo_activity_is_logged_for_generated_credentials(): void
    {
        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());

        $this->assertDatabaseHas('demo_activities', [
            'demo_session_id' => $session->id,
            'event' => 'demo.credentials_generated',
        ]);
    }

    private function demoRequest(): DemoRequest
    {
        return DemoRequest::create([
            'name' => 'Credential Buyer',
            'email' => 'credential-buyer@example.test',
            'school_name' => 'Credential School',
            'status' => DemoRequest::STATUS_REQUESTED,
        ]);
    }

    private function configureDemo(): void
    {
        foreach (['school_admin', 'teacher', 'parent', 'student', 'result_officer', 'accountant'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'demo.enabled' => true,
            'demo.email_enabled' => false,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.demo_system.enabled' => true,
        ]);
    }
}
