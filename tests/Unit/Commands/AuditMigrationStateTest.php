<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\AuditMigrationState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditMigrationStateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test command executes successfully
     */
    public function test_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('migration:audit');

        $this->assertEquals(0, $exitCode);
    }

    /**
     * Test command detects no inconsistencies on fresh database
     */
    public function test_detects_no_inconsistencies_on_fresh_database(): void
    {
        Artisan::call('migration:audit');
        $output = Artisan::output();

        $this->assertStringContainsString('No migration state inconsistencies detected', $output);
    }

    /**
     * Test command detects table without migration record
     */
    public function test_detects_table_without_migration_record(): void
    {
        // Create a test table manually
        Schema::create('test_audit_table', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Artisan::call('migration:audit');
        $output = Artisan::output();

        // Should detect the table exists but no migration recorded
        $this->assertStringContainsString('Tables exist but migrations not recorded', $output);

        // Clean up
        Schema::dropIfExists('test_audit_table');
    }

    /**
     * Test command detects orphaned migration record
     */
    public function test_detects_orphaned_migration_record(): void
    {
        // Insert a fake migration record for a non-existent table
        DB::table('migrations')->insert([
            'migration' => '2024_01_01_000000_create_nonexistent_table',
            'batch' => 1,
        ]);

        Artisan::call('migration:audit');
        $output = Artisan::output();

        // Should detect migration record without corresponding table
        $this->assertStringContainsString('Migration records exist but tables do not', $output);

        // Clean up
        DB::table('migrations')
            ->where('migration', '2024_01_01_000000_create_nonexistent_table')
            ->delete();
    }

    /**
     * Test command signature is correct
     */
    public function test_command_signature_is_correct(): void
    {
        $command = new AuditMigrationState;

        $this->assertEquals('migration:audit', $command->getName());
    }

    /**
     * Test command description is correct
     */
    public function test_command_description_is_correct(): void
    {
        $command = new AuditMigrationState;

        $this->assertEquals('Audit migration state and detect inconsistencies', $command->getDescription());
    }
}
