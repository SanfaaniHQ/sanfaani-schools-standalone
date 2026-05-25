<?php

namespace Tests\Feature\Marketing;

use App\Events\OnboardingStepCompleted;
use App\Models\LeadRequest;
use App\Models\OnboardingChecklist;
use App\Models\OnboardingStep;
use App\Models\School;
use App\Models\User;
use App\Models\UserOnboardingProgress;
use App\Services\Demo\DemoRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LeadScoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('super_admin');
        $this->configureMarketing();
    }

    public function test_lead_scoring_increases_for_demo_request(): void
    {
        app(DemoRequestService::class)->create([
            'name' => 'Demo Buyer',
            'email' => 'demo-buyer@example.test',
            'school_name' => 'Demo Buyer School',
            'source' => 'public_demo',
        ]);

        $this->assertDatabaseHas('marketing_lead_scores', [
            'segment' => 'demo',
            'score' => 25,
        ]);
    }

    public function test_lead_scoring_increases_for_onboarding_completion(): void
    {
        $school = $this->school();
        $user = User::factory()->create(['school_id' => $school->id]);
        $lead = LeadRequest::create([
            'type' => 'demo',
            'name' => 'Converted Lead',
            'email' => 'converted@example.test',
            'school_name' => $school->name,
            'status' => LeadRequest::STATUS_TRIAL_STARTED,
            'converted_school_id' => $school->id,
            'converted_at' => now(),
        ]);

        $checklist = OnboardingChecklist::create([
            'key' => 'marketing_school_admin',
            'name' => 'Marketing school admin',
            'role_name' => 'school_admin',
            'is_active' => true,
        ]);
        $step = OnboardingStep::create([
            'onboarding_checklist_id' => $checklist->id,
            'key' => 'first_step',
            'title' => 'First step',
            'required' => true,
        ])->load('checklist');
        $progress = UserOnboardingProgress::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'onboarding_checklist_id' => $checklist->id,
            'onboarding_step_id' => $step->id,
            'status' => UserOnboardingProgress::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        event(new OnboardingStepCompleted($user, $step, $progress, $school));

        $this->assertDatabaseHas('marketing_lead_activities', [
            'lead_request_id' => $lead->id,
            'school_id' => $school->id,
            'event' => 'onboarding.step_completed',
        ]);
        $this->assertDatabaseHas('marketing_lead_scores', [
            'lead_request_id' => $lead->id,
            'segment' => 'demo',
        ]);
        $this->assertGreaterThan(25, $lead->marketingLeadScores()->firstOrFail()->score);
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
            'name' => 'Marketing School '.School::count(),
            'slug' => 'marketing-school-'.School::count(),
            'status' => 'active',
            'subscription_status' => 'trial',
        ]);
    }
}
