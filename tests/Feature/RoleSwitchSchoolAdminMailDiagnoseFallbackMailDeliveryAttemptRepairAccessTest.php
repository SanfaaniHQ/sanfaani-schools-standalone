<?php

namespace Tests\Feature;

use App\Models\MailDeliveryAttempt;
use App\Models\School;
use App\Services\MailDeliveryAttemptService;
use App\Services\MailSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RoleSwitchSchoolAdminMailDiagnoseFallbackMailDeliveryAttemptRepairAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_focused_behaviors_are_registered_and_safe(): void
    {
        $workspaceRoute = app('router')->getRoutes()->getByName('workspace.store');
        $providerRoute = app('router')->getRoutes()->getByName('school.mail-settings.providers.test');
        $chainRoute = app('router')->getRoutes()->getByName('school.mail-settings.test-chain');

        $this->assertNotNull($workspaceRoute);
        $this->assertContains('POST', $workspaceRoute->methods());
        $this->assertNotNull($providerRoute);
        $this->assertContains('POST', $providerRoute->methods());
        $this->assertNotNull($chainRoute);
        $this->assertContains('POST', $chainRoute->methods());
        $this->assertArrayHasKey('standalone:mail-diagnose', Artisan::all());
        $this->assertArrayHasKey('standalone:repair-access', Artisan::all());
        $this->assertFalse(app(MailSettingService::class)->platformMailerStatus()['external_delivery']);

        $school = School::create([
            'name' => 'Focused Filter Academy',
            'slug' => 'focused-filter-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        app(MailDeliveryAttemptService::class)->record([
            'school_id' => $school->id,
            'transport' => 'array',
            'status' => 'fallback_non_delivery',
            'fallback_used' => true,
            'external_delivery_attempted' => false,
        ]);

        $attempt = MailDeliveryAttempt::sole();
        $this->assertSame('fallback_non_delivery', $attempt->status);
        $this->assertFalse($attempt->external_delivery_attempted);
    }
}
