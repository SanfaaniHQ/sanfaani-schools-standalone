<?php

namespace App\Services\Updates;

use App\Models\UpdatePackage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;
use ZipArchive;

class UpdatePackageService
{
    public function __construct(
        private UpdateManifestService $manifests,
        private UpdateLogService $logs,
        private UpdateRollbackService $rollbacks,
    ) {}

    public function validationRules(): array
    {
        return [
            'package' => ['required', 'file', 'mimes:zip', 'max:'.$this->maxPackageKilobytes()],
            'manifest_json' => ['required', 'string'],
        ];
    }

    public function storeUploadedPackage(UploadedFile $file, array $manifest, ?User $actor = null): UpdatePackage
    {
        if (! (bool) config('updates.allow_package_upload', true)) {
            throw ValidationException::withMessages([
                'package' => 'Update package uploads are disabled for this installation.',
            ]);
        }

        $manifest = $this->manifests->normalize($manifest);
        $errors = array_merge(
            $this->manifests->validate($manifest),
            $this->validateUploadedPackage($file, $manifest),
        );

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'package' => $errors,
            ]);
        }

        $checksum = hash_file('sha256', $file->getRealPath());
        $path = $this->storePrivately($file);
        $compatibility = $this->manifests->compatibility($manifest);
        $reviewPlan = $this->reviewPlanFromManifest($manifest, $compatibility);

        $package = UpdatePackage::create([
            'version' => (string) $manifest['version'],
            'channel' => (string) $manifest['channel'],
            'source' => 'upload',
            'filename' => $this->safeFilename($file->getClientOriginalName()),
            'path' => $path,
            'checksum' => $checksum,
            'signature' => $manifest['signature'] ?? null,
            'size_bytes' => $file->getSize(),
            'status' => UpdatePackage::STATUS_VALIDATED,
            'manifest' => $manifest,
            'uploaded_by' => $actor?->id ?? auth()->id(),
            'validated_at' => now(),
            'metadata' => [
                'safe_foundation_only' => true,
                'stored_on_private_disk' => true,
                'extracted' => false,
                'applied' => false,
                'migrations_run' => false,
                'compatibility' => $compatibility,
                'manifest_summary' => $this->safeManifestSummary($manifest),
                'review_plan' => $reviewPlan,
            ],
        ]);

        $this->logs->log(
            'update.package_uploaded',
            'Update package metadata was validated and stored privately. No files were extracted or applied.',
            $package,
            severity: 'info',
            context: [
                'version' => $package->version,
                'channel' => $package->channel,
                'size_bytes' => $package->size_bytes,
                'checksum' => $checksum,
            ],
            actor: $actor,
        );

        $this->rollbacks->createForPackage($package, $actor);

        return $package;
    }

    public function markReady(UpdatePackage $package, ?User $actor = null): UpdatePackage
    {
        if (! $package->hasKnownStatus() || $package->status !== UpdatePackage::STATUS_PRECHECK_READY) {
            throw new RuntimeException('Update package cannot be marked ready until all blocking preflight checks pass.');
        }

        $manifest = $package->manifest ?: [];
        $compatibility = $this->manifests->compatibility($manifest);

        $package->forceFill([
            'status' => UpdatePackage::STATUS_READY_FOR_MANUAL_UPDATE,
            'metadata' => array_merge($package->metadata ?: [], [
                'ready_for_manual_update_at' => now()->toIso8601String(),
                'application_performed' => false,
                'compatibility' => $compatibility,
                'review_plan' => $this->reviewPlanFromManifest($manifest, $compatibility),
            ]),
        ])->save();

        $this->logs->log(
            'update.package_marked_ready',
            'Package was marked ready for manual update planning. The application was not updated by the wizard.',
            $package,
            severity: 'info',
            actor: $actor,
        );

        return $package->fresh();
    }

    public function validateUploadedPackage(UploadedFile $file, array $manifest = []): array
    {
        $errors = [];
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = collect(config('updates.allowed_package_extensions', ['zip']))
            ->map(fn (mixed $value): string => strtolower((string) $value))
            ->all();

        if (! in_array($extension, $allowed, true)) {
            $errors[] = 'Update package extension is not allowed.';
        }

        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        $allowedMimes = collect(config('updates.allowed_package_mimes', ['application/zip']))
            ->map(fn (mixed $value): string => strtolower((string) $value))
            ->all();

        if (filled($mime) && ! in_array(strtolower((string) $mime), $allowedMimes, true)) {
            $errors[] = 'Update package MIME type is not allowed.';
        }

        if ($file->getSize() !== false && $file->getSize() > $this->maxPackageBytes()) {
            $errors[] = 'Update package exceeds the configured maximum size.';
        }

        $checksum = filled($file->getRealPath()) ? hash_file('sha256', $file->getRealPath()) : null;
        if ($checksum && filled($manifest['checksum'] ?? null) && ! hash_equals(strtolower((string) $manifest['checksum']), strtolower($checksum))) {
            $errors[] = 'Update package checksum does not match the manifest metadata.';
        }

        foreach ($this->archivePathIssues($file) as $issue) {
            $errors[] = $issue;
        }

        return $errors;
    }

    public function reviewPlan(UpdatePackage $package): array
    {
        $manifest = $package->manifest ?: [];

        return $this->reviewPlanFromManifest($manifest, $this->manifests->compatibility($manifest));
    }

    public function safeManifestSummary(array $manifest): array
    {
        return [
            'version' => $manifest['version'] ?? null,
            'channel' => $manifest['channel'] ?? null,
            'release_date' => $manifest['release_date'] ?? null,
            'target_product' => $manifest['target_product'] ?? $manifest['product_name'] ?? $manifest['product'] ?? null,
            'target_edition' => $manifest['target_edition'] ?? $manifest['product_edition'] ?? null,
            'target_version' => $manifest['target_version'] ?? $manifest['to_version'] ?? $manifest['version'] ?? null,
            'requires_backup' => (bool) ($manifest['requires_backup'] ?? false),
            'requires_migration' => (bool) ($manifest['requires_migration'] ?? false),
            'file_count' => count((array) ($manifest['files_changed'] ?? [])),
            'database_change_count' => count((array) ($manifest['database_changes'] ?? [])),
            'entitlements_required' => array_values((array) ($manifest['entitlements_required'] ?? [])),
            'unsafe_path_issues' => $this->manifests->unsafePathIssues($manifest),
        ];
    }

    public function maxPackageKilobytes(): int
    {
        return max(1, (int) config('updates.max_package_mb', 50)) * 1024;
    }

    private function maxPackageBytes(): int
    {
        return $this->maxPackageKilobytes() * 1024;
    }

    private function archivePathIssues(UploadedFile $file): array
    {
        if (! class_exists(ZipArchive::class) || ! filled($file->getRealPath())) {
            return [];
        }

        $zip = new ZipArchive;
        $opened = $zip->open($file->getRealPath());

        if ($opened !== true) {
            return ['Update package archive could not be opened for safe inspection.'];
        }

        try {
            $issues = [];

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->statIndex($index);
                $name = is_array($entry) ? (string) ($entry['name'] ?? '') : '';

                foreach ($this->manifests->pathIssues($name, 'Package entry') as $issue) {
                    $issues[] = $issue;
                }
            }

            return collect($issues)->unique()->values()->all();
        } finally {
            try {
                $zip->close();
            } catch (Throwable) {
                //
            }
        }
    }

    private function reviewPlanFromManifest(array $manifest, array $compatibility): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'safe_foundation_only' => true,
            'manual_only' => true,
            'application_performed' => false,
            'current_version' => $compatibility['current_version'] ?? config('version.version', '1.0.0'),
            'target_version' => $compatibility['target_version'] ?? $manifest['version'] ?? null,
            'compatibility_status' => $compatibility['status'] ?? 'review',
            'requires_backup' => (bool) ($manifest['requires_backup'] ?? false),
            'requires_migration_review' => (bool) ($manifest['requires_migration'] ?? false)
                || collect((array) ($manifest['database_changes'] ?? []))->isNotEmpty(),
            'file_count' => count((array) ($manifest['files_changed'] ?? [])),
            'database_change_count' => count((array) ($manifest['database_changes'] ?? [])),
            'protected_paths_blocked' => $this->manifests->protectedPaths(),
            'steps' => [
                ['label' => 'Confirm package provenance', 'status' => 'manual_review', 'body' => 'Use only a private Sanfaani package supplied through the agreed support channel.'],
                ['label' => 'Run preflight', 'status' => 'required', 'body' => 'Review PHP, Laravel, extension, database, storage, installer, backup, and manifest checks.'],
                ['label' => 'Verify backup', 'status' => (bool) ($manifest['requires_backup'] ?? false) ? 'required' : 'recommended', 'body' => 'Create and verify a backup before any manual file work starts.'],
                ['label' => 'Review changed files', 'status' => 'manual_review', 'body' => 'Compare manifest file counts and release notes before copying files outside the wizard.'],
                ['label' => 'Plan migration review', 'status' => 'manual_review', 'body' => 'Run migrations only from the approved deployment channel; the web wizard never runs them.'],
                ['label' => 'Apply outside web UI', 'status' => 'external_only', 'body' => 'No package extraction, shell command, Composer, npm, git, or migration action is performed by this UI.'],
            ],
        ];
    }

    private function storePrivately(UploadedFile $file): string
    {
        $directory = trim((string) config('updates.package_directory', 'packages'), '/');
        $name = Str::uuid()->toString().'.'.strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs($directory, $name, (string) config('updates.package_disk', 'updates'));

        if (! $path) {
            throw new RuntimeException('Update package could not be stored.');
        }

        return $path;
    }

    private function safeFilename(string $filename): string
    {
        $filename = basename($filename);

        return preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'update-package.zip';
    }
}
