<?php

namespace Tests\Feature\Marketing;

use App\Jobs\Marketing\SendMarketingEmailJob;
use App\Mail\Marketing\LeadFollowUpMail;
use App\Models\LeadRequest;
use App\Services\Marketing\UnsubscribeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MarketingComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('super_admin');
        $this->configureMarketing();
    }

    public function test_marketing_email_job_does_not_send_when_marketing_email_disabled(): void
    {
        Mail::fake();
        config(['marketing.email_enabled' => false]);
        $lead = $this->lead('disabled@example.test');

        (new SendMarketingEmailJob($lead->id))->handle(app(UnsubscribeService::class));

        Mail::assertNothingSent();
    }

    public function test_marketing_email_job_does_not_send_to_unsubscribed_email(): void
    {
        Mail::fake();
        $lead = $this->lead('unsubscribed@example.test');
        app(UnsubscribeService::class)->record($lead->email);

        (new SendMarketingEmailJob($lead->id))->handle(app(UnsubscribeService::class));

        Mail::assertNothingSent();
    }

    public function test_marketing_email_job_sends_when_allowed(): void
    {
        Mail::fake();
        $lead = $this->lead('send@example.test');

        (new SendMarketingEmailJob($lead->id))->handle(app(UnsubscribeService::class));

        Mail::assertSent(LeadFollowUpMail::class);
    }

    public function test_unsubscribe_route_records_without_leaking_contact_existence(): void
    {
        $token = app(UnsubscribeService::class)->tokenForEmail('private@example.test');

        $this->get(route('marketing.unsubscribe.public', $token))
            ->assertOk()
            ->assertSee('Marketing preferences updated')
            ->assertDontSee('private@example.test');

        $this->assertDatabaseHas('marketing_unsubscribes', [
            'email' => 'private@example.test',
            'reason' => 'unsubscribed',
        ]);
        $this->assertDatabaseHas('marketing_suppressions', [
            'email' => 'private@example.test',
        ]);
    }

    private function lead(string $email): LeadRequest
    {
        return LeadRequest::create([
            'type' => 'demo',
            'name' => 'Compliance Lead',
            'email' => $email,
            'status' => LeadRequest::STATUS_NEW,
        ]);
    }

    private function configureMarketing(): void
    {
        config([
            'marketing.enabled' => true,
            'marketing.email_enabled' => true,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.marketing_automation.enabled' => true,
        ]);
    }
}
