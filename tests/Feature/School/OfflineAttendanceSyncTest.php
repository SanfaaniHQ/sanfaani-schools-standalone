<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\AttendanceOfflineSyncReceipt;
use App\Models\AuditLog;
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
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OfflineAttendanceSyncTest extends TestCase
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

        config([
            'standalone.product_edition' => 'standalone',
            'standalone.pwa_offline.capture_enabled' => false,
            'standalone.pwa_offline.sync_enabled' => false,
            'standalone.pwa_offline.allowed_modules' => ['attendance'],
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);
    }

    public function test_offline_sync_endpoint_requires_authentication(): void
    {
        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [],
        ])->assertUnauthorized();
    }

    public function test_offline_sync_monitor_requires_authentication(): void
    {
        $this->get(route('school.attendance.offline-sync-monitor'))
            ->assertRedirect(route('login'));
    }

    public function test_offline_sync_monitor_forbids_unauthorized_roles(): void
    {
        [$school, $officer] = $this->attendanceContext('result_officer');
        $this->actAsSchoolRole($officer, $school, 'result_officer');

        $this->get(route('school.attendance.offline-sync-monitor'))
            ->assertForbidden();
    }

    public function test_offline_sync_endpoint_is_disabled_by_default(): void
    {
        [$school, $admin, $class, , , $student] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student)],
        ])
            ->assertForbidden()
            ->assertJsonPath('code', 'offline_attendance_disabled');

        $this->assertDatabaseCount('student_attendance_records', 0);
    }

    public function test_offline_sync_works_when_enabled_and_audits_browser_source(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, $class, $session, $term, $student] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $uuid = (string) Str::uuid();

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student, $uuid, [
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
            ])],
        ])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'synced')
            ->assertJsonPath('results.0.accepted', true)
            ->assertJsonPath('summary.synced', 1);

        $record = StudentAttendanceRecord::query()->firstOrFail();
        $this->assertSame('browser_offline', $record->source);
        $this->assertSame($uuid, $record->metadata['offline_capture']['client_uuid']);
        $this->assertDatabaseHas('attendance_offline_sync_receipts', [
            'school_id' => $school->id,
            'client_uuid' => $uuid,
            'attendance_record_id' => $record->id,
            'result_status' => 'synced',
        ]);
        $this->assertDatabaseHas('standalone_sync_logs', [
            'direction' => 'browser_push',
            'status' => 'processed',
        ]);

        $audit = AuditLog::query()
            ->where('school_id', $school->id)
            ->where('action', 'attendance_recorded')
            ->firstOrFail();

        $this->assertSame('browser_offline', $audit->metadata['source']);
        $this->assertSame($uuid, $audit->metadata['client_uuid']);
    }

    public function test_offline_sync_returns_per_record_validation_failure_for_invalid_status(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, $class, , , $student] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student, null, ['status' => 'missing'])],
        ])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'failed_validation')
            ->assertJsonPath('results.0.accepted', false);

        $this->assertDatabaseCount('student_attendance_records', 0);
    }

    public function test_offline_sync_rejects_invalid_class(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, , , , $student] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $payload = $this->payloadId(999999, $student->id);

        $this->postJson(route('school.attendance.offline-sync'), ['records' => [$payload]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'failed_permission');
    }

    public function test_offline_sync_rejects_student_outside_selected_class(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, $class] = $this->attendanceContext('school_admin');
        $otherClass = $this->createClass($school, 'JSS 2');
        $student = $this->createStudent($school, $otherClass, 'OFF-OTHER');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student)],
        ])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'failed_validation');
    }

    public function test_teacher_can_sync_only_assigned_class_attendance(): void
    {
        $this->enableOfflineAttendance();
        [$school, $teacher, $class, $session, $term, $student] = $this->attendanceContext('teacher');

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

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student, null, [
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
            ])],
        ])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'synced');
    }

    public function test_teacher_cannot_sync_unassigned_class_attendance(): void
    {
        $this->enableOfflineAttendance();
        [$school, $teacher, $class, $session, $term, $student] = $this->attendanceContext('teacher');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student, null, [
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
            ])],
        ])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'failed_permission');

        $this->assertDatabaseCount('student_attendance_records', 0);
    }

    public function test_cross_school_sync_is_blocked(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin] = $this->attendanceContext('school_admin');
        [, , $otherClass, , , $otherStudent] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($otherClass, $otherStudent)],
        ])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'failed_permission');

        $this->assertDatabaseCount('student_attendance_records', 0);
    }

    public function test_duplicate_client_uuid_is_skipped_and_changed_payload_conflicts(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, $class, , , $student] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $uuid = (string) Str::uuid();
        $payload = $this->payload($class, $student, $uuid);

        $this->postJson(route('school.attendance.offline-sync'), ['records' => [$payload]])
            ->assertJsonPath('results.0.status', 'synced');

        $this->postJson(route('school.attendance.offline-sync'), ['records' => [$payload]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'skipped_duplicate')
            ->assertJsonPath('results.0.accepted', true);

        $changed = [...$payload, 'status' => 'absent'];
        $this->postJson(route('school.attendance.offline-sync'), ['records' => [$changed]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'conflict')
            ->assertJsonPath('results.0.accepted', false);

        $this->assertDatabaseCount('student_attendance_records', 1);
        $this->assertDatabaseCount('attendance_offline_sync_receipts', 1);
        $this->assertSame('present', StudentAttendanceRecord::query()->firstOrFail()->status);
    }

    public function test_school_admin_can_view_offline_sync_monitor_receipts_with_safe_boundary_wording(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, $class, , , $student] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $uuid = (string) Str::uuid();

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student, $uuid, [
                'note' => 'sync-secret RuntimeException: payload-secret-marker at C:\secret\Sync.php:42',
            ])],
        ])->assertJsonPath('results.0.status', 'synced');

        $this->get(route('school.attendance.offline-sync-monitor'))
            ->assertOk()
            ->assertSee('Offline Attendance Sync Monitor')
            ->assertSee('Server receipts')
            ->assertSee($class->name)
            ->assertSee(substr($uuid, 0, 15))
            ->assertSee('Browser-local pending records appear here only after the browser submits them for sync')
            ->assertSee('Other modules require an active connection')
            ->assertDontSee('sync-secret')
            ->assertDontSee('RuntimeException: payload-secret-marker')
            ->assertDontSee('C:\secret\Sync.php:42')
            ->assertDontSee('payload-secret-marker')
            ->assertDontSee($student->admission_number)
            ->assertDontSee($student->fullName());
    }

    public function test_offline_sync_monitor_summary_counts_server_known_statuses(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, $class, , , $student] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $uuid = (string) Str::uuid();
        $payload = $this->payload($class, $student, $uuid);

        $this->postJson(route('school.attendance.offline-sync'), ['records' => [$payload]])
            ->assertJsonPath('results.0.status', 'synced');
        $this->postJson(route('school.attendance.offline-sync'), ['records' => [$payload]])
            ->assertJsonPath('results.0.status', 'skipped_duplicate');
        $this->postJson(route('school.attendance.offline-sync'), ['records' => [[...$payload, 'status' => 'absent']]])
            ->assertJsonPath('results.0.status', 'conflict');
        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student, null, ['status' => 'missing'])],
        ])->assertJsonPath('results.0.status', 'failed_validation');
        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payloadId(999999, $student->id)],
        ])->assertJsonPath('results.0.status', 'failed_permission');

        $summary = app(AttendanceService::class)->offlineSyncMonitor($school, $admin)['summary'];

        $this->assertSame(1, $summary['receipt_total']);
        $this->assertSame(1, $summary['synced_count']);
        $this->assertSame(1, $summary['skipped_duplicate_count']);
        $this->assertSame(1, $summary['conflict_count']);
        $this->assertSame(1, $summary['failed_validation_count']);
        $this->assertSame(1, $summary['failed_permission_count']);
    }

    public function test_offline_sync_monitor_filters_by_status_class_and_date_range(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, $class, , , $student] = $this->attendanceContext('school_admin');
        $otherClass = $this->createClass($school, 'JSS 2');
        $otherStudent = $this->createStudent($school, $otherClass, 'OFF-FILTER-2');
        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $syncedUuid = (string) Str::uuid();
        $conflictUuid = (string) Str::uuid();
        $otherSyncedUuid = (string) Str::uuid();

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student, $syncedUuid)],
        ])->assertJsonPath('results.0.status', 'synced');
        AttendanceOfflineSyncReceipt::query()
            ->where('client_uuid', $syncedUuid)
            ->update([
                'processed_at' => '2026-06-10 08:00:00',
                'created_at' => '2026-06-10 08:00:00',
                'updated_at' => '2026-06-10 08:00:00',
            ]);

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($otherClass, $otherStudent, $otherSyncedUuid, [
                'attendance_date' => '2026-06-12',
                'status' => 'late',
            ])],
        ])->assertJsonPath('results.0.status', 'synced');
        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($otherClass, $otherStudent, $conflictUuid, [
                'attendance_date' => '2026-06-12',
                'status' => 'absent',
            ])],
        ])->assertJsonPath('results.0.status', 'conflict');
        AttendanceOfflineSyncReceipt::query()
            ->where('client_uuid', $conflictUuid)
            ->update([
                'processed_at' => '2026-06-12 08:00:00',
                'created_at' => '2026-06-12 08:00:00',
                'updated_at' => '2026-06-12 08:00:00',
            ]);

        $this->get(route('school.attendance.offline-sync-monitor', ['status' => 'synced']))
            ->assertOk()
            ->assertSee(substr($syncedUuid, 0, 15))
            ->assertDontSee(substr($conflictUuid, 0, 15));

        $this->get(route('school.attendance.offline-sync-monitor', ['school_class_id' => $otherClass->id]))
            ->assertOk()
            ->assertSee($otherClass->name)
            ->assertDontSee(substr($syncedUuid, 0, 15));

        $this->get(route('school.attendance.offline-sync-monitor', [
            'date_from' => '2026-06-12',
            'date_to' => '2026-06-12',
        ]))
            ->assertOk()
            ->assertSee(substr($conflictUuid, 0, 15))
            ->assertDontSee(substr($syncedUuid, 0, 15));
    }

    public function test_teacher_monitor_visibility_is_limited_to_own_or_assigned_class_receipts(): void
    {
        $this->enableOfflineAttendance();
        [$school, $teacher, $class, $session, $term, $student] = $this->attendanceContext('teacher');
        $otherClass = $this->createClass($school, 'JSS 3');
        $otherStudent = $this->createStudent($school, $otherClass, 'OFF-TEACHER-HIDDEN');
        $teacherUuid = (string) Str::uuid();
        $adminUuid = (string) Str::uuid();

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
        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student, $teacherUuid, [
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
            ])],
        ])->assertJsonPath('results.0.status', 'synced');

        $admin = $this->createUserForSchool($school, 'school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($otherClass, $otherStudent, $adminUuid)],
        ])->assertJsonPath('results.0.status', 'synced');

        $this->actAsSchoolRole($teacher, $school, 'teacher');
        $this->get(route('school.attendance.offline-sync-monitor'))
            ->assertOk()
            ->assertSee(substr($teacherUuid, 0, 15))
            ->assertDontSee(substr($adminUuid, 0, 15))
            ->assertDontSee($otherClass->name);

        $this->get(route('school.attendance.offline-sync-monitor', ['school_class_id' => $otherClass->id]))
            ->assertForbidden();
    }

    public function test_cross_school_offline_sync_receipts_are_not_visible_in_monitor(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, $class, , , $student] = $this->attendanceContext('school_admin');
        [$otherSchool, $otherAdmin, $otherClass, , , $otherStudent] = $this->attendanceContext('school_admin');
        $uuid = (string) Str::uuid();
        $otherUuid = (string) Str::uuid();

        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student, $uuid)],
        ])->assertJsonPath('results.0.status', 'synced');

        $this->actAsSchoolRole($otherAdmin, $otherSchool, 'school_admin');
        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($otherClass, $otherStudent, $otherUuid)],
        ])->assertJsonPath('results.0.status', 'synced');

        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $this->get(route('school.attendance.offline-sync-monitor'))
            ->assertOk()
            ->assertSee(substr($uuid, 0, 15))
            ->assertDontSee(substr($otherUuid, 0, 15))
            ->assertDontSee($otherSchool->name);
    }

    public function test_dashboard_and_standalone_status_show_high_level_offline_sync_health(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, $class, , , $student] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student)],
        ])->assertJsonPath('results.0.status', 'synced');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Offline attendance sync')
            ->assertSee('1 receipts');

        $owner = User::factory()->create();
        $owner->assignRole('super_admin');

        $this->actingAs($owner)
            ->get(route('admin.standalone.status'))
            ->assertOk()
            ->assertSee('Offline attendance receipts')
            ->assertSee('Offline synced');
    }

    public function test_new_client_uuid_updates_existing_student_class_date_without_duplicate_row(): void
    {
        $this->enableOfflineAttendance();
        [$school, $admin, $class, , , $student] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student)],
        ])->assertJsonPath('results.0.status', 'synced');

        $this->postJson(route('school.attendance.offline-sync'), [
            'records' => [$this->payload($class, $student, null, [
                'status' => 'late',
                'note' => 'Arrived after assembly',
            ])],
        ])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'conflict')
            ->assertJsonPath('results.0.accepted', true);

        $this->assertDatabaseCount('student_attendance_records', 1);
        $this->assertDatabaseHas('student_attendance_records', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'status' => 'late',
            'note' => 'Arrived after assembly',
            'source' => 'browser_offline',
        ]);
    }

    public function test_offline_attendance_controls_render_only_when_enabled(): void
    {
        [$school, $admin, $class] = $this->attendanceContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.attendance.classes.show', $class))
            ->assertOk()
            ->assertSee('Save Attendance')
            ->assertDontSee('Attendance-only offline capture')
            ->assertDontSee('data-attendance-offline-root', false);

        $this->enableOfflineAttendance();

        $this->get(route('school.attendance.classes.show', $class))
            ->assertOk()
            ->assertSee('Attendance-only offline capture')
            ->assertSee('Pending offline records')
            ->assertSee('Browser storage is temporary')
            ->assertSee('Only this class attendance form can be stored temporarily in this browser')
            ->assertSee('data-attendance-offline-root', false);
    }

    private function enableOfflineAttendance(): void
    {
        config([
            'standalone.pwa_offline.capture_enabled' => true,
            'standalone.pwa_offline.sync_enabled' => true,
            'standalone.pwa_offline.allowed_modules' => ['attendance'],
        ]);
    }

    private function payload(
        SchoolClass $class,
        Student $student,
        ?string $uuid = null,
        array $overrides = []
    ): array {
        return [
            ...$this->payloadId($class->id, $student->id, $uuid),
            ...$overrides,
        ];
    }

    private function payloadId(int $classId, int $studentId, ?string $uuid = null): array
    {
        return [
            'client_uuid' => $uuid ?? (string) Str::uuid(),
            'school_class_id' => $classId,
            'student_id' => $studentId,
            'attendance_date' => '2026-06-11',
            'status' => 'present',
            'note' => null,
            'captured_at' => '2026-06-11T08:15:00+01:00',
            'source' => 'browser_offline',
        ];
    }

    private function attendanceContext(string $role): array
    {
        $school = School::create([
            'name' => fake()->unique()->company().' Academy',
            'slug' => fake()->unique()->slug(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '08030000000',
            'address' => 'Ilorin',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $user = $this->createUserForSchool($school, $role);
        $class = $this->createClass($school, 'JSS 1');
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
        $student = $this->createStudent($school, $class, fake()->unique()->bothify('OFF-###'));

        return [$school, $user, $class, $session, $term, $student];
    }

    private function createClass(School $school, string $name): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $school->id,
            'name' => $name,
            'section' => fake()->unique()->lexify('??'),
            'status' => 'active',
        ]);
    }

    private function createStudent(School $school, SchoolClass $class, string $admissionNumber): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'first_name' => 'Offline',
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
