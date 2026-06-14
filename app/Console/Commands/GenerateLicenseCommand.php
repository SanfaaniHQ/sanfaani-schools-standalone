<?php

namespace App\Console\Commands;

use App\Services\Licensing\SignedLicenseKeyService;
use Illuminate\Console\Command;
use RuntimeException;

class GenerateLicenseCommand extends Command
{
    protected $signature = 'license:generate
        {--type=annual : License type: trial, annual, lifetime, demo, managed, managed_contract, or white_label}
        {--school= : Customer school name}
        {--domain= : Licensed domain, for example portal.school.com}
        {--starts= : Start date in YYYY-MM-DD format}
        {--expires= : Expiry date in YYYY-MM-DD format}
        {--entitlements= : Comma-separated entitlements, for example standard,white_label,reports}
        {--max-schools=1 : Maximum schools allowed by the license metadata}
        {--max-users= : Optional maximum users allowed by the license metadata}
        {--max-students= : Optional maximum students allowed by the license metadata}
        {--issued-by=Sanfaani : Issuer label}
        {--notes= : Optional non-secret seller notes}';

    protected $description = 'Generate a signed local marketplace license key for a Sanfaani Schools buyer.';

    public function handle(SignedLicenseKeyService $licenses): int
    {
        try {
            $generated = $licenses->generate([
                'type' => $this->option('type'),
                'school' => $this->option('school'),
                'domain' => $this->option('domain'),
                'starts' => $this->option('starts') ?: now()->toDateString(),
                'expires' => $this->option('expires'),
                'entitlements' => $this->option('entitlements'),
                'max_schools' => $this->option('max-schools'),
                'max_users' => $this->option('max-users'),
                'max_students' => $this->option('max-students'),
                'issued_by' => $this->option('issued-by'),
                'notes' => $this->option('notes'),
            ]);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $payload = $generated['payload'];

        $this->line('License type: '.$payload['type']);
        $this->line('School: '.$payload['school']);
        $this->line('Domain: '.$payload['domain']);
        $this->line('Issue date: '.$payload['issued_at']);
        $this->line('Start date: '.$payload['starts_at']);
        $this->line('Expiry date: '.($payload['expires_at'] ?: 'No expiry'));
        $this->line('Entitlements: '.(implode(', ', array_keys(array_filter($payload['entitlements']))) ?: 'None'));
        $this->newLine();
        $this->line('License key:');
        $this->line($generated['license_key']);

        return self::SUCCESS;
    }
}
