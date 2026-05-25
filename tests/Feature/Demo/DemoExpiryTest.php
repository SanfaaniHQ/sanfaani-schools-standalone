<?php

namespace Tests\Feature\Demo;

use App\Jobs\Demo\ExpireDemoSessionJob;
use App\Models\DemoRequest;
use App\Models\DemoSession;
use App\Services\Demo\DemoCredentialService;
use App\Services\Demo\DemoEnvironmentService;
use App\Services\Demo\DemoExpiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DemoExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureDemo();
    }

    public function test_demo_session_expires_after_configured_duration(): void
    {
        config(['demo.default_duration_days' => 1]);

        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());

        $this->assertTrue($session->expires_at->isSameDay(now()->addDay()));

        $session->forceFill(['expires_at' => now()->subMinute()])->save();

        $this->assertSame(1, app(DemoExpiryService::class)->expireDueSessions());
        $this->assertSame(DemoSession::STATUS_EXPIRED, $session->fresh()->status);
    }

    public function test_expired_demo_session_blocks_credential_use_where_possible(): void
    {
        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());
        $credential = $session->credentials()->with('user', 'demoSession')->firstOrFail();
        $password = app(DemoCredentialService::class)->revealOnce($credential);

        $this->assertTrue(Hash::check($password, $credential->user->password));

        app(DemoExpiryService::class)->expire($session);

        $credential = $credential->fresh('user');

        $this->assertSame('expired', $credential->status);
        $this->assertNull($credential->temporary_password_encrypted);
        $this->assertFalse(Hash::check($password, $credential->user->password));
    }

    public function test_demo_expiry_job_expires_due_session(): void
    {
        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());

        ExpireDemoSessionJob::dispatchSync($session);

        $this->assertSame(DemoSession::STATUS_EXPIRED, $session->fresh()->status);
        $this->assertDatabaseHas('demo_activities', [
            'demo_session_id' => $session->id,
            'event' => 'demo.expired',
        ]);
    }

    public function test_demo_expiry_command_expires_due_sessions(): void
    {
        $session = app(DemoEnvironmentService::class)->createEnvironment($this->demoRequest());
        $session->forceFill(['expires_at' => now()->subMinute()])->save();

        $this->artisan('demo:expire-sessions')
            ->expectsOutput('Expired 1 demo session(s).')
            ->assertSuccessful();

        $this->assertSame(DemoSession::STATUS_EXPIRED, $session->fresh()->status);
    }

    private function demoRequest(): DemoRequest
    {
        return DemoRequest::create([
            'name' => 'Expiry Buyer',
            'email' => 'expiry-buyer@example.test',
            'school_name' => 'Expiry School',
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
