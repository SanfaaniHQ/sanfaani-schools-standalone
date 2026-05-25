<?php

namespace Tests\Feature\Demo;

use App\Mail\DemoCredentialsMail;
use App\Models\DemoRequest;
use App\Models\DemoSession;
use App\Models\LeadRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DemoRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureDemo();
    }

    public function test_demo_request_page_renders_when_demo_system_is_enabled(): void
    {
        $this->get(route('landing.demo'))
            ->assertOk()
            ->assertSee('Explore a guided school demo')
            ->assertSee('Request demo access');
    }

    public function test_demo_request_page_is_blocked_when_demo_system_is_disabled(): void
    {
        config(['features.features.demo_system.enabled' => false]);

        $this->get(route('landing.demo'))->assertNotFound();
    }

    public function test_public_demo_request_creates_demo_request_session_and_activity(): void
    {
        Mail::fake();

        $this->post(route('demo.request.store'), [
            'name' => 'Buyer User',
            'email' => 'buyer@example.test',
            'phone' => '08000000000',
            'school_name' => 'Buyer School',
            'role_interest' => 'school_admin',
            'message' => 'Show me result publishing.',
        ])->assertRedirect(route('demo.thank-you'));

        $this->assertDatabaseHas('demo_requests', [
            'email' => 'buyer@example.test',
            'status' => DemoRequest::STATUS_ENVIRONMENT_CREATED,
        ]);
        $this->assertDatabaseHas('demo_sessions', [
            'status' => DemoSession::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseHas('demo_activities', [
            'event' => 'demo.environment_created',
        ]);
        Mail::assertSent(DemoCredentialsMail::class);
    }

    public function test_demo_request_creates_or_updates_existing_lead_request(): void
    {
        Mail::fake();

        LeadRequest::create([
            'type' => 'demo',
            'name' => 'Old Name',
            'email' => 'lead@example.test',
            'phone' => '07000000000',
            'status' => LeadRequest::STATUS_NEW,
        ]);

        $this->post(route('demo.request.store'), [
            'name' => 'Updated Buyer',
            'email' => 'lead@example.test',
            'school_name' => 'Updated School',
            'role_interest' => 'teacher',
        ])->assertRedirect(route('demo.thank-you'));

        $this->assertSame(1, LeadRequest::where('type', 'demo')->where('email', 'lead@example.test')->count());
        $this->assertDatabaseHas('lead_requests', [
            'email' => 'lead@example.test',
            'name' => 'Updated Buyer',
            'status' => LeadRequest::STATUS_DEMO_SCHEDULED,
        ]);
    }

    private function configureDemo(): void
    {
        foreach (array_keys(config('demo.roles', [])) as $role) {
            Role::findOrCreate($role === 'super_admin' ? 'school_admin' : $role);
        }

        config([
            'demo.enabled' => true,
            'demo.email_enabled' => true,
            'demo.max_active_sessions' => 25,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.demo_system.enabled' => true,
        ]);
    }
}
