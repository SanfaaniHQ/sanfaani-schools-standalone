<?php

namespace App\Services\Backups;

use App\Models\Backup;
use App\Models\BackupItem;
use Illuminate\Support\Facades\File;

class BackupFilesService
{
    public function __construct(
        private BackupLogService $logs,
    ) {}

    public function capture(Backup $backup): array
    {
        if (! (bool) config('backups.files_enabled', true)) {
            return [
                $this->createItem($backup, 'Uploaded files disabled', null, 'disabled', [
                    'enabled' => false,
                ]),
            ];
        }

        $items = [];

        foreach ($this->safeFileRoots() as $root) {
            $exists = File::exists(base_path($root));

            $items[] = $this->createItem($backup, $this->label($root), $root, $exists ? 'recorded' : 'warning', [
                'exists' => $exists,
                'metadata_only' => true,
                'excluded_paths' => $this->excludedPaths(),
            ]);
        }

        $this->logs->log(
            'backup.files_metadata_recorded',
            'Safe uploaded-file backup metadata was recorded. Full application archives are not created by the web workflow.',
            $backup,
            severity: 'info',
            context: ['safe_roots' => $this->safeFileRoots()],
        );

        return $items;
    }

    public function safeFileRoots(): array
    {
        return collect((array) config('backups.safe_file_roots', []))
            ->map(fn (mixed $path): string => $this->normalizeRelativePath((string) $path))
            ->filter()
            ->reject(fn (string $path): bool => $this->isExcluded($path))
            ->unique()
            ->values()
            ->all();
    }

    public function excludedPaths(): array
    {
        return collect((array) config('backups.excluded_paths', []))
            ->map(fn (mixed $path): string => $this->normalizeRelativePath((string) $path))
            ->filter()
            ->values()
            ->all();
    }

    private function createItem(Backup $backup, string $label, ?string $path, string $status, array $metadata): BackupItem
    {
        return BackupItem::create([
            'backup_id' => $backup->id,
            'item_type' => BackupItem::TYPE_FILES,
            'source_label' => $label,
            'path' => $path,
            'size_bytes' => null,
            'checksum' => null,
            'status' => $status,
            'metadata' => $metadata + [
                'full_app_zip_created' => false,
                'unsafe_paths_included' => false,
            ],
        ]);
    }

    private function label(string $path): string
    {
        return 'Uploaded files metadata: '.$path;
    }

    private function isExcluded(string $path): bool
    {
        foreach ($this->excludedPaths() as $excluded) {
            if ($path === $excluded || str_starts_with($path.'/', $excluded.'/')) {
                return true;
            }
        }

        return false;
    }

    private function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        return preg_replace('#/+#', '/', $path) ?: '';
    }
}
