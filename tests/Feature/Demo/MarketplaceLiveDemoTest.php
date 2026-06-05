<?php

namespace Tests\Feature\Demo;

use App\Models\DemoCredential;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MarketplaceLiveDemoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'parent', 'student', 'result_officer', 'accountant'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'features.features.demo_system.enabled' => true,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'demo.marketplace.enabled' => false,
            'demo.marketplace.auto_login' => false,
            'demo.marketplace.safe_mode' => true,
            'demo.marketplace.reset_hours' => 24,
        ]);
    }

    public function test_marketplace_demo_page_is_hidden_when_disabled(): void
    {
        $this->get(route('demo.live'))->assertNotFound();
    }

    public function test_marketplace_demo_page_renders_when_enabled(): void
    {
        config(['demo.marketplace.enabled' => true]);

        $this->get(route('demo.live'))
            ->assertOk()
            ->assertSee('Explore Sanfaani Schools Live Demo')
            ->assertSee('Preview the school management workflows');
    }

    public function test_marketplace_demo_page_shows_core_public_credentials(): void
    {
        config(['demo.marketplace.enabled' => true]);

        $this->get(route('demo.live'))
            ->assertOk()
            ->assertSee('School Admin')
            ->assertSee('schooladmin@demo.sanfaani.net')
            ->assertSee('Teacher')
            ->assertSee('teacher@demo.sanfaani.net')
            ->assertSee('Result Officer')
            ->assertSee('resultofficer@demo.sanfaani.net')
            ->assertSee('password');
    }

    public function test_marketplace_demo_page_explains_reset_safety_and_purchase_guidance(): void
    {
        config(['demo.marketplace.enabled' => true]);

        $this->get(route('demo.live'))
            ->assertOk()
            ->assertSee('Demo data resets regularly')
            ->assertSee('Destructive actions are disabled')
            ->assertSee('Buy standalone package')
            ->assertSee('Done-for-you installation')
            ->assertSee('Contact Sanfaani');
    }

    public function test_demo_seed_command_exists_and_creates_demo_foundation(): void
    {
        $this->artisan('demo:seed-marketplace')
            ->expectsOutput('Marketplace live demo seeded.')
            ->assertSuccessful();

        $this->assertDatabaseHas('schools', [
            'slug' => 'sanfaani-marketplace-demo',
            'subscription_status' => 'demo',
        ]);

        foreach ([
            'schooladmin@demo.sanfaani.net',
            'teacher@demo.sanfaani.net',
            'resultofficer@demo.sanfaani.net',
            'accountant@demo.sanfaani.net',
        ] as $email) {
            $this->assertDatabaseHas('users', ['email' => $email]);
        }

        $this->assertSame(4, DemoCredential::count());
    }

    public function test_demo_seed_command_is_idempotent(): void
    {
        $this->artisan('demo:seed-marketplace')->assertSuccessful();
        $this->artisan('demo:seed-marketplace')->assertSuccessful();

        $this->assertSame(1, School::where('slug', 'sanfaani-marketplace-demo')->count());
        $this->assertSame(1, User::where('email', 'schooladmin@demo.sanfaani.net')->count());
        $this->assertSame(1, User::where('email', 'teacher@demo.sanfaani.net')->count());
        $this->assertSame(1, User::where('email', 'resultofficer@demo.sanfaani.net')->count());
        $this->assertSame(1, User::where('email', 'accountant@demo.sanfaani.net')->count());
        $this->assertSame(4, DemoCredential::count());
    }

    public function test_demo_login_helper_only_logs_in_known_demo_users_when_enabled(): void
    {
        config([
            'demo.marketplace.enabled' => true,
            'demo.marketplace.auto_login' => true,
        ]);

        $this->artisan('demo:seed-marketplace')->assertSuccessful();

        $this->post(route('demo.live.login', 'teacher'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs(User::where('email', 'teacher@demo.sanfaani.net')->first());
    }

    public function test_demo_login_helper_does_not_accept_arbitrary_users(): void
    {
        config([
            'demo.marketplace.enabled' => true,
            'demo.marketplace.auto_login' => true,
        ]);

        User::factory()->create(['email' => 'real-buyer@example.test']);

        $this->post(route('demo.live.login', 'real-buyer'))->assertNotFound();
        $this->assertGuest();
    }

    public function test_docs_exist_and_do_not_advertise_parent_or_student_portals_as_complete(): void
    {
        $docs = [
            'docs/demo/marketplace-live-demo.md',
            'docs/demo/demo-credentials-and-roles.md',
            'docs/demo/demo-sandbox-safety.md',
            'docs/marketplace/live-demo-sales-flow.md',
            'docs/support/demo-support-playbook.md',
        ];

        foreach ($docs as $doc) {
            $this->assertFileExists(base_path($doc));
            $content = strtolower(file_get_contents(base_path($doc)));

            $this->assertStringNotContainsString('parent portal is complete', $content);
            $this->assertStringNotContainsString('student portal is complete', $content);
            $this->assertStringNotContainsString('parent and student portals are complete', $content);
        }
    }
}
