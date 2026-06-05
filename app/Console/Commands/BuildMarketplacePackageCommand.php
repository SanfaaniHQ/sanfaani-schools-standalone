<?php

namespace App\Console\Commands;

use App\Services\Marketplace\MarketplacePackageBuilder;
use Illuminate\Console\Command;
use InvalidArgumentException;
use RuntimeException;

class BuildMarketplacePackageCommand extends Command
{
    protected $signature = 'marketplace:build-package
        {--profile=technical : Package profile: technical, cpanel_ready, or managed_handover}
        {--dry-run : Write a manifest preview without creating a ZIP archive}';

    protected $description = 'Build a safe marketplace standalone package ZIP or dry-run manifest.';

    public function handle(MarketplacePackageBuilder $builder): int
    {
        $profile = (string) $this->option('profile');
        $dryRun = (bool) $this->option('dry-run');

        try {
            $manifest = $builder->build($profile, $dryRun);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach ($manifest['warnings'] as $warning) {
            $this->warn($warning['message']);
        }

        $this->line('Profile: '.$manifest['profile']);
        $this->line('Manifest: '.$manifest['manifest_path']);

        if ($dryRun) {
            $this->info('Marketplace package dry run complete. No ZIP was created.');

            return self::SUCCESS;
        }

        $this->line('ZIP: '.$manifest['zip_path']);
        $this->info('Marketplace package created successfully.');

        return self::SUCCESS;
    }
}
