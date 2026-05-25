<?php

namespace Tests\Feature\Backups;

use App\Models\Backup;
use App\Models\BackupItem;
use App\Models\BackupVerification;
use App\Models\License;
use App\Models\School;
use App\Models\UpdatePackage;
use App\Models\User;
use App\Services\Licensing\LicenseKeyHasher;
use App\Services\Updates\UpdateManifestService;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

trait BackupTestSupport
{
    private function configureSaasBackups(): void
    {
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'sanfaani.deployment.updates_enabled' => true,
            'features.features.backup_manager.enabled' => true,
            'features.features.managed_backups.enabled' => true,
            'features.features.update_manager.enabled' => true,
            'backups.enabled' => true,
            'backups.require_license_entitlement' => true,
            'updates.enabled' => true,
            'updates.backup_required' => true,
            'updates.require_license_entitlement' => true,
            'licensing.validation_enabled' => true,
            'licensing.require_domain_match' => false,
        ]);
    }

    private function configureSingleSchoolBackups(): School
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.updates_enabled' => true,
            'features.features.backup_manager.enabled' => true,
            'features.features.update_manager.enabled' => true,
            'backups.enabled' => true,
            'backups.require_license_entitlement' => true,
            'updates.enabled' => true,
            'licensing.validation_enabled' => true,
            'licensing.require_domain_match' => false,
        ]);

        return $this->school();
    }

    private function superAdmin(): User
    {
        Role::findOrCreate('super_admin');

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    private function schoolAdmin(?School $school = null): User
    {
        Role::findOrCreate('school_admin');

        $user = User::factory()->create(['school_id' => $school?->id]);
        $user->assignRole('school_admin');

        return $user;
    }

    private function school(string $name = 'Backup School'): School
    {
        $next = School::count() + 1;

        return School::create([
            'name' => "{$name} {$next}",
            'slug' => 'backup-school-'.$next,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function license(School $school, array $overrides = []): License
    {
        return License::create(array_merge([
            'school_id' => $school->id,
            'license_key_hash' => app(LicenseKeyHasher::class)->hash('BACKUP-KEY-'.$school->id.'-'.uniqid()),
            'license_type' => config('sanfaani.deployment.license_mode', 'annual'),
            'status' => 'active',
            'domain' => 'licensed.test',
            'allowed_domains' => ['licensed.test'],
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'features' => [],
            'entitlements' => ['backup_manager' => true, 'backups' => true, 'update_manager' => true],
        ], $overrides));
    }

    private function verifiedBackup(?School $school = null): Backup
    {
        $payload = json_encode(['backup_id' => 'test', 'env_exported' => false], JSON_THROW_ON_ERROR);
        $path = 'backups/metadata/test-'.uniqid().'.json';

        Storage::disk('local')->put($path, $payload);

        $backup = Backup::create([
            'school_id' => $school?->id,
            'type' => Backup::TYPE_MANUAL,
            'status' => Backup::STATUS_VERIFIED,
            'disk' => 'local',
            'path' => $path,
            'filename' => basename($path),
            'size_bytes' => strlen($payload),
            'checksum' => hash('sha256', $payload),
            'trigger' => 'test',
            'completed_at' => now(),
            'expires_at' => now()->addDays(7),
            'metadata' => ['restore_performed' => false],
        ]);

        foreach ([BackupItem::TYPE_DATABASE, BackupItem::TYPE_FILES, BackupItem::TYPE_CONFIG] as $type) {
            BackupItem::create([
                'backup_id' => $backup->id,
                'item_type' => $type,
                'source_label' => "{$type} metadata",
                'path' => null,
                'status' => 'recorded',
                'metadata' => ['test' => true],
            ]);
        }

        BackupVerification::create([
            'backup_id' => $backup->id,
            'status' => BackupVerification::STATUS_VERIFIED,
            'checked_at' => now(),
            'checksum_valid' => true,
            'archive_readable' => true,
            'required_items_present' => true,
            'message' => 'Verified test backup.',
            'context' => [],
        ]);

        return $backup;
    }

    private function updatePackage(): UpdatePackage
    {
        $checksum = str_repeat('d', 64);
        $manifest = array_merge(app(UpdateManifestService::class)->sample(), [
            'version' => '1.0.4',
            'checksum' => $checksum,
            'minimum_laravel' => app()->version(),
        ]);

        return UpdatePackage::create([
            'version' => $manifest['version'],
            'channel' => $manifest['channel'],
            'source' => 'upload',
            'filename' => 'backup-preflight.zip',
            'path' => 'packages/backup-preflight.zip',
            'checksum' => $checksum,
            'signature' => $manifest['signature'],
            'size_bytes' => 1024,
            'status' => UpdatePackage::STATUS_VALIDATED,
            'manifest' => $manifest,
            'validated_at' => now(),
            'metadata' => ['extracted' => false, 'applied' => false],
        ]);
    }
}
