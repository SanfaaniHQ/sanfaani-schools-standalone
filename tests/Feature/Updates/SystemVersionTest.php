<?php

namespace Tests\Feature\Updates;

use App\Models\SystemVersion;
use App\Services\Updates\SystemVersionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_system_version_can_be_recorded(): void
    {
        config([
            'version.version' => '7.0.0',
            'updates.channel' => 'stable',
        ]);

        $version = app(SystemVersionService::class)->recordCurrent(metadata: [
            'release_date' => '2026-05-25',
        ]);

        $this->assertSame('7.0.0', $version->version);
        $this->assertSame('stable', $version->channel);
        $this->assertTrue($version->is_current);
        $this->assertDatabaseHas('system_versions', [
            'version' => '7.0.0',
            'channel' => 'stable',
            'is_current' => true,
        ]);

        app(SystemVersionService::class)->recordCurrent('7.0.1', 'security');

        $this->assertSame(1, SystemVersion::where('is_current', true)->count());
        $this->assertSame('7.0.1', app(SystemVersionService::class)->currentVersion());
    }
}
