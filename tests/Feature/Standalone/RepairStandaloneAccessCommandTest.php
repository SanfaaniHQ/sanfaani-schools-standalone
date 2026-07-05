<?php

namespace Tests\Feature\Standalone;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RepairStandaloneAccessCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'standalone.product_edition' => 'standalone',
            'sanfaani.deployment.mode' => 'single_school',
        ]);
    }

    public function test_dry_run_reports_mail_permission_without_writing(): void
    {
        $exitCode = Artisan::call('standalone:repair-access', ['--dry-run' => true, '--json' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('"permissions_created"', $output);
        $this->assertStringContainsString('school.mail.manage', $output, $output);

        $this->assertFalse(Permission::query()->where('name', 'school.mail.manage')->exists());
    }

    public function test_command_repairs_dual_access_idempotently_without_assigning_unrelated_users(): void
    {
        $school = School::create([
            'name' => 'Repair Academy',
            'slug' => 'repair-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        Role::findOrCreate('super_admin', 'web');
        $owner = User::factory()->create();
        $owner->assignRole('super_admin');
        $unrelated = User::factory()->create();

        $this->artisan('standalone:repair-access')->assertSuccessful();

        $owner->refresh();
        $this->assertTrue($owner->hasRole('school_admin'));
        $this->assertDatabaseHas('user_school_roles', [
            'user_id' => $owner->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);
        $this->assertDatabaseMissing('user_school_roles', ['user_id' => $unrelated->id]);
        $this->assertTrue(Role::findByName('school_admin')->hasPermissionTo('school.mail.manage'));

        $this->artisan('standalone:repair-access')->assertSuccessful();
        $this->assertDatabaseCount('user_school_roles', 1);
    }

    public function test_ambiguous_installation_admins_require_a_targeted_school_assignment(): void
    {
        $school = School::create([
            'name' => 'Targeted Repair Academy',
            'slug' => 'targeted-repair-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        Role::findOrCreate('super_admin', 'web');
        $first = User::factory()->create();
        $second = User::factory()->create();
        $first->assignRole('super_admin');
        $second->assignRole('super_admin');

        $this->artisan('standalone:repair-access')
            ->expectsOutputToContain('multiple Installation Admins')
            ->assertSuccessful();
        $this->assertDatabaseCount('user_school_roles', 0);

        $this->artisan('standalone:repair-access', [
            '--user' => $first->email,
            '--school' => $school->slug,
        ])->assertSuccessful();

        $this->assertDatabaseHas('user_school_roles', [
            'user_id' => $first->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
        ]);
        $this->assertDatabaseMissing('user_school_roles', ['user_id' => $second->id]);
    }
}
