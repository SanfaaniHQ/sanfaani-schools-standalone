<?php

namespace Tests\Feature\Marketing;

use App\Events\LicenseExpiring;
use App\Events\OnboardingChecklistCompleted;
use App\Models\LeadRequest;
use App\Models\License;
use App\Models\OnboardingChecklist;
use App\Models\School;
use App\Models\User;
use App\Services\Demo\DemoRequestService;
use App\Services\Licensing\LicenseKeyHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MarketingAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('super_admin');
        $this->configureMarketing();
    }

    public function test_demo_request_creates_lead_activity_and_follow_up_sales_task(): void
    {
        app(DemoRequestService::class)->create([
            'name' => 'Pipeline Buyer',
            'email' => 'pipeline@example.test',
            'school_name' => 'Pipeline School',
        ]);

        $lead = LeadRequest::where('email', 'pipeline@example.test')->firstOrFail();

        $this->assertDatabaseHas('marketing_lead_activities', [
            'lead_request_id' => $lead->id,
            'event' => 'demo.requested',
        ]);
        $this->assertDatabaseHas('sales_tasks', [
            'lead_request_id' => $lead->id,
            'title' => 'Follow up demo request',
            'status' => 'open',
        ]);
    }

    public function test_onboarding_checklist_completion_creates_conversion_activity(): void
    {
        $school = $this->school();
        $user = User::factory()->create(['school_id' => $school->id]);
        $lead = LeadRequest::create([
            'type' => 'demo',
            'name' => 'Onboarding Lead',
            'email' => 'onboarding@example.test',
            'school_name' => $school->name,
            'status' => LeadRequest::STATUS_TRIAL_STARTED,
            'converted_school_id' => $school->id,
        ]);
        $checklist = OnboardingChecklist::create([
            'key' => 'marketing_checklist',
            'name' => 'Marketing Checklist',
            'role_name' => 'school_admin',
            'is_active' => true,
        ]);

        event(new OnboardingChecklistCompleted($user, $checklist, $school));

        $this->assertDatabaseHas('marketing_lead_activities', [
            'lead_request_id' => $lead->id,
            'school_id' => $school->id,
            'event' => 'onboarding.checklist_completed',
        ]);
    }

    public function test_renewal_reminder_task_can_be_created_for_expiring_license(): void
    {
        $school = $this->school();
        $license = License::create([
            'school_id' => $school->id,
            'license_key_hash' => app(LicenseKeyHasher::class)->hash('RENEWAL-KEY'),
            'license_type' => 'annual',
            'status' => 'active',
            'issued_to_name' => $school->name,
            'expires_at' => now()->addDays(10),
        ]);

        event(new LicenseExpiring($license));

        $this->assertDatabaseHas('sales_tasks', [
            'school_id' => $school->id,
            'title' => 'License renewal reminder',
            'status' => 'open',
        ]);
        $this->assertDatabaseHas('marketing_lead_activities', [
            'school_id' => $school->id,
            'event' => 'license.expiring',
        ]);
    }

    private function configureMarketing(): void
    {
        config([
            'marketing.enabled' => true,
            'marketing.sales_tasks_enabled' => true,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.marketing_automation.enabled' => true,
        ]);
    }

    private function school(): School
    {
        return School::create([
            'name' => 'Automation School '.School::count(),
            'slug' => 'automation-school-'.School::count(),
            'status' => 'active',
            'subscription_status' => 'trial',
        ]);
    }
}
