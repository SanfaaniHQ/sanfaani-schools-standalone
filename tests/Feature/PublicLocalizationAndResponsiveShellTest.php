<?php

namespace Tests\Feature;

use App\Jobs\DispatchMarketingCampaign;
use App\Jobs\ProcessBulkCommunicationBatch;
use App\Jobs\RunMarketingAutomations;
use App\Jobs\SendMarketingCampaignEmail;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PublicLocalizationAndResponsiveShellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('school_admin');
    }

    public function test_public_home_switches_french_and_arabic_without_stale_hero_copy(): void
    {
        $this->get(route('landing.home', ['lang' => 'fr']))
            ->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('Les resultats ont besoin de structure', false)
            ->assertSee('Demander une demo', false)
            ->assertDontSee('Result work needs structure');

        $this->get(route('landing.home', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('عمل النتائج يحتاج إلى نظام', false)
            ->assertSee('طلب عرض توضيحي', false)
            ->assertDontSee('Result work needs structure');
    }

    public function test_public_pricing_and_demo_forms_are_locale_backed(): void
    {
        $this->get(route('landing.pricing', ['lang' => 'fr']))
            ->assertOk()
            ->assertSee('Tarifs flexibles pour petites ecoles', false)
            ->assertSee('Trimestre', false)
            ->assertDontSee('Flexible pricing for small and growing schools');

        $this->get(route('landing.demo', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('نوع المدرسة', false)
            ->assertSee('جار الإرسال', false)
            ->assertDontSee('School Type');
    }

    public function test_authenticated_shell_contains_mobile_rtl_notification_and_zoom_responsive_contracts(): void
    {
        $school = School::create([
            'name' => 'Responsive Shell Academy',
            'slug' => 'responsive-shell-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('school_admin');

        UserSchoolRole::create([
            'user_id' => $admin->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->withSession([
                'active_school_id' => $school->id,
                'active_role_context' => 'school_admin',
            ])
            ->get(route('school.dashboard', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('sidebarOpen', false)
            ->assertSee('x-on:resize.window', false)
            ->assertSee('rtl:translate-x-full', false)
            ->assertSee('data-notification-root', false)
            ->assertSee('w-[min(20rem,calc(100vw-2rem))]', false)
            ->assertSee('overflow-x-clip', false);
    }

    public function test_frontend_css_contains_mobile_table_modal_and_cbt_overflow_guards(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('@media (max-width: 640px)', $css);
        $this->assertStringContainsString('max-height: calc(100dvh - 2rem)', $css);
        $this->assertStringContainsString('.cbt-question-body table', $css);
        $this->assertStringContainsString('overflow-x: auto', $css);
        $this->assertStringContainsString('[dir="rtl"] .education-ops-shell .enterprise-table th', $css);
    }

    public function test_queue_jobs_expose_retry_safe_backoff_contracts(): void
    {
        $bulk = new ProcessBulkCommunicationBatch(1);
        $dispatch = new DispatchMarketingCampaign(1);
        $automation = new RunMarketingAutomations;
        $email = new SendMarketingCampaignEmail(1);

        $this->assertSame([30, 120, 300], $bulk->backoff());
        $this->assertSame([60, 300], $dispatch->backoff());
        $this->assertSame([120, 600], $automation->backoff());
        $this->assertSame([60, 300, 900], $email->backoff());
        $this->assertInstanceOf(DateTimeInterface::class, $bulk->retryUntil());
        $this->assertInstanceOf(DateTimeInterface::class, $dispatch->retryUntil());
        $this->assertInstanceOf(DateTimeInterface::class, $automation->retryUntil());
        $this->assertInstanceOf(DateTimeInterface::class, $email->retryUntil());
    }
}
