<?php

namespace Tests\Feature\School;

use App\Models\School;
use App\Models\SchoolFeatureSetting;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\CurrentSchoolService;
use App\Services\SchoolRoleFeatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StageFRolesPermissionsFeatureControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_switch_between_assigned_school_roles(): void
    {
        $school = $this->school();
        $user = $this->portalUser($school, 'school_admin', 'stage.f.switch@example.com');
        $user->assignRole('teacher');

        UserSchoolRole::query()->create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'teacher',
            'status' => 'active',
        ]);

        $this->withoutMiddleware();

        $this->actingAs($user)
            ->post(route('role-context.switch'), [
                'school_id' => $school->id,
                'role_name' => 'teacher',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertSame($school->id, session('active_school_id'));
        $this->assertSame('teacher', session('active_role_context'));
        $this->assertSame('teacher', session('tenant.role_name'));
    }

    public function test_school_admin_can_update_feature_controls_by_role(): void
    {
        $school = $this->school();
        $admin = $this->portalUser($school, 'school_admin', 'stage.f.admin@example.com');

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'school_admin');

        $this->actingAs($admin)
            ->post(route('school.feature-control.update'), [
                'features' => [
                    'parent' => [
                        'portal.conversations' => '1',
                        'teacher.reviews' => '1',
                    ],
                    'student' => [
                        'portal.conversations' => '1',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('school_feature_settings', [
            'school_id' => $school->id,
            'role_name' => 'parent',
            'feature_key' => 'portal.conversations',
            'enabled' => true,
        ]);

        $this->assertDatabaseHas('school_feature_settings', [
            'school_id' => $school->id,
            'role_name' => 'parent',
            'feature_key' => 'finance.manage',
            'enabled' => false,
        ]);

        $this->assertTrue(app(SchoolRoleFeatureService::class)->isEnabled($school->id, 'parent', 'portal.conversations'));
        $this->assertFalse(app(SchoolRoleFeatureService::class)->isEnabled($school->id, 'parent', 'finance.manage'));
    }

    public function test_school_admin_can_update_role_permissions(): void
    {
        $school = $this->school();
        $admin = $this->portalUser($school, 'school_admin', 'stage.f.permissions@example.com');

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'school_admin');

        $this->actingAs($admin)
            ->post(route('school.role-permissions.update'), [
                'role_name' => 'teacher',
                'permissions' => [
                    'portal.conversations',
                    'lms.manage',
                ],
            ])
            ->assertRedirect();

        $role = Role::findByName('teacher');

        $this->assertTrue($role->hasPermissionTo('portal.conversations'));
        $this->assertTrue($role->hasPermissionTo('lms.manage'));
        $this->assertFalse($role->hasPermissionTo('finance.manage'));

        $this->assertTrue(Permission::query()->where('name', 'portal.conversations')->exists());
    }

    public function test_feature_service_returns_default_features_without_overrides(): void
    {
        $school = $this->school();

        $features = app(SchoolRoleFeatureService::class)->getFeatures($school->id, 'school_admin');

        $this->assertTrue($features['school.profile.manage']['enabled']);
        $this->assertTrue($features['communication.logs.view']['enabled']);
        $this->assertFalse($features['teacher.reviews']['enabled'] === false && $features['teacher.reviews']['default_enabled'] === true);
    }

    private function school(array $overrides = []): School
    {
        $columns = Schema::getColumnListing('schools');

        $defaults = [
            'name' => 'Stage F School '.uniqid(),
            'slug' => 'stage-f-school-'.uniqid(),
            'code' => 'SF'.uniqid(),
            'school_code' => 'SF'.uniqid(),
            'short_name' => 'Stage F',
            'email' => 'school.'.uniqid().'@example.com',
            'contact_email' => 'school.'.uniqid().'@example.com',
            'phone' => '08000000000',
            'contact_phone' => '08000000000',
            'address' => 'Ilorin',
            'city' => 'Ilorin',
            'state' => 'Kwara',
            'country' => 'Nigeria',
            'status' => 'active',
            'subscription_status' => 'active',
            'is_active' => true,
        ];

        $data = array_intersect_key(array_merge($defaults, $overrides), array_flip($columns));

        return School::unguarded(fn () => School::query()->create($data));
    }

    private function portalUser(School $school, string $role, string $email): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => $email,
        ]);

        $user->assignRole($role);

        UserSchoolRole::query()->updateOrCreate([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
        ], [
            'status' => 'active',
            'assigned_by' => null,
        ]);

        return $user;
    }

    private function mockSchoolContext(School $school, string $roleContext): void
    {
        $this->mock(CurrentSchoolService::class, function ($mock) use ($school, $roleContext) {
            $mock->shouldReceive('get')->withAnyArgs()->andReturn($school);
            $mock->shouldReceive('roleContext')->withAnyArgs()->andReturn($roleContext);
            $mock->shouldReceive('inSupportMode')->withAnyArgs()->andReturn(false);
        });
    }
}
