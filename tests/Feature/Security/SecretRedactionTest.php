<?php

namespace Tests\Feature\Security;

use App\Services\Backups\BackupLogService;
use App\Services\Security\SecretRedactionService;
use App\Services\Updates\UpdateLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretRedactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_secret_redaction_masks_database_passwords(): void
    {
        $message = app(SecretRedactionService::class)->redact('DB_PASSWORD=super-secret mysql://user:pass@example.test/db');

        $this->assertStringContainsString('DB_PASSWORD=[redacted]', $message);
        $this->assertStringContainsString('mysql://user:[redacted]@example.test/db', $message);
        $this->assertStringNotContainsString('super-secret', $message);
    }

    public function test_secret_redaction_masks_api_keys_and_tokens(): void
    {
        $message = app(SecretRedactionService::class)->redact('API_KEY=abc123 Authorization: Bearer secret-token');

        $this->assertStringContainsString('API_KEY=[redacted]', $message);
        $this->assertStringContainsString('Bearer [redacted]', $message);
        $this->assertStringNotContainsString('secret-token', $message);
    }

    public function test_secret_redaction_masks_license_keys(): void
    {
        $context = app(SecretRedactionService::class)->redactArray([
            'license_key' => 'SANFAANI-LICENSE-RAW',
            'nested' => ['smtp_password' => 'smtp-secret'],
        ]);

        $this->assertSame('[redacted]', $context['license_key']);
        $this->assertSame('[redacted]', $context['nested']['smtp_password']);
    }

    public function test_backup_and_update_logs_do_not_expose_secrets(): void
    {
        $backupLog = app(BackupLogService::class)->log(
            'backup.warning',
            'Backup warning DB_PASSWORD=secret at '.base_path('storage/app/private'),
            context: ['api_token' => 'raw-token', 'path' => storage_path('logs/laravel.log')],
        );

        $updateLog = app(UpdateLogService::class)->log(
            'update.warning',
            'Update warning SANFAANI_LICENSE_KEY=raw-key at '.base_path('vendor'),
            context: ['mail_password' => 'mail-secret'],
        );

        $this->assertStringNotContainsString('secret', $backupLog->message);
        $this->assertStringNotContainsString(base_path(), $backupLog->message);
        $this->assertSame('[redacted]', $backupLog->context['api_token']);
        $this->assertStringNotContainsString('raw-key', $updateLog->message);
        $this->assertSame('[redacted]', $updateLog->context['mail_password']);
    }
}
