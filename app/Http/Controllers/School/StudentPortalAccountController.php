<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\Portals\StudentPortalLinkService;
use App\Services\Users\UserAccountSetupNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class StudentPortalAccountController extends Controller
{
    public function createParent(
        Request $request,
        Student $student,
        StudentPortalLinkService $portalLinks,
        UserAccountSetupNotificationService $setupNotifications
    ): RedirectResponse {
        $school = $this->authorizeStudent($student);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'relationship' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['nullable', 'boolean'],
            'send_setup_link' => ['nullable', 'boolean'],
        ]);

        $email = strtolower(trim($data['email']));
        $user = User::query()->where('email', $email)->first();
        $wasExistingUser = (bool) $user;

        if (! $user) {
            $user = User::query()->create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make(Str::random(48)),
                'must_change_password' => true,
            ]);
        } else {
            $user->forceFill([
                'name' => $user->name ?: $data['name'],
                'school_id' => $user->school_id ?: $school->id,
            ])->save();
        }

        $this->grantSchoolRole($user, $school->id, 'parent');

        $portalLinks->attachParentToStudent(
            $user->refresh(),
            $student,
            $data['relationship'] ?: 'guardian',
            $request->boolean('is_primary')
        );

        if (! $wasExistingUser || $request->boolean('send_setup_link')) {
            $setupNotifications->sendSetupLink(
                $user->refresh(),
                $school,
                UserAccountSetupNotificationService::ACCOUNT_CREATED_SETUP_LINK,
                'parent',
                $request->user()
            );
        }

        app(AuditLogService::class)->log('student_parent_portal_account_linked', $student, $school, [
            'parent_user_id' => $user->id,
            'was_existing_user' => $wasExistingUser,
        ], $request);

        return back()->with('success', 'Parent account linked successfully.');
    }

    public function linkParent(
        Request $request,
        Student $student,
        StudentPortalLinkService $portalLinks
    ): RedirectResponse {
        $school = $this->authorizeStudent($student);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::exists('users', 'email')],
            'relationship' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $user = User::query()
            ->where('email', strtolower(trim($data['email'])))
            ->firstOrFail();

        $this->grantSchoolRole($user, $school->id, 'parent');

        $portalLinks->attachParentToStudent(
            $user->refresh(),
            $student,
            $data['relationship'] ?: 'guardian',
            $request->boolean('is_primary')
        );

        app(AuditLogService::class)->log('student_existing_parent_portal_account_linked', $student, $school, [
            'parent_user_id' => $user->id,
        ], $request);

        return back()->with('success', 'Existing parent account linked successfully.');
    }

    public function unlinkParent(Request $request, Student $student, User $parent): RedirectResponse
    {
        $school = $this->authorizeStudent($student);

        $student->parentUsers()
            ->wherePivot('school_id', $school->id)
            ->detach($parent->id);

        app(AuditLogService::class)->log('student_parent_portal_account_unlinked', $student, $school, [
            'parent_user_id' => $parent->id,
        ], $request);

        return back()->with('success', 'Parent account unlinked successfully.');
    }

    public function createStudent(
        Request $request,
        Student $student,
        StudentPortalLinkService $portalLinks,
        UserAccountSetupNotificationService $setupNotifications
    ): RedirectResponse {
        $school = $this->authorizeStudent($student);

        if ($student->student_user_id) {
            throw ValidationException::withMessages([
                'email' => 'This student already has a linked portal account.',
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'send_setup_link' => ['nullable', 'boolean'],
        ]);

        $email = strtolower(trim($data['email']));
        $user = User::query()->where('email', $email)->first();
        $wasExistingUser = (bool) $user;

        if (! $user) {
            $user = User::query()->create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make(Str::random(48)),
                'must_change_password' => true,
            ]);
        } else {
            $this->ensureUserIsNotLinkedToAnotherStudent($user, $student);

            $user->forceFill([
                'name' => $user->name ?: $data['name'],
                'school_id' => $user->school_id ?: $school->id,
            ])->save();
        }

        $this->grantSchoolRole($user, $school->id, 'student');
        $portalLinks->linkStudentUser($user->refresh(), $student);

        if (! $wasExistingUser || $request->boolean('send_setup_link')) {
            $setupNotifications->sendSetupLink(
                $user->refresh(),
                $school,
                UserAccountSetupNotificationService::ACCOUNT_CREATED_SETUP_LINK,
                'student',
                $request->user()
            );
        }

        app(AuditLogService::class)->log('student_portal_account_linked', $student, $school, [
            'student_user_id' => $user->id,
            'was_existing_user' => $wasExistingUser,
        ], $request);

        return back()->with('success', 'Student portal account linked successfully.');
    }

    public function linkStudent(
        Request $request,
        Student $student,
        StudentPortalLinkService $portalLinks
    ): RedirectResponse {
        $school = $this->authorizeStudent($student);

        if ($student->student_user_id) {
            throw ValidationException::withMessages([
                'email' => 'This student already has a linked portal account.',
            ]);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::exists('users', 'email')],
        ]);

        $user = User::query()
            ->where('email', strtolower(trim($data['email'])))
            ->firstOrFail();

        $this->ensureUserIsNotLinkedToAnotherStudent($user, $student);
        $this->grantSchoolRole($user, $school->id, 'student');
        $portalLinks->linkStudentUser($user->refresh(), $student);

        app(AuditLogService::class)->log('student_existing_portal_account_linked', $student, $school, [
            'student_user_id' => $user->id,
        ], $request);

        return back()->with('success', 'Existing student account linked successfully.');
    }

    public function unlinkStudent(Request $request, Student $student): RedirectResponse
    {
        $school = $this->authorizeStudent($student);
        $oldUserId = $student->student_user_id;

        $student->forceFill([
            'student_user_id' => null,
        ])->save();

        app(AuditLogService::class)->log('student_portal_account_unlinked', $student, $school, [
            'student_user_id' => $oldUserId,
        ], $request);

        return back()->with('success', 'Student portal account unlinked successfully.');
    }

    private function authorizeStudent(Student $student): School
    {
        $activeSchool = app(CurrentSchoolService::class)->get();

        if ($activeSchool && (int) $student->school_id === (int) $activeSchool->id) {
            return $activeSchool;
        }

        $user = auth()->user();

        $canManageStudent = $user && (
            $user->hasRole('super_admin')
            || (int) $user->school_id === (int) $student->school_id
            || $user->schoolRoles()
                ->where('school_id', $student->school_id)
                ->whereIn('role_name', ['school_admin', 'result_officer'])
                ->where('status', 'active')
                ->exists()
        );

        if (! $canManageStudent) {
            abort(403, 'This student does not belong to the active school.');
        }

        return $student->school ?: School::query()->findOrFail($student->school_id);
    }

    private function grantSchoolRole(User $user, int $schoolId, string $role): void
    {
        Role::findOrCreate($role);

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        UserSchoolRole::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'school_id' => $schoolId,
                'role_name' => $role,
            ],
            [
                'status' => 'active',
                'assigned_by' => auth()->id(),
            ]
        );

        if (! $user->school_id) {
            $user->forceFill(['school_id' => $schoolId])->save();
        }
    }

    private function ensureUserIsNotLinkedToAnotherStudent(User $user, Student $student): void
    {
        $alreadyLinked = Student::query()
            ->where('school_id', $student->school_id)
            ->where('student_user_id', $user->id)
            ->where('id', '!=', $student->id)
            ->exists();

        if ($alreadyLinked) {
            throw ValidationException::withMessages([
                'email' => 'This user is already linked to another student profile in this school.',
            ]);
        }
    }
}
