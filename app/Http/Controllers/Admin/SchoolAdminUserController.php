<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\AuditLogService;
use App\Services\Users\UserAccountSetupNotificationService;
use App\Services\Users\UserLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SchoolAdminUserController extends Controller
{
    private const ROLES = ['school_admin'];

    public function index(Request $request, School $school)
    {
        $status = $this->statusFilter($request);

        $admins = User::query()
            ->where(function ($query) use ($school) {
                $query->where('school_id', $school->id)
                    ->orWhereHas('schoolRoles', fn ($roleQuery) => $roleQuery
                        ->where('school_id', $school->id)
                        ->where('role_name', 'school_admin'));
            })
            ->where(function ($query) use ($school) {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'school_admin'))
                    ->orWhereHas('schoolRoles', fn ($roleQuery) => $roleQuery
                        ->where('school_id', $school->id)
                        ->where('role_name', 'school_admin'));
            })
            ->with(['roles', 'schoolRoles' => fn ($query) => $query->where('school_id', $school->id)])
            ->when($status === 'active', fn ($query) => $query
                ->whereNull('users.disabled_at')
                ->whereNull('users.archived_at')
                ->whereHas('schoolRoles', fn ($roleQuery) => $roleQuery
                    ->where('school_id', $school->id)
                    ->where('role_name', 'school_admin')
                    ->where('status', 'active')))
            ->when($status === 'disabled', fn ($query) => $query
                ->whereNull('users.archived_at')
                ->where(function ($statusQuery) use ($school) {
                    $statusQuery->whereNotNull('users.disabled_at')
                        ->orWhereHas('schoolRoles', fn ($roleQuery) => $roleQuery
                            ->where('school_id', $school->id)
                            ->where('role_name', 'school_admin')
                            ->whereIn('status', ['inactive', 'disabled']));
                }))
            ->when($status === 'archived', fn ($query) => $query
                ->where(function ($statusQuery) use ($school) {
                    $statusQuery->whereNotNull('users.archived_at')
                        ->orWhereHas('schoolRoles', fn ($roleQuery) => $roleQuery
                            ->where('school_id', $school->id)
                            ->where('role_name', 'school_admin')
                            ->where('status', 'archived'));
                }))
            ->latest()
            ->get();

        return view('admin.schools.admins.index', compact('school', 'admins', 'status'));
    }

    public function create(School $school)
    {
        return view('admin.schools.admins.create', compact('school'));
    }

    public function store(
        Request $request,
        School $school,
        AuditLogService $auditLog,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));
        $existingUser = User::where('email', $email)->first();

        if ($existingUser && $existingUser->hasRole('super_admin')) {
            return back()
                ->withInput()
                ->withErrors(['email' => __('ui.super_admin_assignment_blocked')]);
        }

        if ($existingUser) {
            $user = $existingUser;

            if (! $user->school_id || (int) $user->school_id === (int) $school->id) {
                $user->update(['school_id' => $school->id]);
            }
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make(Str::random(48)),
                'school_id' => $school->id,
                'must_change_password' => true,
            ]);
        }

        if (! $user->hasRole('school_admin')) {
            $user->assignRole('school_admin');
        }

        UserSchoolRole::updateOrCreate(
            [
                'user_id' => $user->id,
                'school_id' => $school->id,
                'role_name' => 'school_admin',
            ],
            [
                'status' => 'active',
                'assigned_by' => auth()->id(),
            ]
        );

        $setupResult = $setupNotifications->sendSetupLink(
            $user->refresh(),
            $school,
            UserAccountSetupNotificationService::ACCOUNT_CREATED_SETUP_LINK,
            'school_admin',
            $request->user()
        );

        $auditLog->log('school_admin_created', $user, $school, request: $request);

        return redirect()
            ->route('admin.schools.admins.index', $school)
            ->with($this->setupFlash($setupResult, __('ui.school_admin_created_setup_sent')));
    }

    public function resetPassword(Request $request, School $school, User $admin, AuditLogService $auditLog)
    {
        $this->authorizeAdmin($admin, $school);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $update = ['password' => Hash::make($data['password'])];

        if (Schema::hasColumn('users', 'must_change_password')) {
            $update['must_change_password'] = true;
        }

        $admin->update($update);

        $auditLog->log('school_admin_password_reset', $admin, $school, request: $request);

        return redirect()
            ->route('admin.schools.admins.index', $school)
            ->with('success', __('ui.password_updated_success'));
    }

    public function sendResetLink(
        Request $request,
        School $school,
        User $admin,
        AuditLogService $auditLog,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        return $this->sendSetupLink($request, $school, $admin, $auditLog, $setupNotifications);
    }

    public function sendSetupLink(
        Request $request,
        School $school,
        User $admin,
        AuditLogService $auditLog,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $this->authorizeAdmin($admin, $school);

        $setupResult = $setupNotifications->sendSetupLink(
            $admin,
            $school,
            UserAccountSetupNotificationService::ACCOUNT_SETUP_LINK_RESENT,
            'school_admin',
            $request->user()
        );

        $auditLog->log('school_admin_setup_link_sent', $admin, $school, request: $request);

        return back()->with($this->setupFlash($setupResult, __('ui.setup_link_sent')));
    }

    public function disable(
        Request $request,
        School $school,
        User $admin,
        AuditLogService $auditLog,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $this->authorizeAdmin($admin, $school);

        $lifecycle->disable($admin, $school, self::ROLES, $request->user(), $request);

        if (! $lifecycle->hasOtherActiveSchoolRoles($admin, $school)) {
            $admin->removeRole('school_admin');
        }

        $auditLog->log('school_admin_access_disabled', $admin, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($admin->refresh(), UserAccountSetupNotificationService::ACCOUNT_DISABLED, $school, 'school_admin', $request->user());

        return redirect()
            ->route('admin.schools.admins.index', [$school, 'status' => 'disabled'])
            ->with('success', __('ui.account_disabled_success'));
    }

    public function enable(
        Request $request,
        School $school,
        User $admin,
        AuditLogService $auditLog,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $this->authorizeAdmin($admin, $school);

        if (! $admin->hasRole('school_admin')) {
            $admin->assignRole('school_admin');
        }

        if (! $admin->school_id || (int) $admin->school_id !== (int) $school->id) {
            $admin->update(['school_id' => $school->id]);
        }

        UserSchoolRole::updateOrCreate(
            [
                'user_id' => $admin->id,
                'school_id' => $school->id,
                'role_name' => 'school_admin',
            ],
            [
                'status' => 'active',
                'assigned_by' => auth()->id(),
            ]
        );

        $lifecycle->enable($admin, $school, self::ROLES, $request->user(), $request);

        $auditLog->log('school_admin_access_enabled', $admin, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($admin->refresh(), UserAccountSetupNotificationService::ACCOUNT_ENABLED, $school, 'school_admin', $request->user());

        return redirect()
            ->route('admin.schools.admins.index', [$school, 'status' => 'disabled'])
            ->with('success', __('ui.account_enabled_success'));
    }

    public function archive(
        Request $request,
        School $school,
        User $admin,
        AuditLogService $auditLog,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $this->authorizeAdmin($admin, $school);
        $lifecycle->archive($admin, $school, self::ROLES, $request->user(), $request);

        if (! $lifecycle->hasOtherActiveSchoolRoles($admin, $school)) {
            $admin->removeRole('school_admin');
        }

        $auditLog->log('school_admin_archived', $admin, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($admin->refresh(), UserAccountSetupNotificationService::ACCOUNT_ARCHIVED, $school, 'school_admin', $request->user());

        return redirect()
            ->route('admin.schools.admins.index', [$school, 'status' => 'archived'])
            ->with('success', __('ui.account_archived_success'));
    }

    public function restore(
        Request $request,
        School $school,
        User $admin,
        AuditLogService $auditLog,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $this->authorizeAdmin($admin, $school);

        if (! $admin->hasRole('school_admin')) {
            $admin->assignRole('school_admin');
        }

        $lifecycle->restore($admin, $school, self::ROLES, $request->user(), $request);

        $auditLog->log('school_admin_restored', $admin, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($admin->refresh(), UserAccountSetupNotificationService::ACCOUNT_RESTORED, $school, 'school_admin', $request->user());

        return redirect()
            ->route('admin.schools.admins.index', [$school, 'status' => 'archived'])
            ->with('success', __('ui.account_restored_success'));
    }

    public function destroy(
        Request $request,
        School $school,
        User $admin,
        AuditLogService $auditLog,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ) {
        $this->authorizeAdmin($admin, $school);
        $result = $lifecycle->deleteOrArchive($admin, $school, self::ROLES, $request->user(), $request);

        if ($result['archived']) {
            $admin->removeRole('school_admin');
            $auditLog->log('school_admin_delete_archived_instead', $admin, $school, request: $request);
            $setupNotifications->sendLifecycleNotice($admin->refresh(), UserAccountSetupNotificationService::ACCOUNT_ARCHIVED, $school, 'school_admin', $request->user());

            return redirect()
                ->route('admin.schools.admins.index', [$school, 'status' => 'archived'])
                ->with('success', __('ui.account_delete_archived_instead'));
        }

        $auditLog->log('school_admin_deleted', null, $school, metadata: [
            'target_id' => $admin->id,
        ], request: $request);

        return redirect()
            ->route('admin.schools.admins.index', $school)
            ->with('success', __('ui.account_deleted_success'));
    }

    private function authorizeAdmin(User $admin, School $school): void
    {
        $belongsToSchool = (int) $admin->school_id === (int) $school->id;

        if (! $belongsToSchool && class_exists(UserSchoolRole::class)) {
            $belongsToSchool = $admin->schoolRoles()
                ->where('school_id', $school->id)
                ->where('role_name', 'school_admin')
                ->exists();
        }

        if (! $belongsToSchool) {
            abort(403, __('ui.user_not_in_school'));
        }
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
