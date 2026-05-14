<?php

namespace Tests\Feature\School;

use App\Models\School;
use App\Models\ScratchCardBatch;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SchoolDashboardCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_school_admin_dashboard_handles_generated_scratch_card_batches(): void
    {
        $school = School::create([
            'name' => 'Greenfield Academy',
            'slug' => 'greenfield-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $admin = User::factory()->create(['school_id' => $school->id]);
        Role::findOrCreate('school_admin');
        $admin->assignRole('school_admin');

        UserSchoolRole::create([
            'user_id' => $admin->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        ScratchCardBatch::create([
            'school_id' => $school->id,
            'title' => 'First Term Cards',
            'quantity' => 25,
            'payment_status' => 'paid',
            'status' => 'generated',
        ]);

        $this->actingAs($admin);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ]);

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('School Admin Dashboard')
            ->assertSee('Generated');
    }
}
