<?php

namespace App\Services\LiveClasses\Providers;

use App\Models\LiveClass;

class ManualLiveClassProvider extends AbstractLiveClassProvider
{
    public function key(): string
    {
        return LiveClass::PROVIDER_MANUAL;
    }

    public function label(): string
    {
        return 'Manual link';
    }

    public function description(): string
    {
        return 'Paste an existing meeting link from a school-approved external provider.';
    }

    public function capabilities(): array
    {
        return $this->capabilitiesWith([
            'manual_link_supported' => true,
            'auto_create_supported' => false,
            'recording_link_supported' => true,
            'password_supported' => true,
            'requires_credentials' => false,
        ]);
    }

    public function boundaryNotes(): array
    {
        return [
            'Manual provider is active. Paste the meeting link manually.',
            'Provider automation is not active yet; no OAuth, credentials, API calls, webhooks, or generated rooms are used.',
            'Internet is required. Offline live class is not available.',
        ];
    }
}
