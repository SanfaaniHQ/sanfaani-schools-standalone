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
                ->map(fn (string $extension): array => $this->check("Optional extension: {$extension}", extension_loaded($extension), false, $extension, 'Recommended for some deployments, but not required for installation.'))
                ->all(),
        ])->values()->all();
    }

    public function permissions(): array
    {
        return [
            $this->writablePathCheck('Storage folder writable', 'storage', true, 'Set write permission on storage.'),
            $this->writablePathCheck('Bootstrap cache writable', 'bootstrap/cache', true, 'Set write permission on bootstrap/cache.'),
            $this->writablePathCheck('Storage app writable', 'storage/app', true, 'Set write permission on storage/app.'),
            $this->writablePathCheck('Framework cache writable', 'storage/framework/cache', true, 'Set write permission on storage/framework/cache.'),
            $this->writablePathCheck('Framework sessions writable', 'storage/framework/sessions', true, 'Set write permission on storage/framework/sessions.'),
            $this->writablePathCheck('Framework views writable', 'storage/framework/views', true, 'Set write permission on storage/framework/views.'),
            $this->writablePathCheck('Logs writable', 'storage/logs', true, 'Set write permission on storage/logs.'),
            $this->check('Public storage link', File::exists(public_path('storage')), false, 'public/storage', 'Create the storage link manually from hosting tools if public uploads are unavailable.'),
        ];
    }

    public function environment(): array
    {
        return [
            $this->check('.env file exists', File::exists(base_path('.env')), true, File::exists(base_path('.env')) ? 'Configured' : 'Missing', 'Create .env from .env.example and set hosting values.'),
            $this->check('Security key exists', filled(config('app.key')), true, filled(config('app.key')) ? 'Configured' : 'Missing', 'Generate the Laravel APP_KEY and save it in .env.'),
            $this->check('Database configured', filled(config('database.default')), true, (string) config('database.default'), 'Set DB_CONNECTION and database credentials in .env.'),
            $this->check('Queue connection configured', filled(config('queue.default')), false, (string) config('queue.default'), 'Shared hosting can use sync until a worker is available.'),
            $this->check('Cache store configured', filled(config('cache.default')), false, (string) config('cache.default'), 'File cache is acceptable for small shared-hosting installs.'),
            $this->check('Session driver configured', filled(config('session.driver')), false, (string) config('session.driver'), 'File or database sessions are acceptable for standalone installs.'),
            $this->mailConfigurationCheck(),
            $this->check('Filesystem disk configured', filled(config('filesystems.default')), false, (string) config('filesystems.default'), 'Public disk needs a storage link for uploaded files.'),
            $this->check('Scheduler monitor configured', (bool) config('standalone.scheduler_monitor.enabled', true), false, (bool) config('standalone.scheduler_monitor.enabled', true) ? 'Enabled' : 'Disabled', 'Configure cron to run php artisan schedule:run every minute after installation.'),
            $this->check('Backup metadata directory', filled(config('backups.metadata_directory')), false, (string) config('backups.metadata_directory', 'backups/metadata'), 'Backup metadata stays in storage and should remain outside the public web root.'),
            $this->check('Update package directory', filled(config('updates.package_directory')), false, (string) config('updates.package_directory', 'packages'), 'Guided updates only review package metadata and do not extract code from the installer.'),
            $this->check('Production safety', app()->environment('production') ? ! (bool) config('app.debug') : true, false, app()->environment('production') ? ((bool) config('app.debug') ? 'Review' : 'Ready') : 'Local/test', 'Disable APP_DEBUG before handing the installation to a school.'),
        ];
    }

    public function appKeyStatus(): array
    {
        return $this->check('Security key', filled(config('app.key')), true, filled(config('app.key')) ? 'Configured' : 'Missing', 'Use your hosting setup tool to generate the application security key before going live.');
    }

    public function migrationReadiness(?int $pendingMigrations = null): array
    {
        if ($pendingMigrations === null) {
            return $this->check('Database table status', true, false, 'Unknown', 'Pending database tables could not be counted safely; run migrations manually from hosting tools if needed.');
        }

        return $this->check('Database table status', $pendingMigrations === 0, false, "{$pendingMigrations} pending", 'Prepare the database tables from your hosting migration tool before finalizing.');
    }

    public function summary(): array
    {
        return [
            'requirements' => $this->requirements(),
            'permissions' => $this->permissions(),
            'environment' => $this->environment(),
        ];
    }

    public function diagnostics(array $databaseStatus = []): array
    {
        return [
            $this->diagnostic('PHP', PHP_VERSION, version_compare(PHP_VERSION, (string) config('installer.requirements.php_minimum', '8.2.0'), '>=') ? 'pass' : 'fail'),
            $this->diagnostic('Security key', filled(config('app.key')) ? 'Configured' : 'Missing', filled(config('app.key')) ? 'pass' : 'fail'),
            $this->diagnostic('Database connection', ($databaseStatus['connected'] ?? false) ? 'Connected' : 'Not connected', ($databaseStatus['connected'] ?? false) ? 'pass' : 'warning'),
            $this->diagnostic('Pending migrations', ($databaseStatus['pending_migrations_count'] ?? null) === null ? 'Unknown' : (string) $databaseStatus['pending_migrations_count'], ($databaseStatus['pending_migrations_count'] ?? null) === 0 ? 'pass' : 'warning'),
            $this->diagnostic('Queue', filled(config('queue.default')) ? (string) config('queue.default') : 'Missing', filled(config('queue.default')) ? 'pass' : 'warning'),
            $this->diagnostic('Cache', filled(config('cache.default')) ? (string) config('cache.default') : 'Missing', filled(config('cache.default')) ? 'pass' : 'warning'),
            $this->diagnostic('Session', filled(config('session.driver')) ? (string) config('session.driver') : 'Missing', filled(config('session.driver')) ? 'pass' : 'warning'),
            $this->diagnostic('Mail', $this->mailSummary(), $this->mailReady() ? 'pass' : 'warning'),
            $this->diagnostic('Scheduler/cron', (bool) config('standalone.scheduler_monitor.enabled', true) ? 'Monitor enabled' : 'Monitor disabled', 'warning'),
            $this->diagnostic('Backups', (bool) config('backups.enabled', true) ? 'Metadata checks enabled' : 'Disabled', (bool) config('backups.enabled', true) ? 'pass' : 'warning'),
            $this->diagnostic('Updates', (bool) config('updates.enabled', true) ? 'Package review enabled' : 'Disabled', (bool) config('updates.enabled', true) ? 'pass' : 'warning'),
            $this->diagnostic('Safe output', 'Secrets and private paths hidden', 'pass'),
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

    private function writablePathCheck(string $label, string $relativePath, bool $required, string $message): array
    {
        return $this->check($label, File::isWritable(base_path($relativePath)), $required, $relativePath, $message);
    }

    private function mailConfigurationCheck(): array
    {
        return $this->check('Mail configuration', $this->mailReady(), false, $this->mailSummary(), 'Configure mailer and from address before sending live school notifications.');
    }

    private function mailReady(): bool
    {
        $mailer = (string) config('mail.default', 'log');

        return filled($mailer)
            && filled(config('mail.from.address'))
            && ($mailer !== 'smtp' || filled(config('mail.mailers.smtp.host')));
    }

    private function mailSummary(): string
    {
        $mailer = filled(config('mail.default')) ? (string) config('mail.default') : 'Missing';
        $from = filled(config('mail.from.address')) ? 'from configured' : 'from missing';

        return "{$mailer} / {$from}";
    }

    private function diagnostic(string $label, string $value, string $status): array
    {
        return compact('label', 'value', 'status');
    }
}
