<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendanceRecord;
use App\Models\Subject;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherSubjectAssignment;
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

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'accountant', 'parent', 'student'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_attendance_routes_require_authentication(): void
    {
        $this->get(route('school.attendance.index'))
            ->assertRedirect(route('login'));

        $this->get(route('school.attendance.reports'))
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

        foreach (['result_officer', 'accountant', 'parent', 'student'] as $role) {
            $user = $this->createUserForSchool($school, $role);
            $this->actAsSchoolRole($user, $school, $role);

            $this->post(route('school.attendance.classes.store', $class), $payload)
                ->assertForbidden();
        }

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
        $this->createStudent($school, $class, 'ATT-008B', 'Ireti');
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
        $this->assertSame(4, $summary['total']);
        $this->assertSame(1, $summary['missing']);
        $this->assertEquals(75.0, $summary['attendance_percentage']);
    }

    public function test_school_admin_can_filter_attendance_reports_by_class_date_status_session_and_term(): void
    {
        [$school, $admin, $class, $session, $term] = $this->attendanceContext('school_admin');
        $otherClass = $this->createClass($school, 'JSS 2', fake()->unique()->lexify('??'));
        $presentStudent = $this->createStudent($school, $class, 'ATT-011', 'Lami');
        $absentStudent = $this->createStudent($school, $class, 'ATT-012', 'Musa');
        $otherClassStudent = $this->createStudent($school, $otherClass, 'ATT-013', 'Nkechi');
        [$otherSchool, $otherAdmin, $otherSchoolClass] = $this->attendanceContext('school_admin');
        $otherSchoolStudent = $this->createStudent($otherSchool, $otherSchoolClass, 'OTHER-002', 'Ola');

        StudentAttendanceRecord::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'student_id' => $presentStudent->id,
            'recorded_by' => $admin->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'attendance_date' => '2026-06-11',
            'status' => 'present',
            'note' => 'Report match',
            'source' => 'web',
        ]);
        StudentAttendanceRecord::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'student_id' => $absentStudent->id,
            'recorded_by' => $admin->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'attendance_date' => '2026-06-11',
            'status' => 'absent',
            'note' => 'Filtered absent',
            'source' => 'web',
        ]);
        StudentAttendanceRecord::create([
            'school_id' => $school->id,
            'school_class_id' => $otherClass->id,
            'student_id' => $otherClassStudent->id,
            'recorded_by' => $admin->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'attendance_date' => '2026-06-11',
            'status' => 'present',
            'note' => 'Other class',
            'source' => 'web',
        ]);
        StudentAttendanceRecord::create([
            'school_id' => $otherSchool->id,
            'school_class_id' => $otherSchoolClass->id,
            'student_id' => $otherSchoolStudent->id,
            'recorded_by' => $otherAdmin->id,
            'attendance_date' => '2026-06-11',
            'status' => 'present',
            'note' => 'Other school',
            'source' => 'web',
        ]);

        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.attendance.reports', [
            'date' => '2026-06-11',
            'school_class_id' => $class->id,
            'status' => 'present',
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]))
            ->assertOk()
            ->assertSee('Report match')
            ->assertSee('Lami Student')
            ->assertSee('100.0%')
            ->assertDontSee('Filtered absent')
            ->assertDontSee('Other class')
            ->assertDontSee('Other school');
    }

    public function test_date_range_attendance_report_counts_and_percentage_are_correct(): void
    {
        [$school, $admin, $class] = $this->attendanceContext('school_admin');
        $students = collect([
            $this->createStudent($school, $class, 'ATT-014', 'Pola'),
            $this->createStudent($school, $class, 'ATT-015', 'Qadir'),
            $this->createStudent($school, $class, 'ATT-016', 'Reni'),
            $this->createStudent($school, $class, 'ATT-017', 'Sade'),
        ]);

        foreach (['present', 'absent', 'late', 'excused'] as $index => $status) {
            StudentAttendanceRecord::create([
                'school_id' => $school->id,
                'school_class_id' => $class->id,
                'student_id' => $students[$index]->id,
                'recorded_by' => $admin->id,
                'attendance_date' => $index < 2 ? '2026-06-10' : '2026-06-11',
                'status' => $status,
                'note' => 'Range '.$status,
                'source' => 'web',
            ]);
        }

        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.attendance.reports', [
            'date_from' => '2026-06-10',
            'date_to' => '2026-06-11',
            'school_class_id' => $class->id,
        ]))
            ->assertOk()
            ->assertSee('Range present')
            ->assertSee('Range absent')
            ->assertSee('Range late')
            ->assertSee('Range excused')
            ->assertSee('75.0%');
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

    public function test_student_attendance_history_filters_by_date_range_status_session_and_term(): void
    {
        [$school, $admin, $class, $session, $term] = $this->attendanceContext('school_admin');
        $student = $this->createStudent($school, $class, 'ATT-018', 'Teni');
        $otherSession = AcademicSession::create([
            'school_id' => $school->id,
            'name' => '2027/2028',
            'is_active' => false,
            'status' => 'active',
        ]);
        $otherTerm = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $otherSession->id,
            'name' => 'Second Term',
            'is_active' => false,
            'status' => 'active',
        ]);

        StudentAttendanceRecord::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'student_id' => $student->id,
            'recorded_by' => $admin->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'attendance_date' => '2026-06-10',
            'status' => 'present',
            'note' => 'Before filtered window',
            'source' => 'web',
        ]);
        StudentAttendanceRecord::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'student_id' => $student->id,
            'recorded_by' => $admin->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'attendance_date' => '2026-06-11',
            'status' => 'absent',
            'note' => 'Student history match',
            'source' => 'web',
        ]);
        StudentAttendanceRecord::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'student_id' => $student->id,
            'recorded_by' => $admin->id,
            'academic_session_id' => $otherSession->id,
            'term_id' => $otherTerm->id,
            'attendance_date' => '2026-06-12',
            'status' => 'late',
            'note' => 'Other term row',
            'source' => 'web',
        ]);

        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.attendance.students.show', [
            'student' => $student,
            'date_from' => '2026-06-11',
            'date_to' => '2026-06-12',
            'status' => 'absent',
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]))
            ->assertOk()
            ->assertSee('Student history match')
            ->assertSee('0.0%')
            ->assertDontSee('Before filtered window')
            ->assertDontSee('Other term row');
    }

    public function test_teacher_report_access_requires_active_class_assignment_not_subject_only_visibility(): void
    {
        [$school, $teacher, $assignedClass, $session, $term] = $this->attendanceContext('teacher');
        $unassignedClass = $this->createClass($school, 'JSS 3', fake()->unique()->lexify('??'));
        $assignedStudent = $this->createStudent($school, $assignedClass, 'ATT-019', 'Uche');
        $unassignedStudent = $this->createStudent($school, $unassignedClass, 'ATT-020', 'Vera');
        $subject = $this->createSubject($school, 'Mathematics');

        TeacherClassAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $assignedClass->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'role_type' => 'class_teacher',
            'status' => 'active',
        ]);
        TeacherSubjectAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $unassignedClass->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'role_type' => 'subject_teacher',
            'status' => 'active',
        ]);
        StudentAttendanceRecord::create([
            'school_id' => $school->id,
            'school_class_id' => $assignedClass->id,
            'student_id' => $assignedStudent->id,
            'recorded_by' => $teacher->id,
            'attendance_date' => '2026-06-11',
            'status' => 'present',
            'note' => 'Teacher assigned report row',
            'source' => 'web',
        ]);

        $this->actAsSchoolRole($teacher, $school, 'teacher');

        $this->get(route('school.attendance.reports', [
            'date' => '2026-06-11',
            'school_class_id' => $assignedClass->id,
        ]))
            ->assertOk()
            ->assertSee('Teacher assigned report row');

        $this->get(route('school.attendance.reports', [
            'date' => '2026-06-11',
            'school_class_id' => $unassignedClass->id,
        ]))->assertForbidden();

        $this->get(route('school.attendance.classes.show', $unassignedClass))
            ->assertForbidden();

        $this->post(route('school.attendance.classes.store', $unassignedClass), [
            'attendance_date' => '2026-06-11',
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'records' => [
                ['student_id' => $unassignedStudent->id, 'status' => 'present'],
            ],
        ])->assertForbidden();
    }

    public function test_invalid_report_filters_are_rejected(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->from(route('school.attendance.reports'))
            ->get(route('school.attendance.reports', [
                'date_from' => '2026-06-12',
                'date_to' => '2026-06-11',
            ]))
            ->assertRedirect(route('school.attendance.reports'))
            ->assertSessionHasErrors('date_to');

        $this->from(route('school.attendance.reports'))
            ->get(route('school.attendance.reports', ['status' => 'missing']))
            ->assertRedirect(route('school.attendance.reports'))
            ->assertSessionHasErrors('status');
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

    public function test_attendance_update_audit_log_keeps_safe_before_after_context(): void
    {
        [$school, $admin, $class] = $this->attendanceContext('school_admin');
        $student = $this->createStudent($school, $class, 'ATT-021', 'Wale');
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
                ['student_id' => $student->id, 'status' => 'absent', 'note' => 'Called in sick'],
            ],
        ])->assertSessionHasNoErrors();

        $log = AuditLog::query()
            ->where('school_id', $school->id)
            ->where('action', 'attendance_updated')
            ->firstOrFail();

        $this->assertSame('present', $log->old_values['status']);
        $this->assertSame('absent', $log->new_values['status']);
        $this->assertSame($class->id, $log->metadata['school_class_id']);
        $this->assertSame($student->id, $log->metadata['student_id']);
        $this->assertSame($admin->id, $log->metadata['recorded_by']);
        $this->assertContains('status', $log->metadata['changed_fields']);
        $this->assertArrayNotHasKey('student_name', $log->metadata);
        $this->assertStringNotContainsString($student->admission_number, json_encode($log->metadata));
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

        $accountant = $this->createUserForSchool($school, 'accountant');
        $this->actAsSchoolRole($accountant, $school, 'accountant');

        $this->get(route('school.attendance.index'))
            ->assertForbidden();
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

    private function createSubject(School $school, string $name): Subject
    {
        return Subject::create([
            'school_id' => $school->id,
            'name' => $name,
            'code' => fake()->unique()->lexify('???'),
            'assignment_type' => 'core',
            'is_elective' => false,
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
