<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class LocalSchoolAdminController extends Controller
{
    public function index(): View
    {
        $school = $this->localSchool();

        return view('admin.local-admins.index', [
            'school' => $school,
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

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $school = $this->localSchool();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $active = $request->boolean('is_active', true);
        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'school_id' => $school->id,
            'must_change_password' => false,
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

        $auditLog->log('local_school_admin_created', $user, $school, metadata: [
            'active' => $active,
        ], request: $request);

        return redirect()
            ->route('admin.local-admins.index')
            ->with('success', 'School admin account created.');
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

        return back()->with('success', 'Password updated for the selected school admin.');
    }

    public function disable(Request $request, User $admin, AuditLogService $auditLog): RedirectResponse
    {
        $school = $this->localSchool();
        $this->authorizeLocalAdmin($admin, $school);

        UserSchoolRole::query()
            ->where('user_id', $admin->id)
            ->where('school_id', $school->id)
            ->where('role_name', 'school_admin')
            ->update(['status' => 'inactive']);

        if (! $admin->activeSchoolRoles()->where('role_name', 'school_admin')->exists()) {
            $admin->removeRole('school_admin');
        }

        $auditLog->log('local_school_admin_disabled', $admin, $school, request: $request);

        return back()->with('success', 'School admin access disabled.');
    }

    public function enable(Request $request, User $admin, AuditLogService $auditLog): RedirectResponse
    {
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

        $auditLog->log('local_school_admin_enabled', $admin, $school, request: $request);

        return back()->with('success', 'School admin access enabled.');
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

        abort_unless($school, 404, 'Create the school profile before managing school admins.');

        return $school;
    }
}
