<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\AuditLogService;
use App\Services\Users\UserAccountSetupNotificationService;
use App\Services\Users\UserLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class LocalSchoolAdminController extends Controller
{
    private const ROLES = ['school_admin'];

    public function index(Request $request): View
    {
        $school = $this->localSchool();
        $status = $this->statusFilter($request);

        return view('admin.local-admins.index', [
            'school' => $school,
            'status' => $status,
            'admins' => User::query()
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
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.local-admins.create', [
            'school' => $this->localSchool(),
        ]);
    }

    public function store(
        Request $request,
        AuditLogService $auditLog,
        UserAccountSetupNotificationService $setupNotifications
    ): RedirectResponse {
        $school = $this->localSchool();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $active = $request->boolean('is_active', true);
        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make(Str::random(48)),
            'school_id' => $school->id,
            'must_change_password' => true,
            'disabled_at' => $active ? null : now(),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        Role::findOrCreate('school_admin');

        if ($active) {
            $user->assignRole('school_admin');
        }

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => $active ? 'active' : 'inactive',
            'assigned_by' => $request->user()?->id,
            'metadata' => ['source' => 'local_dashboard'],
        ]);

        $setupResult = $setupNotifications->sendSetupLink(
            $user->refresh(),
            $school,
            UserAccountSetupNotificationService::ACCOUNT_CREATED_SETUP_LINK,
            'school_admin',
            $request->user()
        );

        $auditLog->log('local_school_admin_created', $user, $school, metadata: [
            'active' => $active,
        ], request: $request);

        return redirect()
            ->route('admin.local-admins.index')
            ->with($this->setupFlash($setupResult, __('ui.school_admin_created_setup_sent')));
    }

    public function resetPassword(Request $request, User $admin, AuditLogService $auditLog): RedirectResponse
    {
        $school = $this->localSchool();
        $this->authorizeLocalAdmin($admin, $school);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin->forceFill([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ])->save();

        $auditLog->log('local_school_admin_password_reset', $admin, $school, request: $request);

        return back()->with('success', __('ui.password_updated_success'));
    }

    public function sendSetupLink(
        Request $request,
        User $admin,
        AuditLogService $auditLog,
        UserAccountSetupNotificationService $setupNotifications
    ): RedirectResponse {
        $school = $this->localSchool();
        $this->authorizeLocalAdmin($admin, $school);

        $setupResult = $setupNotifications->sendSetupLink(
            $admin,
            $school,
            UserAccountSetupNotificationService::ACCOUNT_SETUP_LINK_RESENT,
            'school_admin',
            $request->user()
        );

        $auditLog->log('local_school_admin_setup_link_sent', $admin, $school, request: $request);

        return back()->with($this->setupFlash($setupResult, __('ui.setup_link_sent')));
    }

    public function disable(
        Request $request,
        User $admin,
        AuditLogService $auditLog,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ): RedirectResponse {
        $school = $this->localSchool();
        $this->authorizeLocalAdmin($admin, $school);

        $lifecycle->disable($admin, $school, self::ROLES, $request->user(), $request);

        if (! $lifecycle->hasOtherActiveSchoolRoles($admin, $school)) {
            $admin->removeRole('school_admin');
        }

        $auditLog->log('local_school_admin_disabled', $admin, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($admin->refresh(), UserAccountSetupNotificationService::ACCOUNT_DISABLED, $school, 'school_admin', $request->user());

        return redirect()
            ->route('admin.local-admins.index', ['status' => 'disabled'])
            ->with('success', __('ui.account_disabled_success'));
    }

    public function enable(
        Request $request,
        User $admin,
        AuditLogService $auditLog,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ): RedirectResponse {
        $school = $this->localSchool();
        $this->authorizeLocalAdmin($admin, $school);

        Role::findOrCreate('school_admin');
        $admin->assignRole('school_admin');
        $admin->forceFill(['school_id' => $school->id])->save();

        UserSchoolRole::query()->updateOrCreate(
            [
                'user_id' => $admin->id,
                'school_id' => $school->id,
                'role_name' => 'school_admin',
            ],
            [
                'status' => 'active',
                'assigned_by' => $request->user()?->id,
                'metadata' => ['source' => 'local_dashboard'],
            ]
        );

        $lifecycle->enable($admin, $school, self::ROLES, $request->user(), $request);

        $auditLog->log('local_school_admin_enabled', $admin, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($admin->refresh(), UserAccountSetupNotificationService::ACCOUNT_ENABLED, $school, 'school_admin', $request->user());

        return redirect()
            ->route('admin.local-admins.index', ['status' => 'disabled'])
            ->with('success', __('ui.account_enabled_success'));
    }

    public function archive(
        Request $request,
        User $admin,
        AuditLogService $auditLog,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ): RedirectResponse {
        $school = $this->localSchool();
        $this->authorizeLocalAdmin($admin, $school);

        $lifecycle->archive($admin, $school, self::ROLES, $request->user(), $request);

        if (! $lifecycle->hasOtherActiveSchoolRoles($admin, $school)) {
            $admin->removeRole('school_admin');
        }

        $auditLog->log('local_school_admin_archived', $admin, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($admin->refresh(), UserAccountSetupNotificationService::ACCOUNT_ARCHIVED, $school, 'school_admin', $request->user());

        return redirect()
            ->route('admin.local-admins.index', ['status' => 'archived'])
            ->with('success', __('ui.account_archived_success'));
    }

    public function restore(
        Request $request,
        User $admin,
        AuditLogService $auditLog,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ): RedirectResponse {
        $school = $this->localSchool();
        $this->authorizeLocalAdmin($admin, $school);

        Role::findOrCreate('school_admin');
        $admin->assignRole('school_admin');
        $lifecycle->restore($admin, $school, self::ROLES, $request->user(), $request);

        $auditLog->log('local_school_admin_restored', $admin, $school, request: $request);
        $setupNotifications->sendLifecycleNotice($admin->refresh(), UserAccountSetupNotificationService::ACCOUNT_RESTORED, $school, 'school_admin', $request->user());

        return redirect()
            ->route('admin.local-admins.index', ['status' => 'archived'])
            ->with('success', __('ui.account_restored_success'));
    }

    public function destroy(
        Request $request,
        User $admin,
        AuditLogService $auditLog,
        UserLifecycleService $lifecycle,
        UserAccountSetupNotificationService $setupNotifications
    ): RedirectResponse {
        $school = $this->localSchool();
        $this->authorizeLocalAdmin($admin, $school);
        $result = $lifecycle->deleteOrArchive($admin, $school, self::ROLES, $request->user(), $request);

        if ($result['archived']) {
            $admin->removeRole('school_admin');
            $auditLog->log('local_school_admin_delete_archived_instead', $admin, $school, request: $request);
            $setupNotifications->sendLifecycleNotice($admin->refresh(), UserAccountSetupNotificationService::ACCOUNT_ARCHIVED, $school, 'school_admin', $request->user());

            return redirect()
                ->route('admin.local-admins.index', ['status' => 'archived'])
                ->with('success', __('ui.account_delete_archived_instead'));
        }

        $auditLog->log('local_school_admin_deleted', null, $school, metadata: [
            'target_id' => $admin->id,
        ], request: $request);

        return redirect()
            ->route('admin.local-admins.index')
            ->with('success', __('ui.account_deleted_success'));
    }

    private function authorizeLocalAdmin(User $admin, School $school): void
    {
        $belongs = (int) $admin->school_id === (int) $school->id
            || $admin->schoolRoles()
                ->where('school_id', $school->id)
                ->where('role_name', 'school_admin')
                ->exists();

        abort_unless($belongs, 403);
    }

    private function localSchool(): School
    {
        $school = School::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        abort_unless($school, 404, __('ui.school_profile_required'));

        return $school;
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
