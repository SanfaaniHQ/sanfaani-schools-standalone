<?php

namespace Tests\Feature\Mail;

use App\Contracts\SchoolAwareMailNotification;
use App\Exceptions\MailConfigurationException;
use App\Models\MailDeliveryAttempt;
use App\Models\MailSetting;
use App\Models\School;
use App\Models\User;
use App\Services\MailSettingService;
use App\Services\SchoolSmtpService;
use App\Support\MailSecurity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\MailManager;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class SchoolSmtpDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('m', 32)),
            'mail.default' => 'log',
            'mail.from.address' => 'platform@example.test',
            'mail.from.name' => 'Sanfaani Schools',
        ]);

        $this->school = School::create([
            'name' => 'Mail Test Academy',
            'slug' => 'mail-test-academy',
            'email' => 'school@example.test',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    public function test_missing_sender_name_is_normalized_and_reply_to_never_breaks_mailer_creation(): void
    {
        $smtp = app(SchoolSmtpService::class);
        $settings = $smtp->normalize([
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => 587,
            'encryption' => 'tls',
            'from_address' => 'sender@example.test',
            'reply_to_email' => 'replies@example.test',
        ]);

        $this->assertArrayHasKey('name', $settings['from']);
        $this->assertArrayHasKey('name', $settings['reply_to']);
        $this->assertSame('Sanfaani Schools', $settings['from']['name']);

        $smtp->configure($settings);

        $this->assertSame('Sanfaani Schools', config('mail.from.name'));
        $this->assertSame('Sanfaani Schools', config('mail.reply_to.name'));
        $this->assertNotNull(app(MailManager::class)->mailer(SchoolSmtpService::MAILER));
    }

    public function test_ssl_tls_gmail_and_generic_cpanel_configs_map_to_symfony_mailer_options(): void
    {
        $smtp = app(SchoolSmtpService::class);
        $ssl = $smtp->normalize([
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'username' => 'teacher@example.test',
            'password' => 'app-password',
            'encryption' => 'ssl',
            'from_address' => 'teacher@example.test',
            'from_name' => 'Teacher',
        ]);
        $tls = $smtp->normalize([
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'mail.school.example',
            'port' => 587,
            'username' => 'info@school.example',
            'password' => 'mailbox-password',
            'encryption' => 'tls',
            'from_address' => 'info@school.example',
            'from_name' => 'School',
        ]);
        $gmailTls = $smtp->normalize([
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => 'teacher@example.test',
            'password' => 'app-password',
            'encryption' => 'tls',
            'from_address' => 'teacher@example.test',
        ]);
        $cpanelSsl = $smtp->normalize([
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'server.example.test',
            'port' => 465,
            'username' => 'info@example.test',
            'password' => 'mailbox-password',
            'encryption' => 'ssl',
            'from_address' => 'info@example.test',
        ]);

        $sslConfig = $smtp->mailerConfig($ssl);
        $tlsConfig = $smtp->mailerConfig($tls);
        $gmailTlsConfig = $smtp->mailerConfig($gmailTls);
        $cpanelSslConfig = $smtp->mailerConfig($cpanelSsl);

        $this->assertSame('smtps', $sslConfig['scheme']);
        $this->assertSame(465, $sslConfig['port']);
        $this->assertSame('smtp', $tlsConfig['scheme']);
        $this->assertSame(587, $tlsConfig['port']);
        $this->assertTrue($tlsConfig['require_tls']);
        $this->assertTrue($tlsConfig['auto_tls']);
        $this->assertSame('smtp', $gmailTlsConfig['scheme']);
        $this->assertTrue($gmailTlsConfig['require_tls']);
        $this->assertSame('smtps', $cpanelSslConfig['scheme']);
        $this->assertSame(465, $cpanelSslConfig['port']);
        $this->assertArrayNotHasKey('verify_peer', $sslConfig);
    }

    public function test_unsupported_encryption_is_rejected(): void
    {
        $this->expectException(MailConfigurationException::class);
        $this->expectExceptionMessage('not supported');

        app(SchoolSmtpService::class)->normalize([
            'encryption' => 'starttls-or-bust',
        ]);
    }

    public function test_password_is_encrypted_and_blank_or_mask_keeps_it_while_new_value_replaces_it(): void
    {
        $service = app(MailSettingService::class);
        $setting = $service->updateForSchool($this->school, $this->settings(['password' => 'first-secret']));

        $this->assertNotSame('first-secret', $setting->getRawOriginal('password'));
        $this->assertSame('first-secret', $setting->password);
        $this->assertArrayNotHasKey('password', $setting->toArray());

        $service->updateForSchool($this->school, $this->settings(['password' => '']));
        $this->assertSame('first-secret', $service->current($this->school->id)->password);

        $service->updateForSchool($this->school, $this->settings(['password' => '************']));
        $this->assertSame('first-secret', $service->current($this->school->id)->password);

        $service->updateForSchool($this->school, $this->settings(['password' => 'replacement-secret']));
        $this->assertSame('replacement-secret', $service->current($this->school->id)->password);
        $this->assertDatabaseMissing('mail_settings', ['password' => 'replacement-secret']);
    }

    public function test_app_key_change_marks_password_unusable_without_exposing_ciphertext(): void
    {
        $service = app(MailSettingService::class);
        $setting = $service->updateForSchool($this->school, $this->settings(['password' => 'never-expose-me']));
        DB::table('mail_settings')->where('id', $setting->id)->update(['password' => 'invalid-encrypted-payload']);
        $setting = $setting->fresh();

        $state = $service->passwordState($setting);
        $this->assertTrue($state['unusable']);
        $this->assertFalse($state['available']);
        $this->assertSame('Needs re-entry', $service->maskedPassword($setting));

        try {
            app(SchoolSmtpService::class)->normalizeSetting($setting, $this->school);
            $this->fail('Expected the invalid encrypted password to be rejected.');
        } catch (MailConfigurationException $exception) {
            $this->assertSame('password_decryption_failed', $exception->category);
            $this->assertStringNotContainsString('invalid-encrypted-payload', $exception->getMessage());
        }
    }

    public function test_school_smtp_failure_is_not_hidden_by_platform_fallback(): void
    {
        $service = new class extends MailSettingService
        {
            protected function sendTestMessage(string $recipient, ?string $mailer = null): void
            {
                throw new RuntimeException('535 authentication failed using never-log-this-secret');
            }
        };

        try {
            $service->sendSchoolTestUsingData(
                $this->school,
                $this->settings(['password' => 'never-log-this-secret']),
                'admin@example.test'
            );
            $this->fail('Expected school SMTP failure.');
        } catch (RuntimeException $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);
            $this->assertSame('authentication_failed', $diagnostic['category']);
            $this->assertStringNotContainsString('never-log-this-secret', $diagnostic['message']);
            $this->assertStringNotContainsString('fallback', $diagnostic['message']);
        }
    }

    public function test_unsaved_settings_are_tested_without_persisting_them(): void
    {
        $saved = app(MailSettingService::class)->updateForSchool($this->school, $this->settings([
            'host' => 'saved.example.test',
            'password' => 'saved-secret',
        ]));
        $service = new class extends MailSettingService
        {
            public ?string $testedHost = null;

            protected function sendTestMessage(string $recipient, ?string $mailer = null): void
            {
                $this->testedHost = config('mail.mailers.school_smtp.host');
            }
        };

        $result = $service->sendSchoolTestUsingData($this->school, $this->settings([
            'host' => 'temporary.example.test',
            'password' => '',
        ]), 'admin@example.test', $saved);

        $this->assertTrue($result['accepted']);
        $this->assertSame('temporary.example.test', $service->testedHost);
        $this->assertSame('saved.example.test', $saved->fresh()->host);
    }

    public function test_log_fallback_is_reported_as_non_delivery(): void
    {
        $service = new MailSettingService;
        $result = $service->sendPlatformTest('admin@example.test', new MailSetting([
            'is_enabled' => true,
            'mailer' => 'log',
            'from_address' => 'platform@example.test',
            'from_name' => 'Platform',
        ]));

        $this->assertTrue($result['logged_only']);
        $this->assertFalse($result['accepted']);
        $this->assertSame('log', $result['transport']);
    }

    public function test_array_fallback_is_reported_as_non_delivery(): void
    {
        $result = (new MailSettingService)->sendPlatformTest('admin@example.test', new MailSetting([
            'is_enabled' => true,
            'mailer' => 'array',
            'from_address' => 'platform@example.test',
            'from_name' => 'Platform',
        ]));

        $this->assertFalse($result['accepted']);
        $this->assertTrue($result['logged_only']);
        $this->assertSame('array', $result['transport']);
    }

    public function test_smtp_failures_are_classified_into_safe_categories(): void
    {
        $this->assertSame('connection_failed', MailSecurity::diagnostic('Connection refused by server')['category']);
        $this->assertSame('tls_failed', MailSecurity::diagnostic('STARTTLS crypto negotiation failed')['category']);
        $this->assertSame('authentication_failed', MailSecurity::diagnostic('535 authentication failed')['category']);
        $this->assertSame('sender_rejected', MailSecurity::diagnostic('sender address rejected')['category']);
        $this->assertSame('recipient_rejected', MailSecurity::diagnostic('recipient address rejected')['category']);
        $this->assertSame('relay_denied', MailSecurity::diagnostic('relay access denied')['category']);
    }

    public function test_safe_delivery_attempts_store_provider_ids_without_secrets_or_bodies(): void
    {
        $service = app(MailSettingService::class);
        $service->recordDeliveryAttempt([
            'school_id' => $this->school->id,
            'transport' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => 587,
            'encryption' => 'tls',
            'sender' => 'sender@example.test',
            'recipient' => 'recipient@example.test',
            'status' => 'accepted_by_smtp',
            'provider_message_id' => 'provider-message-id-123',
            'configuration' => 'temporary',
            'external_delivery_attempted' => true,
        ]);

        $attempt = MailDeliveryAttempt::firstOrFail();
        $this->assertSame('accepted_by_smtp', $attempt->status);
        $this->assertSame('provider-message-id-123', $attempt->provider_message_id);
        $this->assertSame('temporary', $attempt->configuration);
        $this->assertArrayNotHasKey('password', $attempt->getAttributes());
        $this->assertArrayNotHasKey('body', $attempt->getAttributes());
    }

    public function test_runtime_mailer_is_refreshed_and_tenant_settings_do_not_leak(): void
    {
        $service = app(MailSettingService::class);
        $other = School::create([
            'name' => 'Other Academy',
            'slug' => 'other-academy',
            'email' => 'other@example.test',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $first = $service->updateForSchool($this->school, $this->settings(['host' => 'first.example.test']));
        $second = $service->updateForSchool($other, $this->settings([
            'host' => 'second.example.test',
            'from_address' => 'mail@other.example.test',
        ]));

        $firstHost = $service->withMailSettingContext($first, fn () => config('mail.mailers.school_smtp.host'));
        $secondHost = $service->withMailSettingContext($second, fn () => config('mail.mailers.school_smtp.host'));

        $this->assertSame('first.example.test', $firstHost);
        $this->assertSame('second.example.test', $secondHost);
        $this->assertNotSame($firstHost, $secondHost);
    }

    public function test_school_success_does_not_trigger_fallback_and_failure_retries_once_when_policy_allows(): void
    {
        Mail::fake();
        app(MailSettingService::class)->updateForSchool($this->school, $this->settings());

        $success = new class extends MailSettingService
        {
            public int $schoolAttempts = 0;

            public int $platformAttempts = 0;

            public function withSchoolMailContext(?School $school, callable $callback): mixed
            {
                $this->schoolAttempts++;

                return $callback();
            }

            public function withPlatformMailContext(callable $callback): mixed
            {
                $this->platformAttempts++;

                return $callback();
            }
        };
        $callbackCalls = 0;
        $result = $success->deliverForSchool($this->school, function () use (&$callbackCalls): string {
            $callbackCalls++;

            return 'accepted';
        });

        $this->assertSame('accepted', $result['result']);
        $this->assertFalse($result['fallback_used']);
        $this->assertSame(1, $success->schoolAttempts);
        $this->assertSame(0, $success->platformAttempts);
        $this->assertSame(1, $callbackCalls);

        $fallback = new class extends MailSettingService
        {
            public int $schoolAttempts = 0;

            public int $platformAttempts = 0;

            public function withSchoolMailContext(?School $school, callable $callback): mixed
            {
                $this->schoolAttempts++;

                throw new RuntimeException('535 authentication failed credential-redacted');
            }

            public function withPlatformMailContext(callable $callback): mixed
            {
                $this->platformAttempts++;

                return $callback();
            }

            public function platformMailerCanDeliver(): bool
            {
                return true;
            }

            public function platformMailerStatus(?MailSetting $setting = null): array
            {
                return [
                    'driver' => 'smtp',
                    'configured' => true,
                    'external_delivery' => true,
                    'password_unusable' => false,
                ];
            }
        };
        $callbackCalls = 0;
        $result = $fallback->deliverForSchool($this->school, function () use (&$callbackCalls): string {
            $callbackCalls++;

            return 'fallback-accepted';
        });

        $this->assertSame('fallback-accepted', $result['result']);
        $this->assertTrue($result['fallback_used']);
        $this->assertSame('authentication_failed', $result['primary_error']);
        $this->assertSame(1, $fallback->schoolAttempts);
        $this->assertSame(1, $fallback->platformAttempts);
        $this->assertSame(1, $callbackCalls);
    }

    public function test_mail_test_routes_are_authorized_and_rate_limited(): void
    {
        $this->get(route('school.mail-settings.edit'))->assertRedirect(route('login'));
        $schoolRoute = app('router')->getRoutes()->getByName('school.mail-settings.test');
        $fallbackRoute = app('router')->getRoutes()->getByName('school.mail-settings.test-fallback');

        $this->assertContains('auth', $schoolRoute->gatherMiddleware());
        $this->assertContains('role:school_admin', $schoolRoute->gatherMiddleware());
        $this->assertContains('throttle:5,1', $schoolRoute->gatherMiddleware());
        $this->assertContains('throttle:5,1', $fallbackRoute->gatherMiddleware());
    }

    public function test_mail_diagnose_command_reports_status_without_printing_secrets(): void
    {
        $setting = app(MailSettingService::class)->updateForSchool($this->school, $this->settings([
            'password' => 'diagnostic-secret',
        ]));

        Artisan::call('standalone:mail-diagnose', ['--school' => $this->school->id]);
        $output = Artisan::output();

        $this->assertStringContainsString('School SMTP complete: yes', $output);
        $this->assertStringContainsString('Host: smtp.example.test', $output);
        $this->assertStringNotContainsString('diagnostic-secret', $output);
        $this->assertStringNotContainsString((string) $setting->getRawOriginal('password'), $output);
    }

    public function test_notification_channel_uses_and_then_restores_the_correct_school_mailer(): void
    {
        Mail::fake();
        app(MailSettingService::class)->updateForSchool($this->school, $this->settings([
            'host' => 'queue-school.example.test',
        ]));
        $user = User::factory()->create(['school_id' => $this->school->id]);
        $notification = new RecordingSchoolMailNotification($this->school->id);
        $before = config('mail');

        app(ChannelManager::class)->driver('mail')->send($user, $notification);

        $this->assertSame('school_smtp', $notification->mailerSeen);
        $this->assertSame('queue-school.example.test', $notification->hostSeen);
        $this->assertSame($before, config('mail'));
    }

    private function settings(array $overrides = []): array
    {
        return array_merge([
            'is_enabled' => true,
            'mailer' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => 587,
            'username' => 'mailer@example.test',
            'password' => 'smtp-secret',
            'encryption' => 'tls',
            'from_address' => 'mailer@example.test',
            'from_name' => 'Mail Test Academy',
            'reply_to_email' => 'reply@example.test',
            'timeout' => 15,
        ], $overrides);
    }
}

class RecordingSchoolMailNotification extends Notification implements SchoolAwareMailNotification
{
    public ?string $mailerSeen = null;

    public ?string $hostSeen = null;

    public function __construct(private int $schoolId) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function schoolIdForMail(object $notifiable): ?int
    {
        return $this->schoolId;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->mailerSeen = config('mail.default');
        $this->hostSeen = config('mail.mailers.school_smtp.host');

        return (new MailMessage)->line('Tenant mail context test.');
    }
}
