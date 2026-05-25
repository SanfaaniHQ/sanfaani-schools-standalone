<?php

namespace App\Services\Updates;

use Illuminate\Http\UploadedFile;
use JsonException;

class UpdateManifestService
{
    public function requiredFields(): array
    {
        return [
            'version',
            'channel',
            'release_date',
            'minimum_php',
            'minimum_laravel',
            'requires_backup',
            'requires_migration',
            'migration_notes',
            'files_changed',
            'database_changes',
            'checksum',
            'signature',
            'rollback_supported',
            'release_notes',
            'entitlements_required',
        ];
    }

    public function parseJson(?string $json): array
    {
        if (! filled($json)) {
            return [];
        }

        try {
            $manifest = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        return is_array($manifest) ? $this->normalize($manifest) : [];
    }

    public function parseUploadedJson(UploadedFile $file): array
    {
        $contents = file_get_contents($file->getRealPath());

        return $this->parseJson($contents === false ? null : $contents);
    }

    public function validate(array $manifest): array
    {
        $errors = [];

        foreach ($this->requiredFields() as $field) {
            if (! array_key_exists($field, $manifest)) {
                $errors[] = "Manifest is missing {$field}.";
            }
        }

        $channel = $this->normalizeString($manifest['channel'] ?? null);
        if ($channel !== '' && ! in_array($channel, config('updates.channels', []), true)) {
            $errors[] = 'Manifest channel must be stable, beta, or security.';
        }

        if (filled($manifest['version'] ?? null) && ! preg_match('/^\d+\.\d+\.\d+(?:[-+][A-Za-z0-9._-]+)?$/', (string) $manifest['version'])) {
            $errors[] = 'Manifest version must use semantic version format.';
        }

        if (filled($manifest['release_date'] ?? null) && strtotime((string) $manifest['release_date']) === false) {
            $errors[] = 'Manifest release_date must be a valid date.';
        }

        foreach (['minimum_php', 'minimum_laravel'] as $field) {
            if (filled($manifest[$field] ?? null) && ! preg_match('/^\d+\.\d+(?:\.\d+)?/', (string) $manifest[$field])) {
                $errors[] = "Manifest {$field} must start with a version number.";
            }
        }

        if (filled($manifest['checksum'] ?? null) && ! preg_match('/^[a-f0-9]{64}$/i', (string) $manifest['checksum'])) {
            $errors[] = 'Manifest checksum must be a SHA-256 hash.';
        }

        foreach (['files_changed', 'database_changes', 'entitlements_required'] as $field) {
            if (array_key_exists($field, $manifest) && ! is_array($manifest[$field])) {
                $errors[] = "Manifest {$field} must be an array.";
            }
        }

        return $errors;
    }

    public function normalize(array $manifest): array
    {
        $manifest['channel'] = $this->normalizeString($manifest['channel'] ?? config('updates.channel', 'stable'));
        $manifest['requires_backup'] = (bool) ($manifest['requires_backup'] ?? false);
        $manifest['requires_migration'] = (bool) ($manifest['requires_migration'] ?? false);
        $manifest['rollback_supported'] = (bool) ($manifest['rollback_supported'] ?? false);

        foreach (['files_changed', 'database_changes', 'entitlements_required'] as $field) {
            $manifest[$field] = array_values((array) ($manifest[$field] ?? []));
        }

        return $manifest;
    }

    public function sample(): array
    {
        return [
            'version' => '1.0.1',
            'channel' => 'stable',
            'release_date' => now()->toDateString(),
            'minimum_php' => config('updates.php_minimum', '8.2.0'),
            'minimum_laravel' => app()->version(),
            'requires_backup' => true,
            'requires_migration' => false,
            'migration_notes' => 'No automatic migration will run from the web wizard.',
            'files_changed' => ['app/Services/ExampleService.php'],
            'database_changes' => [],
            'checksum' => str_repeat('0', 64),
            'signature' => 'planned-signature-placeholder',
            'rollback_supported' => true,
            'release_notes' => 'Describe the safe manual update steps here.',
            'entitlements_required' => ['update_manager'],
        ];
    }

    private function normalizeString(mixed $value): string
    {
        return str((string) $value)
            ->trim()
            ->lower()
            ->replace([' ', '-'], '_')
            ->toString();
    }
}
