<?php

namespace Tests\Feature\Standalone;

use App\Models\BrandingSetting;
use App\Models\MailSetting;
use App\Models\School;
use App\Models\SchoolNotificationTemplate;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\MailSettingService;
use App\Support\Notifications\SchoolNotificationTemplateRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LiveClientReadinessTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'standalone.product_edition' => 'standalone',
            'standalone.installer_enabled' => true,
            'standalone.installed' => true,
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.installed' => true,
            'features.features.branding_manager.enabled' => true,
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '',
            'mail.mailers.smtp.port' => 587,
            'mail.from.address' => 'portal@example.test',
            'mail.from.name' => 'Sanfaani Schools',
        ]);

        $this->school = School::create([
            'name' => 'Live Client Academy',
            'slug' => 'live-client-academy',
            'email' => 'school@example.test',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->superAdmin = User::factory()->create(['school_id' => $this->school->id]);
        $this->superAdmin->assignRole('super_admin');
        $this->superAdmin->assignRole('school_admin');

        UserSchoolRole::create([
            'user_id' => $this->superAdmin->id,
            'school_id' => $this->school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);
    }

    public function test_local_branding_and_smtp_pages_are_live_pages(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.local-branding.edit'))
            ->assertOk()
            ->assertSee('Brand Your Portal')
            ->assertSee('Brand your school portal with your logo, colours, and display name.')
            ->assertDontSee('Requires setup')
            ->assertDontSee('not implemented');

        $this->get(route('admin.local-mail-settings.edit'))
            ->assertOk()
            ->assertSee('Email Delivery')
            ->assertSee('Use the outgoing SMTP details from your hosting email account.')
            ->assertSee('Platform fallback is not configured.')
            ->assertDontSee('Requires setup')
            ->assertDontSee('not implemented');
    }

    public function test_school_smtp_settings_save_encrypted_password_and_empty_password_preserves_existing_secret(): void
    {
        $service = app(MailSettingService::class);

        $setting = $service->updateForSchool($this->school, [
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'mail.school.example',
            'port' => 465,
            'username' => 'mailer@school.example',
            'password' => 'first-secret',
            'encryption' => 'ssl',
            'from_address' => 'mailer@school.example',
            'from_name' => 'Faz College',
        ]);

        $this->assertNotSame('first-secret', $setting->getRawOriginal('password'));
        $this->assertSame('first-secret', $setting->password);

        $service->updateForSchool($this->school, [
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'mail.school.example',
            'port' => 465,
            'username' => 'mailer@school.example',
            'password' => '',
            'encryption' => 'ssl',
            'from_address' => 'mailer@school.example',
            'from_name' => 'Faz College',
        ]);

        $this->assertSame('first-secret', $service->current($this->school->id)->password);
    }

    public function test_smtp_context_maps_from_name_and_blocks_empty_platform_fallback_host(): void
    {
        $service = app(MailSettingService::class);
        $setting = $service->updateForSchool($this->school, [
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'mail.school.example',
            'port' => 465,
            'username' => 'mailer@school.example',
            'password' => 'mail-secret',
            'encryption' => 'ssl',
            'from_address' => 'mailer@school.example',
            'from_name' => 'Faz College',
        ]);

        $service->withMailSettingContext($setting, function (): void {
            $this->assertSame('mailer@school.example', config('mail.from.address'));
            $this->assertSame('Faz College', config('mail.from.name'));
            $this->assertSame('mail.school.example', config('mail.mailers.school_smtp.host'));
            $this->assertSame('smtps', config('mail.mailers.school_smtp.scheme'));
        });

        $failingService = new class extends MailSettingService
        {
            public function withMailSettingContext(MailSetting $setting, callable $callback): mixed
            {
                throw new RuntimeException('primary smtp failed');
            }

            public function withPlatformMailContext(callable $callback): mixed
            {
                throw new RuntimeException('platform fallback should not run when it is not configured');
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('primary smtp failed');

        $failingService->sendSchoolTestUsingData($this->school, [
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'mail.school.example',
            'port' => 465,
            'username' => 'mailer@school.example',
            'password' => 'mail-secret',
            'encryption' => 'ssl',
            'from_address' => 'mailer@school.example',
            'from_name' => 'Faz College',
        ], 'admin@example.test');
    }

    public function test_unsaved_school_smtp_controller_test_reports_acceptance_without_fallback(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.local-mail-settings.test'), [
                'is_enabled' => '1',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'school@example.test',
                'password' => 'app-password',
                'encryption' => 'tls',
                'from_address' => 'school@example.test',
                'from_name' => 'Live Client Academy',
                'reply_to_email' => 'reply@example.test',
                'timeout' => 10,
                'test_email' => 'admin@example.test',
            ]);

        $response->assertSessionHas('success', fn (string $message) => str_contains($message, 'School SMTP accepted'));
        $response->assertSessionMissing('error');
        $this->assertNull(MailSetting::where('school_id', $this->school->id)->value('host'));
        $this->assertSame('temporary', data_get(
            MailSetting::where('school_id', $this->school->id)->firstOrFail()->metadata,
            'last_test.configuration'
        ));
    }

    public function test_school_smtp_controller_failure_is_safe_and_never_claims_fallback_success(): void
    {
        Log::spy();
        $failingService = new class extends MailSettingService
        {
            public function sendSchoolTestUsingData(School $school, array $data, string $recipient, ?MailSetting $existing = null): array
            {
                throw new RuntimeException('535 authentication failed app-password-should-not-leak');
            }
        };
        $this->app->instance(MailSettingService::class, $failingService);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.local-mail-settings.test'), [
                'is_enabled' => '1',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'school@example.test',
                'password' => 'app-password-should-not-leak',
                'encryption' => 'tls',
                'from_address' => 'school@example.test',
                'from_name' => 'Live Client Academy',
                'timeout' => 10,
                'test_email' => 'admin@example.test',
            ]);

        $response->assertSessionHas('error', fn (string $message) => str_contains($message, 'SMTP authentication failed'));
        $response->assertSessionMissing('success');
        $this->assertStringNotContainsString('app-password-should-not-leak', (string) session('error'));
        $this->assertStringNotContainsString('fallback sent', (string) session('error'));
        Log::shouldHaveReceived('warning')->withArgs(function (string $message, array $context): bool {
            return $message === 'Local school SMTP test failed.'
                && ! str_contains(json_encode($context), 'app-password-should-not-leak');
        })->once();
    }

    public function test_separate_log_fallback_test_does_not_claim_external_delivery(): void
    {
        config(['mail.default' => 'log']);
        $this->app->instance(MailSettingService::class, new MailSettingService);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.local-mail-settings.test-fallback'), [
                'test_email' => 'admin@example.test',
            ])
            ->assertSessionHas('success', 'Fallback is configured to log messages only; no external email was delivered.');
    }

    public function test_local_branding_saves_school_branding(): void
    {
        $this->actingAs($this->superAdmin)
            ->patch(route('admin.local-branding.update'), [
                'brand_name' => 'Faz College',
                'primary_color' => '#047857',
                'secondary_color' => '#111827',
                'accent_color' => '#0ea5e9',
                'login_heading' => 'Welcome to Faz College',
                'login_subheading' => 'Learning for excellence',
                'dashboard_heading' => 'Faz College Dashboard',
                'email_footer_text' => 'Faz College',
                'report_footer_text' => 'Faz College',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('branding_settings', [
            'school_id' => $this->school->id,
            'scope' => BrandingSetting::SCOPE_SCHOOL,
            'brand_name' => 'Faz College',
            'primary_color' => '#047857',
        ]);
    }

    public function test_profile_image_upload_validates_and_stores_avatar(): void
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin)
            ->patch(route('profile.update'), [
                'name' => $this->superAdmin->name,
                'email' => $this->superAdmin->email,
                'avatar' => UploadedFile::fake()->create('avatar.jpg', 10, 'image/jpeg'),
            ])
            ->assertRedirect(route('profile.edit'));

        $avatarPath = $this->superAdmin->fresh()->avatar_path;

        $this->assertNotNull($avatarPath);
        Storage::disk('public')->assertExists($avatarPath);
    }

    public function test_local_dashboard_creates_additional_school_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.local-admins.store'), [
                'name' => 'Second Admin',
                'email' => 'second-admin@example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.local-admins.index'));

        $admin = User::where('email', 'second-admin@example.test')->firstOrFail();

        $this->assertTrue($admin->hasRole('school_admin'));
        $this->assertDatabaseHas('user_school_roles', [
            'user_id' => $admin->id,
            'school_id' => $this->school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);
    }

    public function test_template_registry_and_channel_dropdown_are_available(): void
    {
        $registry = app(SchoolNotificationTemplateRegistry::class);

        $this->assertContains('admission_application_received', $registry->keys());
        $this->assertArrayHasKey(SchoolNotificationTemplate::CHANNEL_EMAIL, $registry->channels());
        $this->assertArrayHasKey(SchoolNotificationTemplate::CHANNEL_DATABASE, $registry->channels());

        $this->actingAs($this->superAdmin);
        session([
            'active_school_id' => $this->school->id,
            'active_role_context' => 'school_admin',
        ]);

        $this->get(route('school.communications.templates.create'))
            ->assertOk()
            ->assertSee('Admission Application Received')
            ->assertSee('Email')
            ->assertSee('In-app notification');
    }

    public function test_template_channel_validation_rejects_unsupported_channel(): void
    {
        $this->actingAs($this->superAdmin);
        session([
            'active_school_id' => $this->school->id,
            'active_role_context' => 'school_admin',
        ]);

        $this->post(route('school.communications.templates.store'), [
            'template_key' => 'general_notification',
            'title' => 'General Notice',
            'subject' => 'Notice',
            'body' => 'Hello {{school_name}}',
            'channel' => 'telegram',
            'audience_type' => 'school_admin',
            'is_active' => '1',
        ])->assertSessionHasErrors('channel');
    }
}
