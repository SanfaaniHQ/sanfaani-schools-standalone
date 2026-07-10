<?php

namespace Tests\Feature\Mail;

use App\Models\MailDeliveryAttempt;
use App\Models\MailSetting;
use App\Models\School;
use App\Models\SchoolMailProviderProfile;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\MailSettingService;
use App\Services\RolePermissionService;
use App\Services\SchoolMailDeliveryOrchestrator;
use App\Services\SchoolMailProviderService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SchoolMailProviderProfileTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('p', 32))]);
        Role::findOrCreate('school_admin', 'web');
        app(RolePermissionService::class)->ensureDefaultRolePermissions();
        $this->school = $this->school('Provider Academy');
    }

    public function test_gmail_and_webmail_are_retained_and_edited_independently(): void
    {
        $service = app(SchoolMailProviderService::class);
        $gmail = $service->save($this->school, $this->profile([
            'name' => 'Sanfaani Gmail',
            'provider_type' => 'gmail',
            'host' => 'smtp.gmail.com',
            'username' => 'gmail@example.test',
            'from_address' => 'gmail@example.test',
            'is_primary' => true,
        ]));
        $webmail = $service->save($this->school, $this->profile([
            'name' => 'Sanfaani Webmail',
            'provider_type' => 'cpanel',
            'host' => 'mail.example.test',
            'username' => 'office@example.test',
            'from_address' => 'office@example.test',
            'port' => 587,
            'encryption' => 'tls',
            'is_primary' => false,
            'priority' => 20,
        ]));

        $service->save($this->school, $this->profile([
            'name' => 'Updated Gmail',
            'provider_type' => 'gmail',
            'host' => 'smtp.gmail.com',
            'username' => 'gmail@example.test',
            'from_address' => 'gmail@example.test',
            'password' => '',
            'is_primary' => true,
        ]), $gmail);

        $this->assertSame(2, $service->forSchool($this->school)->count());
        $this->assertSame('Updated Gmail', $gmail->fresh()->name);
        $this->assertSame('Sanfaani Webmail', $webmail->fresh()->name);
        $this->assertSame('smtp-secret', $gmail->fresh()->password);
        $this->assertNotSame('smtp-secret', $gmail->fresh()->getRawOriginal('password'));
        $this->assertArrayNotHasKey('password', $gmail->fresh()->toArray());
    }

    public function test_only_one_enabled_primary_exists_and_secondary_order_is_deterministic(): void
    {
        $service = app(SchoolMailProviderService::class);
        $first = $service->save($this->school, $this->profile(['name' => 'First', 'priority' => 50]));
        $second = $service->save($this->school, $this->profile(['name' => 'Second', 'priority' => 20, 'is_primary' => true]));
        $third = $service->save($this->school, $this->profile(['name' => 'Third', 'priority' => 10]));

        $this->assertSame(1, SchoolMailProviderProfile::where('school_id', $this->school->id)->where('is_primary', true)->count());
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertSame(['Second', 'Third', 'First'], $service->enabledChain($this->school)->pluck('name')->all());

        $service->makePrimary($this->school, $third);
        $this->assertSame('Third', $service->enabledChain($this->school)->first()->name);
    }

    public function test_mail_delivery_attempt_primary_acceptance_stops_chain_and_records_one_attempt(): void
    {
        $service = app(SchoolMailProviderService::class);
        $service->save($this->school, $this->profile(['name' => 'Gmail', 'is_primary' => true]));
        $service->save($this->school, $this->profile(['name' => 'Webmail', 'host' => 'mail.example.test', 'priority' => 20]));
        $calls = [];

        $result = app(SchoolMailDeliveryOrchestrator::class)->deliver(
            $this->school,
            function ($mailer, $provider) use (&$calls) {
                $calls[] = $provider->name;

                return 'accepted';
            },
            ['recipient' => 'recipient@example.test']
        );

        $this->assertSame(['Gmail'], $calls);
        $this->assertSame('Gmail', $result['provider_name']);
        $this->assertSame('accepted_by_smtp', MailDeliveryAttempt::sole()->status);
    }

    public function test_fallback_primary_failure_invokes_secondary_and_stops_after_acceptance(): void
    {
        $service = app(SchoolMailProviderService::class);
        $service->save($this->school, $this->profile(['name' => 'Gmail', 'is_primary' => true]));
        $service->save($this->school, $this->profile(['name' => 'Webmail', 'host' => 'mail.example.test', 'priority' => 20]));
        $service->save($this->school, $this->profile(['name' => 'Third', 'host' => 'third.example.test', 'priority' => 30]));
        $calls = [];

        $result = app(SchoolMailDeliveryOrchestrator::class)->deliver(
            $this->school,
            function ($mailer, $provider) use (&$calls) {
                $calls[] = $provider->name;

                if ($provider->name === 'Gmail') {
                    throw new RuntimeException('535 authentication failed private-password');
                }

                return 'accepted';
            },
            ['recipient' => 'recipient@example.test', 'message_kind' => 'transactional']
        );

        $this->assertSame(['Gmail', 'Webmail'], $calls);
        $this->assertSame('Webmail', $result['provider_name']);
        $this->assertSame(['authentication_failed', 'accepted_by_smtp'], MailDeliveryAttempt::orderBy('id')->pluck('status')->all());
        $this->assertSame([1, 2], MailDeliveryAttempt::orderBy('id')->pluck('attempt_sequence')->all());
        $this->assertDatabaseMissing('mail_delivery_attempts', ['sanitized_error_message' => 'private-password']);
    }

    public function test_individual_test_uses_only_selected_provider_and_never_fallback(): void
    {
        Mail::fake();
        $service = app(SchoolMailProviderService::class);
        $gmail = $service->save($this->school, $this->profile(['name' => 'Gmail', 'is_primary' => true]));
        $webmail = $service->save($this->school, $this->profile(['name' => 'Webmail', 'host' => 'mail.example.test']));

        $result = app(SchoolMailDeliveryOrchestrator::class)->testProvider(
            $this->school,
            $webmail,
            'recipient@example.test'
        );

        $this->assertSame('Webmail', $result['provider_name']);
        $this->assertSame($webmail->id, MailDeliveryAttempt::sole()->provider_profile_id);
        $this->assertSame('test', MailDeliveryAttempt::sole()->message_kind);
        $this->assertNull($gmail->fresh()->last_test_status);
        $this->assertSame('accepted_by_smtp', $webmail->fresh()->last_test_status);
    }

    public function test_post_acceptance_metadata_failure_never_advances_to_another_provider(): void
    {
        $service = app(SchoolMailProviderService::class);
        $service->save($this->school, $this->profile(['name' => 'Primary', 'is_primary' => true]));
        $service->save($this->school, $this->profile(['name' => 'Secondary', 'host' => 'secondary.example.test']));
        $calls = [];
        SchoolMailProviderProfile::updating(fn () => throw new RuntimeException('metadata storage failed'));

        try {
            $result = app(SchoolMailDeliveryOrchestrator::class)->deliver(
                $this->school,
                function ($mailer, $provider) use (&$calls) {
                    $calls[] = $provider->name;

                    return 'accepted';
                },
                ['recipient' => 'recipient@example.test', 'message_kind' => 'test']
            );
        } finally {
            SchoolMailProviderProfile::flushEventListeners();
        }

        $this->assertSame('Primary', $result['provider_name']);
        $this->assertSame(['Primary'], $calls);
        $this->assertSame('accepted_by_smtp', MailDeliveryAttempt::sole()->status);
    }

    public function test_post_acceptance_attempt_record_failure_never_advances_to_another_provider(): void
    {
        $service = app(SchoolMailProviderService::class);
        $service->save($this->school, $this->profile(['name' => 'Primary', 'is_primary' => true]));
        $service->save($this->school, $this->profile(['name' => 'Secondary', 'host' => 'secondary.example.test']));
        $calls = [];
        MailDeliveryAttempt::creating(fn () => throw new RuntimeException('attempt storage failed'));

        try {
            $result = app(SchoolMailDeliveryOrchestrator::class)->deliver(
                $this->school,
                function ($mailer, $provider) use (&$calls) {
                    $calls[] = $provider->name;

                    return 'accepted';
                },
                ['recipient' => 'recipient@example.test', 'message_kind' => 'transactional']
            );
        } finally {
            MailDeliveryAttempt::flushEventListeners();
        }

        $this->assertSame('Primary', $result['provider_name']);
        $this->assertSame(['Primary'], $calls);
        $this->assertSame(0, MailDeliveryAttempt::count());
    }

    public function test_fallback_array_transport_is_recorded_as_non_delivery_after_all_providers_fail(): void
    {
        $service = app(SchoolMailProviderService::class);
        $service->save($this->school, $this->profile(['name' => 'Gmail', 'is_primary' => true]));
        $service->save($this->school, $this->profile(['name' => 'Webmail', 'host' => 'mail.example.test']));

        try {
            app(MailSettingService::class)->deliverForSchool(
                $this->school,
                fn () => throw new RuntimeException('connection refused'),
                ['recipient' => 'recipient@example.test']
            );
            $this->fail('Expected a non-delivery fallback exception.');
        } catch (\Throwable $exception) {
            $this->assertStringContainsString('non-delivery fallback', $exception->getMessage());
        }

        $this->assertSame(
            ['connection_failed', 'connection_failed', 'fallback_non_delivery'],
            MailDeliveryAttempt::orderBy('id')->pluck('status')->all()
        );
        $this->assertFalse((bool) MailDeliveryAttempt::latest('id')->first()->external_delivery_attempted);
    }

    public function test_decryption_failure_is_safe_and_does_not_crash_profile_listing(): void
    {
        $service = app(SchoolMailProviderService::class);
        $provider = $service->save($this->school, $this->profile());
        DB::table('school_mail_provider_profiles')->where('id', $provider->id)->update(['password' => 'broken-ciphertext']);
        $provider = $provider->fresh();

        $this->assertTrue($service->passwordState($provider)['unusable']);
        $this->assertFalse($service->isComplete($provider));

        try {
            $service->normalize($provider);
            $this->fail('Expected unusable encrypted password to be rejected.');
        } catch (\Throwable $exception) {
            $this->assertSame('The saved SMTP password cannot be decrypted. Re-enter and save the password.', $exception->getMessage());
            $this->assertStringNotContainsString('broken-ciphertext', $exception->getMessage());
        }
    }

    public function test_existing_single_profile_settings_are_backfilled_without_losing_encryption(): void
    {
        $legacy = $this->legacyMailSetting([
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'username' => 'legacy@example.test',
            'password' => 'legacy-app-password',
            'encryption' => 'ssl',
            'from_address' => 'legacy@example.test',
            'from_name' => 'Legacy School',
            'timeout' => 10,
        ]);

        $this->runMailProviderMigration();

        $provider = SchoolMailProviderProfile::where('school_id', $this->school->id)->sole();
        $this->assertSame('gmail', $provider->provider_type);
        $this->assertSame('legacy-app-password', $provider->password);
        $this->assertSame($legacy->getRawOriginal('password'), $provider->getRawOriginal('password'));
        $this->assertTrue($provider->is_primary);
    }

    public function test_mail_provider_migration_normalizes_iso_legacy_test_datetime_and_preserves_encrypted_password(): void
    {
        $legacy = $this->legacyMailSetting([
            'host' => 'smtp.gmail.com',
            'username' => 'legacy@example.test',
            'password' => 'legacy-app-password',
            'from_address' => 'legacy@example.test',
        ]);

        DB::table('mail_settings')->where('id', $legacy->id)->update([
            'metadata' => json_encode([
                'timeout' => 20,
                'last_test' => [
                    'outcome' => 'accepted_by_smtp',
                    'category' => 'accepted',
                    'at' => '2026-07-07T19:49:44+00:00',
                ],
            ]),
            'created_at' => '2026-07-07T18:49:44+00:00',
            'updated_at' => '2026-07-07T19:00:01+00:00',
        ]);
        $legacy = $legacy->fresh();

        $this->runMailProviderMigration();

        $provider = SchoolMailProviderProfile::where('school_id', $this->school->id)->sole();
        $rawProvider = DB::table('school_mail_provider_profiles')->where('id', $provider->id)->first();

        $this->assertSame('2026-07-07 19:49:44', $rawProvider->last_tested_at);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $rawProvider->last_tested_at);
        $this->assertSame('2026-07-07 18:49:44', $rawProvider->created_at);
        $this->assertSame('2026-07-07 19:00:01', $rawProvider->updated_at);
        $this->assertSame('legacy-app-password', $provider->password);
        $this->assertSame($legacy->getRawOriginal('password'), $provider->getRawOriginal('password'));
        $this->assertNotSame('legacy-app-password', $provider->getRawOriginal('password'));
    }

    public function test_mail_provider_migration_is_safe_after_partial_table_creation_and_reruns_without_duplicates(): void
    {
        $this->legacyMailSetting([
            'host' => 'mail.example.test',
            'username' => 'legacy@example.test',
            'from_address' => 'legacy@example.test',
        ]);

        $this->runMailProviderMigration();
        $this->runMailProviderMigration();

        $this->assertTrue(Schema::hasTable('school_mail_provider_profiles'));

        foreach (['provider_profile_id', 'provider_name', 'provider_type', 'provider_position', 'attempt_sequence', 'message_kind'] as $column) {
            $this->assertTrue(Schema::hasColumn('mail_delivery_attempts', $column));
        }

        $this->assertSame(1, SchoolMailProviderProfile::where('school_id', $this->school->id)->count());
    }

    public function test_mail_provider_migration_updates_existing_matching_profile_without_replacing_encrypted_password(): void
    {
        $legacy = $this->legacyMailSetting([
            'host' => 'mail.example.test',
            'username' => 'legacy@example.test',
            'password' => 'legacy-app-password',
            'from_address' => 'legacy@example.test',
        ]);
        DB::table('mail_settings')->where('id', $legacy->id)->update([
            'metadata' => json_encode([
                'last_test' => [
                    'outcome' => 'failed',
                    'category' => 'authentication_failed',
                    'at' => '2026-07-07T19:49:44+00:00',
                ],
            ]),
        ]);

        $existing = app(SchoolMailProviderService::class)->save($this->school, $this->profile([
            'host' => 'mail.example.test',
            'username' => 'legacy@example.test',
            'password' => 'existing-provider-secret',
            'from_address' => 'legacy@example.test',
        ]));
        $existingCiphertext = $existing->fresh()->getRawOriginal('password');

        $this->runMailProviderMigration();

        $provider = SchoolMailProviderProfile::where('school_id', $this->school->id)->sole();
        $rawProvider = DB::table('school_mail_provider_profiles')->where('id', $provider->id)->first();

        $this->assertSame($existing->id, $provider->id);
        $this->assertSame($existingCiphertext, $provider->getRawOriginal('password'));
        $this->assertSame('existing-provider-secret', $provider->password);
        $this->assertSame('2026-07-07 19:49:44', $rawProvider->last_tested_at);
        $this->assertSame($legacy->id, data_get($provider->metadata, 'migrated_from_mail_setting_id'));
    }

    public function test_mail_provider_migration_does_not_touch_protected_files(): void
    {
        $protectedFiles = [
            public_path('build.zip'),
            database_path('migrations/2026_05_01_173857_create_result_publications_table.php'),
        ];
        $before = $this->fileHashes($protectedFiles);

        $this->legacyMailSetting();
        $this->runMailProviderMigration();

        $this->assertSame($before, $this->fileHashes($protectedFiles));
    }

    public function test_school_admin_provider_ui_is_tenant_isolated_and_never_renders_passwords(): void
    {
        $service = app(SchoolMailProviderService::class);
        $provider = $service->save($this->school, $this->profile(['password' => 'never-render-this']));
        $other = $this->school('Other Academy');
        $otherProvider = $service->save($other, $this->profile(['name' => 'Other Secret Provider', 'password' => 'other-secret']));
        $admin = $this->schoolAdmin($this->school);
        $this->actInSchool($admin, $this->school);

        $this->get(route('school.mail-settings.edit'))
            ->assertOk()
            ->assertSee('Email Delivery')
            ->assertSee($provider->name)
            ->assertDontSee($otherProvider->name)
            ->assertDontSee('never-render-this')
            ->assertDontSee((string) $provider->getRawOriginal('password'));

        $this->post(route('school.mail-settings.providers.test', $otherProvider), [
            'test_email' => 'recipient@example.test',
        ])->assertForbidden();
    }

    public function test_mail_diagnose_lists_every_provider_without_credentials(): void
    {
        $service = app(SchoolMailProviderService::class);
        $provider = $service->save($this->school, $this->profile([
            'name' => 'Diagnostic Gmail',
            'provider_type' => 'gmail',
            'host' => 'smtp.gmail.com',
            'password' => 'diagnostic-private-secret',
        ]));

        Artisan::call('standalone:mail-diagnose', ['--school' => $this->school->id]);
        $output = Artisan::output();

        $this->assertStringContainsString('Provider: Diagnostic Gmail', $output);
        $this->assertStringContainsString('Password: Available', $output);
        $this->assertStringContainsString('— non-delivery', $output);
        $this->assertStringNotContainsString('diagnostic-private-secret', $output);
        $this->assertStringNotContainsString((string) $provider->getRawOriginal('password'), $output);
    }

    private function profile(array $overrides = []): array
    {
        return array_merge([
            'name' => 'SMTP Provider',
            'provider_type' => 'custom_smtp',
            'mailer' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => 465,
            'username' => 'mailer@example.test',
            'password' => 'smtp-secret',
            'encryption' => 'ssl',
            'from_address' => 'mailer@example.test',
            'from_name' => 'Provider Academy',
            'reply_to_address' => 'reply@example.test',
            'reply_to_name' => 'Support',
            'timeout' => 10,
            'is_enabled' => true,
            'is_primary' => false,
            'priority' => 10,
        ], $overrides);
    }

    private function legacyMailSetting(array $overrides = []): MailSetting
    {
        return app(MailSettingService::class)->updateForSchool($this->school, array_merge([
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'mail.example.test',
            'port' => 587,
            'username' => 'legacy@example.test',
            'password' => 'legacy-app-password',
            'encryption' => 'tls',
            'from_address' => 'legacy@example.test',
            'from_name' => 'Legacy School',
            'timeout' => 10,
        ], $overrides));
    }

    private function runMailProviderMigration(): void
    {
        $migration = require database_path('migrations/2026_07_06_000001_create_school_mail_provider_profiles_and_extend_attempts.php');
        $migration->up();
    }

    private function fileHashes(array $paths): array
    {
        return collect($paths)
            ->mapWithKeys(fn (string $path): array => [
                $path => File::exists($path) ? hash_file('sha256', $path) : null,
            ])
            ->all();
    }

    private function school(string $name): School
    {
        return School::create([
            'name' => $name,
            'slug' => str($name)->slug().'-'.uniqid(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function schoolAdmin(School $school): User
    {
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('school_admin');
        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        return $user;
    }

    private function actInSchool(User $user, School $school): void
    {
        $this->actingAs($user)->withSession([
            'workspace.type' => TenantContext::WORKSPACE_SCHOOL,
            'workspace.key' => "school:{$school->id}:school_admin",
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ]);
    }
}
