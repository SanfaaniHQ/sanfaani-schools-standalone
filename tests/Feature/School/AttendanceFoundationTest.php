<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendanceRecord;
use App\Models\TeacherClassAssignment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Attendance\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttendanceFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'accountant'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_attendance_routes_require_authentication(): void
    {
        $this->get(route('school.attendance.index'))
            ->assertRedirect(route('login'));
    }

    public function test_school_admin_can_access_attendance_dashboard(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.attendance.index'))
            ->assertOk()
            ->assertSee('Attendance')
            ->assertSee('Online attendance foundation')
            ->assertSee('Browser offline attendance capture is not implemented');
    }

    public function test_teacher_can_mark_attendance_for_assigned_class(): void
    {
        [$school, $teacher, $class, $session, $term] = $this->attendanceContext('teacher');
        $student = $this->createStudent($school, $class, 'ATT-001', 'Ada');

        TeacherClassAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'role_type' => 'class_teacher',
            'status' => 'active',
        ]);

        $this->actAsSchoolRole($teacher, $school, 'teacher');

        $this->post(route('school.attendance.classes.store', $class), [
            'attendance_date' => '2026-06-11',
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'records' => [
                ['student_id' => $student->id, 'status' => 'present', 'note' => 'Morning roll call'],
            ],
        ])->assertRedirect(route('school.attendance.classes.show', ['class' => $class, 'date' => '2026-06-11']));

        $this->assertDatabaseHas('student_attendance_records', [
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'student_id' => $student->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'recorded_by' => $teacher->id,
            'attendance_date' => '2026-06-11 00:00:00',
            'status' => 'present',
            'source' => 'web',
        ]);
    }

    public function test_unassigned_teacher_and_unauthorized_roles_cannot_manage_attendance(): void
    {
        [$school, $teacher, $class] = $this->attendanceContext('teacher');
        $student = $this->createStudent($school, $class, 'ATT-002', 'Bala');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        $payload = [
            'attendance_date' => '2026-06-11',
            'records' => [
                ['student_id' => $student->id, 'status' => 'present'],
            ],
        ];

        $this->post(route('school.attendance.classes.store', $class), $payload)
            ->assertForbidden();

        $accountant = $this->createUserForSchool($school, 'accountant');
        $this->actAsSchoolRole($accountant, $school, 'accountant');

        $this->post(route('school.attendance.classes.store', $class), $payload)
            ->assertForbidden();

        $this->assertDatabaseCount('student_attendance_records', 0);
    }

    public function test_duplicate_attendance_submission_updates_instead_of_duplicating(): void
    {
        [$school, $admin, $class] = $this->attendanceContext('school_admin');
        $student = $this->createStudent($school, $class, 'ATT-003', 'Chidi');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->post(route('school.attendance.classes.store', $class), [
            'attendance_date' => '2026-06-11',
            'records' => [
                ['student_id' => $student->id, 'status' => 'present'],
            ],
        ])->assertSessionHasNoErrors();

        $this->post(route('school.attendance.classes.store', $class), [
            'attendance_date' => '2026-06-11',
            'records' => [
                ['student_id' => $student->id, 'status' => 'late', 'note' => 'Arrived after assembly'],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('student_attendance_records', 1);
        $this->assertDatabaseHas('student_attendance_records', [
            'student_id' => $student->id,
            'attendance_date' => '2026-06-11 00:00:00',
            'status' => 'late',
            'note' => 'Arrived after assembly',
        ]);
    }

    public function test_invalid_status_is_rejected(): void
    {
        [$school, $admin, $class] = $this->attendanceContext('school_admin');
        $student = $this->createStudent($school, $class, 'ATT-004', 'Dara');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->from(route('school.attendance.classes.show', $class))
            ->post(route('school.attendance.classes.store', $class), [
                'attendance_date' => '2026-06-11',
                'records' => [
                    ['student_id' => $student->id, 'status' => 'missing'],
                ],
            ])
            ->assertRedirect(route('school.attendance.classes.show', $class))
            ->assertSessionHasErrors('records.0.status');

        $this->assertDatabaseCount('student_attendance_records', 0);
    }

    public function test_attendance_records_are_school_scoped(): void
    {
        [$school, $admin, $class] = $this->attendanceContext('school_admin');
        [$otherSchool, , $otherClass] = $this->attendanceContext('school_admin');
        $otherStudent = $this->createStudent($otherSchool, $otherClass, 'OTHER-001', 'Efe');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.attendance.classes.show', $otherClass))
            ->assertForbidden();

        $this->post(route('school.attendance.classes.store', $class), [
            'attendance_date' => '2026-06-11',
            'records' => [
                ['student_id' => $otherStudent->id, 'status' => 'present'],
            ],
        ])->assertSessionHasErrors('records');

        $this->assertDatabaseCount('student_attendance_records', 0);
    }

    public function test_attendance_summary_counts_all_statuses(): void
    {
        [$school, $admin, $class] = $this->attendanceContext('school_admin');
        $students = collect([
            $this->createStudent($school, $class, 'ATT-005', 'Fati'),
            $this->createStudent($school, $class, 'ATT-006', 'Gani'),
            $this->createStudent($school, $class, 'ATT-007', 'Hauwa'),
            $this->createStudent($school, $class, 'ATT-008', 'Ife'),
        ]);
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->post(route('school.attendance.classes.store', $class), [
            'attendance_date' => '2026-06-11',
            'records' => $students->zip(['present', 'absent', 'late', 'excused'])
                ->map(fn ($pair) => ['student_id' => $pair[0]->id, 'status' => $pair[1]])
                ->all(),
        ])->assertSessionHasNoErrors();

        $summary = app(AttendanceService::class)->dailyClassSummary($school, $class, '2026-06-11');

        $this->assertSame([
            'present' => 1,
            'absent' => 1,
            'late' => 1,
            'excused' => 1,
        ], $summary['counts']);
    }

    public function test_student_attendance_history_page_lists_records(): void
    {
        [$school, $admin, $class] = $this->attendanceContext('school_admin');
        $student = $this->createStudent($school, $class, 'ATT-009', 'Jumoke');

        StudentAttendanceRecord::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'student_id' => $student->id,
            'recorded_by' => $admin->id,
            'attendance_date' => '2026-06-11',
            'status' => 'excused',
            'source' => 'web',
        ]);

        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.attendance.students.show', $student))
            ->assertOk()
            ->assertSee('Jumoke Student Attendance')
            ->assertSee('Excused')
            ->assertSee('11 Jun 2026');
    }

    public function test_attendance_actions_are_audit_logged(): void
    {
        [$school, $admin, $class] = $this->attendanceContext('school_admin');
        $student = $this->createStudent($school, $class, 'ATT-010', 'Kemi');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->post(route('school.attendance.classes.store', $class), [
            'attendance_date' => '2026-06-11',
            'records' => [
                ['student_id' => $student->id, 'status' => 'present'],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'user_id' => $admin->id,
            'action' => 'attendance_recorded',
            'auditable_type' => StudentAttendanceRecord::class,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'user_id' => $admin->id,
            'action' => 'bulk_class_attendance_submitted',
            'auditable_type' => SchoolClass::class,
            'auditable_id' => $class->id,
        ]);
    }

    public function test_attendance_navigation_is_role_aware_and_offline_boundary_is_clear(): void
    {
        config([
            'standalone.product_edition' => 'standalone',
            'standalone.offline_mode' => 'local_first',
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        [$school, $admin] = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Attendance')
            ->assertSee('Attendance foundation')
            ->assertSee('Offline attendance capture')
            ->assertSee('Browser offline attendance capture is not complete')
            ->assertDontSee('Offline attendance capture: Available');

        $officer = $this->createUserForSchool($school, 'result_officer');
        $this->actAsSchoolRole($officer, $school, 'result_officer');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertDontSee('Attendance Foundation')
            ->assertDontSee('Attendance Dashboard');
    }

    private function attendanceContext(string $role): array
    {
        [$school, $user] = $this->schoolContext($role);
        $class = $this->createClass($school, 'JSS 1', fake()->unique()->lexify('??'));
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => fake()->unique()->bothify('2026/####'),
            'is_active' => true,
            'status' => 'active',
        ]);
        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'is_active' => true,
            'status' => 'active',
        ]);

        return [$school, $user, $class, $session, $term];
    }

    private function schoolContext(string $role): array
    {
        $school = School::create([
            'name' => fake()->unique()->company().' Academy',
            'slug' => fake()->unique()->slug(),
            'email' => 'school@example.test',
            'phone' => '08030000000',
            'address' => 'Ilorin',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        return [$school, $this->createUserForSchool($school, $role)];
    }

    private function createClass(School $school, string $name, string $section): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $school->id,
            'name' => $name,
            'section' => $section,
            'status' => 'active',
        ]);
    }

    private function createStudent(School $school, SchoolClass $class, string $admissionNumber, string $firstName): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'first_name' => $firstName,
            'last_name' => 'Student',
            'status' => 'active',
        ]);
    }

    private function createUserForSchool(School $school, string $role): User
    {
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole($role);

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);

        return $user;
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
