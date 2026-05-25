<?php

namespace Tests\Feature\Security;

use App\Jobs\Marketing\SendMarketingEmailJob;
use App\Mail\DemoCredentialsMail;
use App\Mail\Marketing\LeadFollowUpMail;
use App\Models\DemoRequest;
use App\Models\LeadRequest;
use App\Models\License;
use App\Models\School;
use App\Models\User;
use App\Services\Demo\DemoEnvironmentService;
use App\Services\Licensing\LicenseKeyHasher;
use App\Services\Marketing\UnsubscribeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmailSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('super_admin');

        config([
            'marketing.enabled' => true,
            'marketing.email_enabled' => true,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.marketing_automation.enabled' => true,
        ]);
    }

    public function test_marketing_emails_include_unsubscribe_footer_when_required(): void
    {
        $lead = $this->lead('footer@example.test');

        $html = (new LeadFollowUpMail($lead))->render();

        $this->assertStringContainsString('unsubscribe', strtolower($html));
        $this->assertStringContainsString('/unsubscribe/', $html);
    }

    public function test_marketing_emails_are_blocked_for_unsubscribed_contacts(): void
    {
        Mail::fake();
        $lead = $this->lead('blocked@example.test');
        app(UnsubscribeService::class)->record($lead->email);

        (new SendMarketingEmailJob($lead->id))->handle(app(UnsubscribeService::class));

        Mail::assertNothingSent();
    }

    public function test_demo_credentials_email_does_not_expose_internal_paths(): void
    {
        foreach (['school_admin', 'teacher', 'parent', 'student', 'result_officer', 'accountant'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'demo.enabled' => true,
            'demo.email_enabled' => false,
            'features.features.demo_system.enabled' => true,
        ]);

        $session = app(DemoEnvironmentService::class)->createEnvironment(DemoRequest::create([
            'name' => 'Safe Demo',
            'email' => 'safe-demo@example.test',
            'school_name' => 'Safe Demo School',
            'status' => DemoRequest::STATUS_REQUESTED,
        ]));
        $session->load('credentials.user', 'school');

        $html = (new DemoCredentialsMail($session))->render();

        $this->assertStringNotContainsString(base_path(), $html);
        $this->assertStringNotContainsString(storage_path(), $html);
        $this->assertStringNotContainsString('.env', $html);
    }

    public function test_license_status_ui_does_not_expose_raw_license_keys(): void
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'features.features.license_activation.enabled' => true,
        ]);

        $school = School::create([
            'name' => 'Licensed School',
            'slug' => 'licensed-school',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $rawKey = 'RAW-LICENSE-KEY-123';

        License::create([
            'school_id' => $school->id,
            'license_key_hash' => app(LicenseKeyHasher::class)->hash($rawKey),
            'license_type' => 'annual',
            'status' => 'active',
            'issued_to_name' => 'Licensed School',
            'issued_to_email' => 'license@example.test',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addYear(),
            'features' => [],
            'entitlements' => [],
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.license.index'))
            ->assertOk()
            ->assertDontSee($rawKey)
            ->assertSee('Raw keys are never shown');
    }

    private function lead(string $email): LeadRequest
    {
        return LeadRequest::create([
            'type' => 'demo',
            'name' => 'Security Lead',
            'email' => $email,
            'status' => LeadRequest::STATUS_NEW,
        ]);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
