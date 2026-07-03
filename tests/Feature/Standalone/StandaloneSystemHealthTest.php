<?php

namespace Tests\Feature\Standalone;

use App\Models\License;
use App\Models\School;
use App\Services\Standalone\StandaloneSchedulerHeartbeatService;
use App\Services\Standalone\StandaloneSystemHealthService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StandaloneSystemHealthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        File::delete(storage_path('app/installed.lock'));

        config([
            'app.url' => 'https://school.example.test',
            'standalone.product_edition' => 'standalone',
            'standalone.installer_enabled' => true,
            'standalone.installed' => true,
            'standalone.offline_mode' => 'local_first',
            'standalone.sync.enabled' => false,
            'standalone.sync.endpoint' => '',
            'standalone.sync.token' => '',
            'standalone.sync.backup_enabled' => false,
            'standalone.scheduler_monitor.enabled' => true,
            'standalone.scheduler_monitor.cache_store' => 'array',
            'standalone.scheduler_monitor.schedule_cache_store' => 'array',
            'standalone.scheduler_monitor.cache_key' => StandaloneSchedulerHeartbeatService::CACHE_KEY,
            'standalone.scheduler_monitor.stale_after_minutes' => 15,
            'standalone.health.disk_free_warning_mb' => 1,
            'standalone.health.writable_paths' => [
                'storage/app',
                'storage/framework/cache',
                'storage/logs',
            ],
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.demo_enabled' => false,
            'sanfaani.deployment.installed' => true,
            'demo.enabled' => false,
            'demo.marketplace.enabled' => false,
            'sanfaani.license_validation_enabled' => false,
        ]);
    }

    protected function tearDown(): void
    {
        File::delete(storage_path('app/installed.lock'));

        parent::tearDown();
    }

    public function test_health_summary_includes_required_standalone_signals(): void
    {
        $summary = app(StandaloneSystemHealthService::class)->summary($this->school());
        $checks = $this->checksByKey($summary);

        foreach ([
            'php_version',
            'laravel_version',
            'database_connection',
            'writable_storage_app',
            'writable_storage_framework_cache',
            'writable_storage_logs',
            'disk_free_space',
            'upload_max_filesize',
            'post_max_size',
            'queue_connection',
            'scheduler_heartbeat',
            'mail_configuration',
            'installer_status',
            'backup_status',
            'update_readiness',
            'standalone_sync',
            'offline_mode',
            'safe_health_output',
        ] as $key) {
            $this->assertArrayHasKey($key, $checks);
        }

        $this->assertSame('pass', $checks['database_connection']['status']);
        $this->assertSame('pass', $checks['safe_health_output']['status']);
        $this->assertArrayNotHasKey('license_status', $checks);
    }

    public function test_scheduler_heartbeat_command_records_healthy_state(): void
    {
        Cache::forget(StandaloneSchedulerHeartbeatService::CACHE_KEY);

        $this->artisan('standalone:scheduler-heartbeat')
            ->expectsOutput('Standalone scheduler heartbeat recorded: Healthy')
            ->assertExitCode(Command::SUCCESS);

        $status = app(StandaloneSchedulerHeartbeatService::class)->status();

        $this->assertSame('healthy', $status['status']);
        $this->assertNotNull($status['last_heartbeat_at']);
    }

    public function test_scheduler_heartbeat_service_reports_stale_state(): void
    {
        config(['standalone.scheduler_monitor.stale_after_minutes' => 5]);

        Cache::put(
            StandaloneSchedulerHeartbeatService::CACHE_KEY,
            now()->subMinutes(30)->toIso8601String(),
            now()->addDay(),
        );

        $status = app(StandaloneSchedulerHeartbeatService::class)->status();

        $this->assertSame('stale', $status['status']);
        $this->assertSame('Stale', $status['label']);
        $this->assertStringContainsString('cron', $status['message']);
    }

    public function test_sync_queue_warning_works_safely_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['queue.default' => 'sync']);

        $queueCheck = $this->checksByKey(app(StandaloneSystemHealthService::class)->summary($this->school()))['queue_connection'];

        $this->assertSame('warning', $queueCheck['status']);
        $this->assertStringContainsString('Sync queue is configured in production', $queueCheck['message']);
        $this->assertSame('sync', $queueCheck['context']['connection']);
        $this->assertSame('sync', $queueCheck['context']['driver']);
    }

    public function test_disk_and_storage_checks_use_safe_relative_paths(): void
    {
        $summary = app(StandaloneSystemHealthService::class)->summary($this->school());
        $checks = $this->checksByKey($summary);
        $payload = json_encode($summary, JSON_UNESCAPED_SLASHES);

        $this->assertSame('storage/app', $checks['writable_storage_app']['context']['path']);
        $this->assertSame('storage', $checks['disk_free_space']['context']['path']);
        $this->assertStringNotContainsString(base_path(), (string) $payload);
        $this->assertStringNotContainsString(storage_path(), (string) $payload);
    }

    public function test_health_output_does_not_expose_secrets(): void
    {
        config([
            'database.connections.sqlite.password' => 'database-secret-value',
            'mail.mailers.smtp.password' => 'smtp-secret-value',
            'standalone.sync.enabled' => true,
            'standalone.sync.endpoint' => 'https://sync.example.test/private-endpoint',
            'standalone.sync.token' => 'sync-token-secret-value',
        ]);

        License::create([
            'school_id' => null,
            'license_key_hash' => hash('sha256', 'raw-license-secret-value'),
            'license_type' => 'annual',
            'status' => 'active',
            'issued_to_name' => 'Standalone School',
            'issued_to_email' => 'owner@example.test',
            'domain' => 'school.example.test',
            'allowed_domains' => ['school.example.test'],
            'features' => [],
            'entitlements' => [],
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addYear(),
        ]);

        $payload = json_encode(app(StandaloneSystemHealthService::class)->summary($this->school()), JSON_UNESCAPED_SLASHES);

        $this->assertStringNotContainsString('database-secret-value', (string) $payload);
        $this->assertStringNotContainsString('smtp-secret-value', (string) $payload);
        $this->assertStringNotContainsString('sync-token-secret-value', (string) $payload);
        $this->assertStringNotContainsString('raw-license-secret-value', (string) $payload);
        $this->assertStringNotContainsString('https://sync.example.test/private-endpoint', (string) $payload);
    }

    private function school(): School
    {
        return School::create([
            'name' => 'Standalone Health Academy',
            'slug' => 'standalone-health-academy',
            'email' => 'school@example.test',
            'phone' => '08030000000',
            'address' => 'Ilorin',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function checksByKey(array $summary): array
    {
        return collect($summary['sections'])
            ->flatMap(fn (array $section): array => $section['checks'])
            ->keyBy('key')
            ->all();
    }
}
