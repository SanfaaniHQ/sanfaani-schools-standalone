<?php

namespace App\Services\LiveClasses\Providers;

abstract class FutureApiLiveClassProvider extends AbstractLiveClassProvider
{
    protected const KEY = '';

    protected const LABEL = '';

    public function key(): string
    {
        return static::KEY;
    }

    public function label(): string
    {
        return static::LABEL;
    }

    public function description(): string
    {
        return static::LABEL.' provider metadata for future automation. This provider is not enabled for API-created rooms yet.';
    }

    public function capabilities(): array
    {
        return $this->capabilitiesWith([
            'manual_link_supported' => true,
            'auto_create_supported' => false,
            'recording_link_supported' => true,
            'password_supported' => true,
            'requires_credentials' => true,
        ]);
    }

    public function boundaryNotes(): array
    {
        return [
            static::LABEL.' API automation is currently disabled.',
            'OAuth, provider credentials, token refresh, webhooks, generated rooms, and external API calls are not active.',
            'Manual meeting links remain required until a later automation stage safely adds credentials and provider APIs.',
        ];
    }
}
