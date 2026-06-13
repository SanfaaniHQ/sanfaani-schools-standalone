<?php

namespace Tests\Feature\Standalone;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StandaloneStatusCommandMissingDatabaseTest extends TestCase
{
    public function test_standalone_status_command_handles_missing_database_file(): void
    {
        $missingDatabase = database_path('missing-standalone-status.sqlite');
        $originalDatabase = config('database.connections.sqlite.database');

        File::delete($missingDatabase);

        try {
            config(['database.connections.sqlite.database' => $missingDatabase]);
            DB::purge('sqlite');

            $this->artisan('standalone:status')
                ->expectsOutputToContain('Pending outbox items: tables not migrated')
                ->assertExitCode(Command::SUCCESS);
        } finally {
            config(['database.connections.sqlite.database' => $originalDatabase]);
            DB::purge('sqlite');
            File::delete($missingDatabase);
        }
    }
}
