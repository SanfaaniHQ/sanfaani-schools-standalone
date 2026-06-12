<?php

namespace App\Services\LiveClasses\Providers;

use App\Contracts\LiveClasses\LiveClassProviderInterface;

abstract class AbstractLiveClassProvider implements LiveClassProviderInterface
{
    public function capabilities(): array
    {
        return [
            'manual_link_supported' => false,
            'auto_create_supported' => false,
            'recording_link_supported' => false,
            'password_supported' => false,
            'requires_credentials' => false,
        ];
    }

    public function requiresCredentials(): bool
    {
        return (bool) ($this->capabilities()['requires_credentials'] ?? false);
    }

    public function supportsManualLink(): bool
    {
        return (bool) ($this->capabilities()['manual_link_supported'] ?? false);
    }

    public function supportsAutoCreate(): bool
    {
        return (bool) ($this->capabilities()['auto_create_supported'] ?? false);
    }

    public function validateManualMeetingUrl(?string $url): bool
    {
        return $this->supportsManualLink() && $this->isHttpUrl($url);
    }

    public function validateRecordingUrl(?string $url): bool
    {
        if (! filled($url)) {
            return true;
        }

        return (bool) ($this->capabilities()['recording_link_supported'] ?? false)
            && $this->isHttpUrl($url);
    }

    /**
     * @param  array<string, bool>  $overrides
     * @return array<string, bool>
     */
    protected function capabilitiesWith(array $overrides): array
    {
        return array_merge($this->defaultCapabilities(), $overrides);
    }

    /**
     * @return array<string, bool>
     */
    private function defaultCapabilities(): array
    {
        return [
            'manual_link_supported' => false,
            'auto_create_supported' => false,
            'recording_link_supported' => false,
            'password_supported' => false,
            'requires_credentials' => false,
        ];
    }

    private function isHttpUrl(?string $url): bool
    {
        if (! filled($url)) {
            return false;
        }

        $url = trim((string) $url);
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array($scheme, ['http', 'https'], true);
    }
}
