<?php

namespace Tests\Feature\Deployment;

use App\Providers\EnvironmentGuardServiceProvider;
use RuntimeException;
use Tests\TestCase;

class EnvironmentGuardDatabaseNameTest extends TestCase
{
    public function test_cpanel_database_name_is_allowed_when_database_name_guard_is_disabled(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'swifarpx_fazportal',
            'sanfaani.database.name_guard.enabled' => false,
            'sanfaani.database.name_guard.required_fragment' => 'sanfaani_schools',
        ]);

        $this->bootEnvironmentGuard();

        $this->assertTrue(true);
    }

    public function test_unsafe_database_name_is_blocked_when_database_name_guard_is_enabled(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'portal_db',
            'sanfaani.database.name_guard.enabled' => true,
            'sanfaani.database.name_guard.required_fragment' => 'sanfaani_schools',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB_DATABASE must contain sanfaani_schools');

        $this->bootEnvironmentGuard();
    }

    public function test_internal_database_name_guard_can_still_be_enabled(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'local_sanfaani_schools_testing',
            'sanfaani.database.name_guard.enabled' => true,
            'sanfaani.database.name_guard.required_fragment' => 'sanfaani_schools',
        ]);

        $this->bootEnvironmentGuard();

        $this->assertTrue(true);
    }

    private function bootEnvironmentGuard(): void
    {
        (new EnvironmentGuardServiceProvider($this->app))->boot();
    }
}
