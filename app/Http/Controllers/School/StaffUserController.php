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
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffUserController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $staffUsers = User::query()
            ->where(function ($query) use ($school) {
                $query->where('school_id', $school->id)
                    ->orWhereHas('activeSchoolRoles', fn ($query) => $query->where('school_id', $school->id));
            })
            ->where(function ($query) {
                $query->whereHas('roles', fn ($query) => $query->whereIn('name', $this->manageableRoles()))
                    ->orWhereHas('activeSchoolRoles', fn ($query) => $query->whereIn('role_name', $this->manageableRoles()));
            })
            ->with(['roles', 'schoolRoles' => fn ($query) => $query->where('school_id', $school->id)])
            ->latest()
            ->paginate(10);

        return view('school.staff.index', [
            'school' => $school,
            'staffUsers' => $staffUsers,
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

    public function store(Request $request, StaffCodeGeneratorService $staffCodes)
    {
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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'must_change_password' => ['nullable', 'boolean'],
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
                'password' => $data['password'],
                'must_change_password' => (bool) ($data['must_change_password'] ?? false),
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

        event(StaffTransactionalEmailRequested::accountCreated($user->refresh(), $school, $data['role'], $wasExistingUser));

        return redirect()
            ->route('school.staff.index')
            ->with('success', $wasExistingUser ? 'Existing user was granted access to this school.' : 'Staff account created successfully.');
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
            ->with('success', 'Staff account updated successfully.');
    }

    public function disable(Request $request, User $staff)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStaff($staff, $school);

        // Prevent disabling Super Admin or School Admin
        if ($staff->hasAnyRole(['super_admin', 'school_admin'])) {
            abort(403, 'You cannot disable this user.');
        }

        $role = $this->currentSchoolRole($staff, $school) ?? 'staff';

        // Set user_school_roles to inactive for this school
        UserSchoolRole::where('user_id', $staff->id)
            ->where('school_id', $school->id)
            ->whereIn('role_name', $this->manageableRoles())
            ->update(['status' => 'inactive']);

        // Check if user has other active school roles
        $hasOtherActiveRoles = UserSchoolRole::where('user_id', $staff->id)
            ->where('school_id', '!=', $school->id)
            ->where('status', 'active')
            ->exists();

        // Remove global Spatie roles only if no other active contexts exist
        if (! $hasOtherActiveRoles) {
            foreach ($this->manageableRoles() as $role) {
                if ($staff->hasRole($role)) {
                    $staff->removeRole($role);
                }
            }
        }

        if (class_exists(AuditLogService::class)) {
            app(AuditLogService::class)->log('staff_access_disabled', $staff, $school, request: $request);
        }

        event(StaffTransactionalEmailRequested::accountStatusChanged($staff, $school, $role, false));

        return redirect()
            ->route('school.staff.index')
            ->with('success', 'Staff access disabled successfully.');
    }

    public function enable(Request $request, User $staff)
    {
        $school = $this->currentSchoolOrFail();

        // Get the staff member's role for this school
        $schoolRole = UserSchoolRole::where('user_id', $staff->id)
            ->where('school_id', $school->id)
            ->whereIn('role_name', $this->manageableRoles())
            ->first();

        if (! $schoolRole) {
            abort(403, 'This staff member does not have a role in this school.');
        }

        // Assign Spatie role if not already assigned
        if (! $staff->hasRole($schoolRole->role_name)) {
            $staff->assignRole($schoolRole->role_name);
        }

        // Set school_id if null or mismatched
        if (! $staff->school_id || (int) $staff->school_id !== (int) $school->id) {
            $staff->update(['school_id' => $school->id]);
        }

        // Update user_school_roles to active
        $schoolRole->update([
            'status' => 'active',
            'assigned_by' => auth()->id(),
        ]);

        if (class_exists(AuditLogService::class)) {
            app(AuditLogService::class)->log('staff_access_enabled', $staff, $school, request: $request);
        }

        event(StaffTransactionalEmailRequested::accountStatusChanged($staff->refresh(), $school, $schoolRole->role_name, true));

        return redirect()
            ->route('school.staff.index')
            ->with('success', 'Staff access enabled successfully.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeStaff(User $staff, School $school): void
    {
        $hasSchoolRole = $staff->activeSchoolRoles()
            ->where('school_id', $school->id)
            ->whereIn('role_name', $this->manageableRoles())
            ->exists();

        if (((int) $staff->school_id !== (int) $school->id && ! $hasSchoolRole) || ! $staff->hasAnyRole($this->manageableRoles())) {
            abort(403, 'You cannot manage this staff account.');
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
}
