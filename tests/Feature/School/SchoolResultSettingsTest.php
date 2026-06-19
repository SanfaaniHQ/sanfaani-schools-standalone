<?php

namespace Tests\Feature\School;

use App\Models\GradingScale;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\ResultGradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SchoolResultSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'accountant', 'parent', 'student'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_school_admin_can_view_and_update_result_settings(): void
    {
        [$school, $user] = $this->schoolAndUser('school_admin');
        $this->actAsSchoolRole($user, $school, 'school_admin');

        $this->get(route('school.result-system.index'))
            ->assertOk()
            ->assertSee('School Result Settings')
            ->assertSee('Pass mark');

        $this->patch(route('school.result-system.update'), [
            'pass_mark' => 50,
            'maximum_score' => 100,
            'ca_max_score' => 40,
            'exam_max_score' => 60,
            'default_result_type' => 'term_result',
            'require_all_subjects' => '1',
            'show_positions' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('school_result_settings', [
            'school_id' => $school->id,
            'pass_mark' => 50,
            'maximum_score' => 100,
            'ca_max_score' => 40,
            'exam_max_score' => 60,
            'require_all_subjects' => true,
            'show_positions' => false,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'action' => 'school_result_settings_updated',
        ]);
    }

    public function test_result_settings_pass_mark_controls_school_grading_pass_status(): void
    {
        [$school, $user] = $this->schoolAndUser('school_admin');
        $school->resultSetting()->create([
            'pass_mark' => 50,
            'maximum_score' => 100,
            'default_result_type' => 'term_result',
            'require_all_subjects' => true,
            'show_positions' => true,
            'updated_by' => $user->id,
        ]);
        GradingScale::create([
            'school_id' => $school->id,
            'name' => 'Average',
            'min_score' => 40,
            'max_score' => 59,
            'grade' => 'C',
            'remark' => 'Average',
            'is_pass' => true,
            'status' => 'active',
        ]);

        $grading = app(ResultGradingService::class);

        $this->assertSame('C', $grading->calculate($school, 45)['grade']);
        $this->assertFalse($grading->calculate($school, 45)['is_pass']);
        $this->assertTrue($grading->calculate($school, 55)['is_pass']);
    }

    public function test_result_settings_reject_pass_mark_above_maximum_score(): void
    {
        [$school, $user] = $this->schoolAndUser('school_admin');
        $this->actAsSchoolRole($user, $school, 'school_admin');

        $this->from(route('school.result-system.index'))
            ->patch(route('school.result-system.update'), [
                'pass_mark' => 120,
                'maximum_score' => 100,
                'default_result_type' => 'term_result',
                'require_all_subjects' => '1',
                'show_positions' => '1',
            ])
            ->assertRedirect(route('school.result-system.index'))
            ->assertSessionHasErrors('pass_mark');
    }

    private function schoolAndUser(string $role): array
    {
        $id = fake()->unique()->numberBetween(1, 999999);
        $school = School::create([
            'name' => 'Sanfaani Result Settings '.$id,
            'slug' => 'sanfaani-result-settings-'.$id,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => fake()->unique()->safeEmail(),
        ]);
        $user->assignRole($role);
        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);

        return [$school, $user];
    }

    private function actAsSchoolRole(User $user, School $school, string $role): void
    {
        $this->actingAs($user);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }
}
