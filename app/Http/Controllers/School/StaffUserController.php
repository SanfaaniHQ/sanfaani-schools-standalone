<?php

namespace App\Http\Controllers\School;

use App\Events\StaffTransactionalEmailRequested;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\StaffCodeGeneratorService;
use App\Services\Users\UserAccountSetupNotificationService;
use App\Services\Users\UserLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StaffUserController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        $status = $this->statusFilter($request);

        $staffUsers = User::query()
            ->where(function ($query) use ($school) {
                $query->where('school_id', $school->id)
                    ->orWhereHas('schoolRoles', fn ($roleQuery) => $roleQuery->where('school_id', $school->id));
            })
            ->where(function ($query) {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('name', $this->manageableRoles()))
                    ->orWhereHas('schoolRoles', fn ($roleQuery) => $roleQuery->whereIn('role_name', $this->manageableRoles()));
            })
            ->with(['roles', 'schoolRoles' => fn ($query) => $query->where('school_id', $school->id)])
            ->when($status === 'active', fn ($query) => $query
                ->whereNull('users.disabled_at')
                ->whereNull('users.archived_at')
                ->whereHas('schoolRoles', fn ($roleQuery) => $roleQuery
                    ->where('school_id', $school->id)
                    ->whereIn('role_name', $this->manageableRoles())
                    ->where('status', 'active')))
            ->when($status === 'disabled', fn ($query) => $query
                ->whereNull('users.archived_at')
                ->where(function ($statusQuery) use ($school) {
                    $statusQuery->whereNotNull('users.disabled_at')
                        ->orWhereHas('schoolRoles', fn ($roleQuery) => $roleQuery
                            ->where('school_id', $school->id)
                            ->whereIn('role_name', $this->manageableRoles())
                            ->whereIn('status', ['inactive', 'disabled']));
                }))
            ->when($status === 'archived', fn ($query) => $query
                ->where(function ($statusQuery) use ($school) {
                    $statusQuery->whereNotNull('users.archived_at')
                        ->orWhereHas('schoolRoles', fn ($roleQuery) => $roleQuery
                            ->where('school_id', $school->id)
                            ->whereIn('role_name', $this->manageableRoles())
                            ->where('status', 'archived'));
                }))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('school.staff.index', [
            'school' => $school,
            'staffUsers' => $staffUsers,
            'status' => $status,
        ]);
    }

    public function create(StaffCodeGeneratorService $staffCodes)
    {
        $school = $this->currentSchoolOrFail();
        $role = request('role', 'teacher');

        if (! in_array($role, $this->manageableRoles(), true)) {
            $role = 'teacher';
        }

        return view('school.staff.create', [
            'school' => $school,
            'roles' => $this->manageableRoles(),
            'suggestedStaffCode' => $staffCodes->generateForSchool($school, $role),
            'selectedRole' => $role,
        ]);
    }

    public function store(
        Request $request,
        StaffCodeGeneratorService $staffCodes,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $school = $this->currentSchoolOrFail();

        if ($request->boolean('auto_generate_staff_code')) {
            $request->merge(['staff_code' => null]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in($this->manageableRoles())],
            'staff_code' => ['nullable', 'string', 'max:100', Rule::unique('users', 'staff_code')],
            'auto_generate_staff_code' => ['nullable', 'boolean'],
        ]);

        $staffCode = $data['staff_code'] ?: $staffCodes->generateForSchool($school, $data['role']);

        $email = strtolower($data['email']);
        $user = User::where('email', $email)->first();
        $wasExistingUser = (bool) $user;

        if (! $user) {
            $user = User::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'email' => $email,
                'staff_code' => strtoupper(trim($staffCode)),
                'password' => Hash::make(Str::random(48)),
                'must_change_password' => true,
            ]);
        } else {
            $user->update([
                'name' => $user->name ?: $data['name'],
                'school_id' => $user->school_id ?: $school->id,
                'staff_code' => $user->staff_code ?: strtoupper(trim($staffCode)),
            ]);
        }

        $user->assignRole($data['role']);

        UserSchoolRole::updateOrCreate([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $data['role'],
        ], [
            'status' => 'active',
            'assigned_by' => auth()->id(),
        ]);

        $setupResult = ['sent' => true];

        if (! $wasExistingUser) {
            $setupResult = $setupNotifications->sendSetupLink(
                $user->refresh(),
                $school,
                UserAccountSetupNotificationService::ACCOUNT_CREATED_SETUP_LINK,
                $data['role'],
                $request->user()
            );
        }

        return redirect()
            ->route('school.staff.index')
            ->with($this->setupFlash(
                $setupResult,
                $wasExistingUser ? __('ui.existing_user_school_access_granted') : __('ui.staff_created_setup_sent')
            ));
    }

    public function edit(User $staff)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStaff($staff, $school);

        return view('school.staff.edit', [
            'school' => $school,
            'staff' => $staff->load('roles'),
            'roles' => $this->manageableRoles(),
            'selectedRole' => $staff->roles->pluck('name')->first(fn ($role) => in_array($role, $this->manageableRoles(), true)) ?? 'teacher',
        ]);
    }

    public function update(Request $request, User $staff)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStaff($staff, $school);
        $previousRole = $this->currentSchoolRole($staff, $school);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff->id)],
            'role' => ['required', Rule::in($this->manageableRoles())],
            'staff_code' => ['required', 'string', 'max:100', Rule::unique('users', 'staff_code')->ignore($staff->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'must_change_password' => ['nullable', 'boolean'],
        ]);

        $update = [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'staff_code' => strtoupper(trim($data['staff_code'])),
            'must_change_password' => (bool) ($data['must_change_password'] ?? false),
        ];

        if (filled($data['password'] ?? null)) {
            $update['password'] = $data['password'];
        }

        $staff->update($update);
        $staff->assignRole($data['role']);

        UserSchoolRole::where('user_id', $staff->id)
            ->where('school_id', $school->id)
            ->whereIn('role_name', $this->manageableRoles())
            ->update(['status' => 'inactive']);

        UserSchoolRole::updateOrCreate([
            'user_id' => $staff->id,
            'school_id' => $school->id,
            'role_name' => $data['role'],
        ], [
            'status' => 'active',
            'assigned_by' => auth()->id(),
        ]);

        if ($previousRole && $previousRole !== $data['role']) {
            event(StaffTransactionalEmailRequested::roleUpdated($staff->refresh(), $school, $previousRole, $data['role']));
        }

        return redirect()
            ->route('school.staff.index')
            ->with('success', __('ui.staff_updated_success'));
    }

    public function sendSetupLink(
        Request $request,
        User $staff,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStaff($staff, $school);
        $role = $this->currentSchoolRole($staff, $school) ?? 'staff';

        $setupResult = $setupNotifications->sendSetupLink(
            $staff,
            $school,
            UserAccountSetupNotificationService::ACCOUNT_SETUP_LINK_RESENT,
            $role,
            $request->user()
        );

        return back()->with($this->setupFlash($setupResult, __('ui.setup_link_sent')));
    }

    public function disable(
        Request $request,
        User $staff,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStaff($staff, $school);

        if ($staff->hasAnyRole(['super_admin', 'school_admin'])) {
            abort(403, __('ui.account_action_not_allowed'));
        }

        $role = $this->currentSchoolRole($staff, $school) ?? 'staff';
        $lifecycle->disable($staff, $school, $this->manageableRoles(), $request->user(), $request);

        if (! $lifecycle->hasOtherActiveSchoolRoles($staff, $school)) {
            foreach ($this->manageableRoles() as $manageableRole) {
                if ($staff->hasRole($manageableRole)) {
                    $staff->removeRole($manageableRole);
                }
            }
        }

        app(AuditLogService::class)->log('staff_access_disabled', $staff, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($staff->refresh(), UserAccountSetupNotificationService::ACCOUNT_DISABLED, $school, $role, $request->user());

        return redirect()
            ->route('school.staff.index', ['status' => 'disabled'])
            ->with('success', __('ui.account_disabled_success'));
    }

    public function enable(
        Request $request,
        User $staff,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $school = $this->currentSchoolOrFail();

        $schoolRole = UserSchoolRole::where('user_id', $staff->id)
            ->where('school_id', $school->id)
            ->whereIn('role_name', $this->manageableRoles())
            ->latest()
            ->first();

        if (! $schoolRole) {
            abort(403, __('ui.staff_role_missing'));
        }

        if (! $staff->hasRole($schoolRole->role_name)) {
            $staff->assignRole($schoolRole->role_name);
        }

        if (! $staff->school_id || (int) $staff->school_id !== (int) $school->id) {
            $staff->update(['school_id' => $school->id]);
        }

        $lifecycle->enable($staff, $school, [$schoolRole->role_name], $request->user(), $request);

        app(AuditLogService::class)->log('staff_access_enabled', $staff, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($staff->refresh(), UserAccountSetupNotificationService::ACCOUNT_ENABLED, $school, $schoolRole->role_name, $request->user());

        return redirect()
            ->route('school.staff.index', ['status' => 'disabled'])
            ->with('success', __('ui.account_enabled_success'));
    }

    public function archive(
        Request $request,
        User $staff,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStaff($staff, $school);

        if ($staff->hasAnyRole(['super_admin', 'school_admin'])) {
            abort(403, __('ui.account_action_not_allowed'));
        }

        $role = $this->currentSchoolRole($staff, $school) ?? 'staff';
        $lifecycle->archive($staff, $school, $this->manageableRoles(), $request->user(), $request);

        if (! $lifecycle->hasOtherActiveSchoolRoles($staff, $school)) {
            foreach ($this->manageableRoles() as $manageableRole) {
                if ($staff->hasRole($manageableRole)) {
                    $staff->removeRole($manageableRole);
                }
            }
        }

        app(AuditLogService::class)->log('staff_archived', $staff, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($staff->refresh(), UserAccountSetupNotificationService::ACCOUNT_ARCHIVED, $school, $role, $request->user());

        return redirect()
            ->route('school.staff.index', ['status' => 'archived'])
            ->with('success', __('ui.account_archived_success'));
    }

    public function restore(
        Request $request,
        User $staff,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStaff($staff, $school);
        $role = $this->currentSchoolRole($staff, $school) ?? 'teacher';

        $staff->assignRole($role);
        $lifecycle->restore($staff, $school, [$role], $request->user(), $request);

        app(AuditLogService::class)->log('staff_restored', $staff, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($staff->refresh(), UserAccountSetupNotificationService::ACCOUNT_RESTORED, $school, $role, $request->user());

        return redirect()
            ->route('school.staff.index', ['status' => 'archived'])
            ->with('success', __('ui.account_restored_success'));
    }

    public function destroy(
        Request $request,
        User $staff,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStaff($staff, $school);

        if ($staff->hasAnyRole(['super_admin', 'school_admin'])) {
            abort(403, __('ui.account_action_not_allowed'));
        }

        $role = $this->currentSchoolRole($staff, $school) ?? 'staff';
        $result = $lifecycle->deleteOrArchive($staff, $school, $this->manageableRoles(), $request->user(), $request);

        if ($result['archived']) {
            foreach ($this->manageableRoles() as $manageableRole) {
                if ($staff->hasRole($manageableRole)) {
                    $staff->removeRole($manageableRole);
                }
            }

            app(AuditLogService::class)->log('staff_delete_archived_instead', $staff, $school, request: $request);
            $setupNotifications->sendLifecycleNotice($staff->refresh(), UserAccountSetupNotificationService::ACCOUNT_ARCHIVED, $school, $role, $request->user());

            return redirect()
                ->route('school.staff.index', ['status' => 'archived'])
                ->with('success', __('ui.account_delete_archived_instead'));
        }

        app(AuditLogService::class)->log('staff_deleted', null, $school, metadata: [
            'target_id' => $staff->id,
        ], request: $request);

        return redirect()
            ->route('school.staff.index')
            ->with('success', __('ui.account_deleted_success'));
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, __('ui.account_not_assigned_to_school'));
        }

        return $school;
    }

    private function authorizeStaff(User $staff, School $school): void
    {
        $hasSchoolRole = $staff->schoolRoles()
            ->where('school_id', $school->id)
            ->whereIn('role_name', $this->manageableRoles())
            ->exists();

        if ((int) $staff->school_id !== (int) $school->id && ! $hasSchoolRole) {
            abort(403, __('ui.staff_manage_blocked'));
        }
    }

    private function currentSchoolRole(User $staff, School $school): ?string
    {
        $activeRole = UserSchoolRole::where('user_id', $staff->id)
            ->where('school_id', $school->id)
            ->whereIn('role_name', $this->manageableRoles())
            ->where('status', 'active')
            ->value('role_name');

        if ($activeRole) {
            return $activeRole;
        }

        return UserSchoolRole::where('user_id', $staff->id)
            ->where('school_id', $school->id)
            ->whereIn('role_name', $this->manageableRoles())
            ->latest()
            ->value('role_name');
    }

    private function manageableRoles(): array
    {
        return ['teacher', 'result_officer'];
    }

    private function statusFilter(Request $request): string
    {
        $status = (string) $request->query('status', 'active');

        return in_array($status, ['active', 'disabled', 'archived'], true) ? $status : 'active';
    }

    private function setupFlash(array $setupResult, string $successMessage): array
    {
        if ($setupResult['sent']) {
            return ['success' => $successMessage];
        }

        return ['warning' => __('ui.account_created_setup_failed')];
    }
}
