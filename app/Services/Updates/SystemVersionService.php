<?php

namespace App\Services\Updates;

use App\Models\SystemVersion;
use Illuminate\Support\Facades\DB;

class SystemVersionService
{
    public function current(): SystemVersion
    {
        $current = SystemVersion::query()
            ->where('is_current', true)
            ->latest('id')
            ->first();

        return $current ?: $this->recordCurrent();
    }

    public function currentVersion(): string
    {
        return $this->current()->version;
    }

    public function recordCurrent(?string $version = null, ?string $channel = null, array $metadata = []): SystemVersion
    {
        $version ??= (string) config('version.version', '1.0.0');
        $channel ??= (string) config('updates.channel', 'stable');

        return DB::transaction(function () use ($version, $channel, $metadata): SystemVersion {
            SystemVersion::query()->where('is_current', true)->update(['is_current' => false]);

            $systemVersion = SystemVersion::query()->updateOrCreate(
                ['version' => $version, 'channel' => $channel],
                [
                    'release_date' => $metadata['release_date'] ?? null,
                    'is_current' => true,
                    'metadata' => $metadata,
                ]
            );

            return $systemVersion->fresh();
        });
    }
}
