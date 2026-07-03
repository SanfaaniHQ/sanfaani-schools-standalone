<?php

namespace App\Listeners\Marketing;

use App\Events\LicenseExpiring;
use App\Services\Marketing\MarketingActivityService;
use App\Services\Marketing\SalesTaskService;

class CreateRenewalReminderFromLicense
{
    public function handle(LicenseExpiring $event): void
    {
        if (! (bool) config('sanfaani.license_validation_enabled', false)
            || ! (bool) config('marketing.enabled', true)) {
            return;
        }

        $task = app(SalesTaskService::class)->createRenewalReminder($event->license);

        app(MarketingActivityService::class)->log(
            'license.expiring',
            'License renewal reminder created.',
            school: $event->license->school,
            context: [
                'license_id' => $event->license->id,
                'sales_task_id' => $task?->id,
            ]
        );
    }
}
