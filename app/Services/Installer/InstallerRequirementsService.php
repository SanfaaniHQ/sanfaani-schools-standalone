<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\File;

class InstallerRequirementsService
{
    public function requirements(): array
    {
        $minimum = (string) config('installer.requirements.php_minimum', '8.2.0');

        return collect([
            $this->check('PHP version', version_compare(PHP_VERSION, $minimum, '>='), true, PHP_VERSION, "PHP {$minimum} or newer is required."),
            ...collect(config('installer.requirements.required_extensions', []))
                ->map(fn (string $extension): array => $this->check("PHP extension: {$extension}", extension_loaded($extension), true, $extension, "Enable the {$extension} PHP extension."))
                ->all(),
            ...collect(config('installer.requirements.optional_extensions', []))
                ->map(fn (string $extension): array => $this->check("Optional extension: {$extension}", extension_loaded($extension), false, $extension, "Recommended for some deployments, but not required for installation."))
                ->all(),
        ])->values()->all();
    }

    public function permissions(): array
    {
        return [
            $this->check('Storage folder writable', File::isWritable(storage_path()), true, storage_path(), 'Set write permission on storage.'),
            $this->check('Bootstrap cache writable', File::isWritable(base_path('bootstrap/cache')), true, base_path('bootstrap/cache'), 'Set write permission on bootstrap/cache.'),
            $this->check('Public storage link', File::exists(public_path('storage')), false, public_path('storage'), 'Create the storage link manually from hosting tools if public uploads are unavailable.'),
        ];
    }

    public function environment(): array
    {
        return [
            $this->check('.env file exists', File::exists(base_path('.env')), true, base_path('.env'), 'Create .env from .env.example and set hosting values.'),
            $this->check('APP_KEY exists', filled(config('app.key')), true, filled(config('app.key')) ? 'Configured' : 'Missing', 'Generate and paste APP_KEY in .env.'),
            $this->check('Database configured', filled(config('database.default')), true, (string) config('database.default'), 'Set DB_CONNECTION and database credentials in .env.'),
            $this->check('Queue connection configured', filled(config('queue.default')), false, (string) config('queue.default'), 'Shared hosting can use sync until a worker is available.'),
            $this->check('Cache store configured', filled(config('cache.default')), false, (string) config('cache.default'), 'File cache is acceptable for small shared-hosting installs.'),
            $this->check('Filesystem disk configured', filled(config('filesystems.default')), false, (string) config('filesystems.default'), 'Public disk needs a storage link for uploaded files.'),
        ];
    }

    public function appKeyStatus(): array
    {
        return $this->check('Application key', filled(config('app.key')), true, filled(config('app.key')) ? 'Configured' : 'Missing', 'Use cPanel terminal, local packaging, or hosting tools to generate APP_KEY before going live.');
    }

    public function migrationReadiness(?int $pendingMigrations = null): array
    {
        if ($pendingMigrations === null) {
            return $this->check('Migration status', true, false, 'Unknown', 'Pending migrations could not be counted safely; run migrations manually from hosting tools if needed.');
        }

        return $this->check('Migration status', $pendingMigrations === 0, false, "{$pendingMigrations} pending", 'Run php artisan migrate from a terminal or hosting migration tool before finalizing.');
    }

    public function summary(): array
    {
        return [
            'requirements' => $this->requirements(),
            'permissions' => $this->permissions(),
            'environment' => $this->environment(),
        ];
    }

    private function check(string $label, bool $passes, bool $required, string $value, string $message): array
    {
        return [
            'label' => $label,
            'status' => $passes ? 'pass' : ($required ? 'fail' : 'warning'),
            'required' => $required,
            'value' => $value,
            'message' => $passes ? 'Ready' : $message,
        ];
    }
}
