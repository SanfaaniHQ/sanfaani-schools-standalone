<?php

namespace Tests\Feature\School;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\CurrentSchoolService;
use App\Services\Portals\StudentPortalLinkService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentPortalAccountStageCTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_user_can_be_linked_to_child_profile(): void
    {
        $school = $this->school();
        $parent = $this->portalUser($school, 'parent', 'parent@example.com');
        $student = $this->student($school);

        app(StudentPortalLinkService::class)->attachParentToStudent(
            $parent,
            $student,
            'father',
            true
        );

        $this->assertDatabaseHas('parent_student', [
            'school_id' => $school->id,
            'parent_user_id' => $parent->id,
            'student_id' => $student->id,
            'relationship' => 'father',
            'is_primary' => true,
            'can_view_results' => true,
            'can_view_attendance' => true,
            'can_view_finance' => true,
            'receives_notifications' => true,
        ]);
    }

    public function test_student_user_can_be_linked_to_student_profile(): void
    {
        $school = $this->school();
        $studentUser = $this->portalUser($school, 'student', 'student@example.com');
        $student = $this->student($school);

        app(StudentPortalLinkService::class)->linkStudentUser($studentUser, $student);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'school_id' => $school->id,
            'student_user_id' => $studentUser->id,
        ]);

        $this->assertTrue($studentUser->hasRole('student'));

        $this->assertDatabaseHas('user_school_roles', [
            'user_id' => $studentUser->id,
            'school_id' => $school->id,
            'role_name' => 'student',
            'status' => 'active',
        ]);
    }

    public function test_existing_parent_can_be_linked_to_multiple_children(): void
    {
        $school = $this->school();
        $parent = $this->portalUser($school, 'parent', 'shared.parent@example.com');
        $firstChild = $this->student($school, ['first_name' => 'First']);
        $secondChild = $this->student($school, ['first_name' => 'Second']);

        $links = app(StudentPortalLinkService::class);

        $links->attachParentToStudent($parent, $firstChild, 'mother', true);
        $links->attachParentToStudent($parent, $secondChild, 'mother', false);

        $this->assertDatabaseHas('parent_student', [
            'school_id' => $school->id,
            'parent_user_id' => $parent->id,
            'student_id' => $firstChild->id,
        ]);

        $this->assertDatabaseHas('parent_student', [
            'school_id' => $school->id,
            'parent_user_id' => $parent->id,
            'student_id' => $secondChild->id,
        ]);

        $this->assertSame(2, $parent->children()->wherePivot('school_id', $school->id)->count());
    }

    public function test_parent_dashboard_lists_linked_children(): void
    {
        $school = $this->school();
        $parent = $this->portalUser($school, 'parent', 'dashboard.parent@example.com');

        $firstChild = $this->student($school, [
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
        ]);

        $secondChild = $this->student($school, [
            'first_name' => 'Bala',
            'last_name' => 'Musa',
            'status' => 'graduated',
        ]);

        $links = app(StudentPortalLinkService::class);
        $links->attachParentToStudent($parent, $firstChild, 'guardian', true);
        $links->attachParentToStudent($parent, $secondChild, 'guardian', false);

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'parent');

        $this->actingAs($parent)
            ->get(route('parent.dashboard'))
            ->assertOk()
            ->assertSee('Amina Yusuf')
            ->assertSee('Bala Musa')
            ->assertSee('Graduated / Alumni');
    }

    public function test_student_dashboard_shows_linked_student_profile(): void
    {
        $school = $this->school();
        $studentUser = $this->portalUser($school, 'student', 'student.portal@example.com');

        $student = $this->student($school, [
            'first_name' => 'Student',
            'last_name' => 'Portal',
            'student_user_id' => $studentUser->id,
        ]);

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'student');

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Student Portal')
            ->assertSee($student->admission_number);
    }

    public function test_same_student_user_cannot_be_linked_to_two_students_in_same_school(): void
    {
        $this->expectException(QueryException::class);

        $school = $this->school();
        $studentUser = $this->portalUser($school, 'student', 'duplicate.student@example.com');

        $this->student($school, [
            'student_user_id' => $studentUser->id,
        ]);

        $this->student($school, [
            'first_name' => 'Second',
            'admission_number' => 'ADM-SECOND',
            'student_user_id' => $studentUser->id,
        ]);
    }

    private function school(array $overrides = []): School
    {
        $columns = Schema::getColumnListing('schools');

        $defaults = [
            'name' => 'Test School '.uniqid(),
            'slug' => 'test-school-'.uniqid(),
            'code' => 'SCH'.uniqid(),
            'school_code' => 'SCH'.uniqid(),
            'short_name' => 'Test School',
            'email' => 'school.'.uniqid().'@example.com',
            'contact_email' => 'school.'.uniqid().'@example.com',
            'phone' => '08000000000',
            'contact_phone' => '08000000000',
            'address' => 'Ilorin',
            'city' => 'Ilorin',
            'state' => 'Kwara',
            'country' => 'Nigeria',
            'status' => 'active',
            'is_active' => true,
        ];

        $data = array_intersect_key(
            array_merge($defaults, $overrides),
            array_flip($columns)
        );

        return School::unguarded(fn () => School::query()->create($data));
    }

    private function portalUser(School $school, string $role, string $email): User
    {
        Role::findOrCreate($role);

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

    private function student(School $school, array $overrides = []): Student
    {
        $columns = Schema::getColumnListing('students');

        $defaults = [
            'school_id' => $school->id,
            'school_class_id' => null,
            'admission_number' => 'ADM-'.uniqid(),
            'first_name' => 'Test',
            'middle_name' => null,
            'last_name' => 'Student',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(10)->toDateString(),
            'guardian_name' => 'Guardian User',
            'guardian_phone' => '08000000000',
            'guardian_email' => 'guardian.'.uniqid().'@example.com',
            'address' => 'Ilorin',
            'status' => 'active',
        ];

        $data = array_intersect_key(
            array_merge($defaults, $overrides),
            array_flip($columns)
        );

        return Student::unguarded(fn () => Student::query()->create($data));
    }

    private function mockSchoolContext(School $school, string $roleContext): void
    {
        $this->mock(CurrentSchoolService::class, function ($mock) use ($school, $roleContext) {
            $mock->shouldReceive('get')->withAnyArgs()->andReturn($school);
            $mock->shouldReceive('roleContext')->withAnyArgs()->andReturn($roleContext);
        });
    }
}
