<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Notifications\UserAccountCreatedNotification;
use App\Services\NotificationPreferenceService;
use App\Services\StaffCodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StaffUserController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $staffUsers = $school->users()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $this->manageableRoles()))
            ->with('roles')
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
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in($this->manageableRoles())],
            'staff_code' => ['nullable', 'string', 'max:100', Rule::unique('users', 'staff_code')],
            'auto_generate_staff_code' => ['nullable', 'boolean'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'must_change_password' => ['nullable', 'boolean'],
        ]);

        $staffCode = $data['staff_code'] ?: $staffCodes->generateForSchool($school, $data['role']);

        $user = User::create([
            'school_id' => $school->id,
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'staff_code' => strtoupper(trim($staffCode)),
            'password' => $data['password'],
            'must_change_password' => (bool) ($data['must_change_password'] ?? false),
        ]);

        $user->syncRoles([$data['role']]);

        if (app(NotificationPreferenceService::class)->emailEnabled('user_account_created', $school, $user, $data['role'])) {
            try {
                $user->notify(new UserAccountCreatedNotification($user, $data['role'], $school));
            } catch (\Throwable $exception) {
                Log::warning('User account created notification failed.', [
                    'user_id' => $user->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('school.staff.index')
            ->with('success', 'Staff account created successfully.');
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
        $staff->syncRoles([$data['role']]);

        return redirect()
            ->route('school.staff.index')
            ->with('success', 'Staff account updated successfully.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = auth()->user()->school;

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeStaff(User $staff, School $school): void
    {
        if ((int) $staff->school_id !== (int) $school->id || ! $staff->hasAnyRole($this->manageableRoles())) {
            abort(403, 'You cannot manage this staff account.');
        }
    }

    private function manageableRoles(): array
    {
        return ['teacher', 'result_officer'];
    }
}
