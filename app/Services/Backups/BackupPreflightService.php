<?php

namespace App\Services\Backups;

use App\Models\School;
use App\Models\User;
use App\Support\Backups\BackupPreflightResult;
use Illuminate\Support\Facades\File;

class BackupPreflightService
{
    public function run(?School $school = null, ?User $actor = null): BackupPreflightResult
    {
        $result = new BackupPreflightResult;

        $this->checkEnabled($result);
        $this->checkDisk($result);
        $this->checkStorage($result);
        $this->checkArchiveLimit($result);
        $this->checkConfiguredScopes($result);
        $this->checkSharedHostingReadiness($result);

        return $result;
    }

    private function checkEnabled(BackupPreflightResult $result): void
    {
        $enabled = (bool) config('backups.enabled', true);

        $result->add(
            'backup_manager_enabled',
            'Backup manager enabled',
            $enabled ? 'pass' : 'fail',
            $enabled ? 'info' : 'error',
            $enabled ? 'Backup manager configuration is enabled.' : 'Backup manager configuration is disabled.',
            ! $enabled,
        );
    }

    private function checkDisk(BackupPreflightResult $result): void
    {
        $disk = (string) config('backups.disk', 'local');
        $configured = is_array(config("filesystems.disks.{$disk}"));

        $result->add(
            'backup_disk',
            'Backup disk',
            $configured ? 'pass' : 'fail',
            $configured ? 'info' : 'error',
            $configured ? "Backup disk [{$disk}] is configured." : "Backup disk [{$disk}] is not configured.",
            ! $configured,
            ['disk' => $disk],
        );
    }

    private function checkStorage(BackupPreflightResult $result): void
    {
        $storageWritable = $this->writable(storage_path('app'));
        $cacheWritable = $this->writable(storage_path('framework/cache'));
        $diskRoot = config('filesystems.disks.'.config('backups.disk', 'local').'.root');
        $diskWritable = filled($diskRoot) ? $this->writable((string) $diskRoot) : false;

        $result->add(
            'storage_writable',
            'Storage writable',
            $storageWritable ? 'pass' : 'fail',
            $storageWritable ? 'info' : 'error',
            $storageWritable ? 'Application storage is writable.' : 'Application storage is not writable.',
            ! $storageWritable,
        );

        $result->add(
            'cache_writable',
            'Cache writable',
            $cacheWritable ? 'pass' : 'fail',
            $cacheWritable ? 'info' : 'error',
            $cacheWritable ? 'Framework cache storage is writable.' : 'Framework cache storage is not writable.',
            ! $cacheWritable,
        );

        $result->add(
            'backup_disk_writable',
            'Backup disk writable',
            $diskWritable ? 'pass' : 'fail',
            $diskWritable ? 'info' : 'error',
            $diskWritable ? 'Backup disk storage is writable.' : 'Backup disk storage is not writable.',
            ! $diskWritable,
        );
    }

    private function checkArchiveLimit(BackupPreflightResult $result): void
    {
        $limit = (int) config('backups.max_archive_mb', 250);

        $result->add(
            'max_archive_size',
            'Maximum archive size',
            $limit > 0 ? 'pass' : 'fail',
            $limit > 0 ? 'info' : 'error',
            $limit > 0 ? "Configured maximum archive metadata size is {$limit} MB." : 'Maximum archive size must be greater than zero.',
            $limit <= 0,
            ['max_archive_mb' => $limit],
        );
    }

    private function checkConfiguredScopes(BackupPreflightResult $result): void
    {
        foreach ([
            'database_enabled' => 'Database backup metadata',
            'files_enabled' => 'Uploaded files backup metadata',
            'config_enabled' => 'Configuration backup metadata',
        ] as $key => $label) {
            $enabled = (bool) config("backups.{$key}", true);

            $result->add(
                $key,
                $label,
                $enabled ? 'pass' : 'warning',
                $enabled ? 'info' : 'warning',
                $enabled ? "{$label} is enabled." : "{$label} is disabled by configuration.",
                false,
            );
        }
    }

    private function checkSharedHostingReadiness(BackupPreflightResult $result): void
    {
        if ((bool) config('backups.database_enabled', true) && ! (bool) config('backups.shell_dump_enabled', false)) {
            $result->add(
                'database_shell_dump',
                'Database export method',
                'warning',
                'warning',
                'Shell database dumps are not required. Use cPanel Backup Wizard or phpMyAdmin export when the host blocks mysqldump.',
                false,
                ['manual_export_guidance' => true],
            );
        }

        $result->add(
            'shared_hosting_guidance',
            'Shared-hosting guidance',
            'warning',
            'warning',
            'For cPanel or Namecheap, keep backups outside public folders and restore manually after verification.',
            false,
            ['cpanel_guidance' => true, 'namecheap_guidance' => true],
        );
    }

    private function writable(string $path): bool
    {
        if (! File::exists($path)) {
            File::ensureDirectoryExists($path, 0750);
        }

        return File::isDirectory($path) && File::isWritable($path);
    }
}
