<?php

namespace App\Services\Updates;

class UpdateServerClient
{
    public function checkForUpdates(?string $channel = null): array
    {
        return [
            'status' => 'safe_stub',
            'channel' => $channel ?: (string) config('updates.channel', 'stable'),
            'server_configured' => filled(config('updates.server_url')),
            'remote_checks_enabled' => (bool) config('updates.remote_checks_enabled', false),
            'network_request_made' => false,
            'message' => 'External update checks are intentionally disabled in this foundation step.',
        ];
    }

    public function fetchManifest(string $version): array
    {
        return [
            'status' => 'safe_stub',
            'version' => $version,
            'network_request_made' => false,
            'manifest' => [],
            'message' => 'Manifest fetching is reserved for a future safe update delivery layer.',
        ];
    }
}
