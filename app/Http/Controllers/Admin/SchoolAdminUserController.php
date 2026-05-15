<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;

class SchoolAdminUserController extends Controller
{
    public function index(School $school)
    {
        $admins = User::query()
            ->where(function ($q) use ($school) {
                $q->where('school_id', $school->id)
                    ->orWhereHas('schoolRoles', fn ($q) => $q->where('school_id', $school->id)
                        ->where('role_name', 'school_admin')
                    );
            })
            ->whereHas('roles', fn ($q) => $q->where('name', 'school_admin'))
            ->with(['roles', 'schoolRoles' => fn ($q) => $q->where('school_id', $school->id)])
            ->latest()
            ->get();

        return view('admin.schools.admins.index', compact('school', 'admins'));
    }

    public function create(School $school)
    {
        return view('admin.schools.admins.create', compact('school'));
    }

    public function store(Request $request, School $school, AuditLogService $auditLog)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'send_reset_link' => ['nullable', 'boolean'],
        ]);

        $email = strtolower(trim($data['email']));
        $existingUser = User::where('email', $email)->first();

        if ($existingUser && $existingUser->hasRole('super_admin')) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'This email belongs to a Super Admin account and cannot be assigned as a School Admin.']);
        }

        if ($existingUser) {
            $user = $existingUser;

            // Preserve school_id if user already belongs to a different school
            if (! $user->school_id || (int) $user->school_id === (int) $school->id) {
                $user->update(['school_id' => $school->id]);
            }
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make($data['password']),
                'school_id' => $school->id,
            ]);
        }

        // Assign Spatie role if not already assigned
        if (! $user->hasRole('school_admin')) {
            $user->assignRole('school_admin');
        }

        // Upsert UserSchoolRole
        if (class_exists(UserSchoolRole::class)) {
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
        }

        // Send reset link if requested
        if ($request->boolean('send_reset_link')) {
            try {
                Password::sendResetLink(['email' => $user->email]);
            } catch (\Throwable $e) {
                // Silent — link is best-effort
            }
        }

        $auditLog->log('school_admin_created', $user, $school, request: $request);

        return redirect()
            ->route('admin.schools.admins.index', $school)
            ->with('success', 'School Admin account created successfully.');
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
            ->with('success', 'Password reset successfully.');
    }

    public function sendResetLink(Request $request, School $school, User $admin, AuditLogService $auditLog)
    {
        $this->authorizeAdmin($admin, $school);

        try {
            Password::sendResetLink(['email' => $admin->email]);
            $message = 'Password reset link has been sent if mail is configured.';
        } catch (\Throwable $e) {
            $message = 'Reset link could not be sent. Check mail settings.';
        }

        $auditLog->log('school_admin_password_reset_link_sent', $admin, $school, request: $request);

        return back()->with('success', $message);
    }

    public function disable(Request $request, School $school, User $admin, AuditLogService $auditLog)
    {
        $this->authorizeAdmin($admin, $school);

        // Set user_school_roles to inactive
        if (class_exists(UserSchoolRole::class)) {
            UserSchoolRole::where('user_id', $admin->id)
                ->where('school_id', $school->id)
                ->where('role_name', 'school_admin')
                ->update(['status' => 'inactive']);

            // Check if user has other active school contexts
            $hasOtherActiveRoles = UserSchoolRole::where('user_id', $admin->id)
                ->where('school_id', '!=', $school->id)
                ->where('status', 'active')
                ->exists();

            // Only remove global Spatie role if no other active contexts exist
            if (! $hasOtherActiveRoles) {
                $admin->removeRole('school_admin');
            }
        }

        $auditLog->log('school_admin_access_disabled', $admin, $school, request: $request);

        return redirect()
            ->route('admin.schools.admins.index', $school)
            ->with('success', 'School Admin access disabled successfully.');
    }

    public function enable(Request $request, School $school, User $admin, AuditLogService $auditLog)
    {
        $this->authorizeAdmin($admin, $school);

        // Assign Spatie role if not already assigned
        if (! $admin->hasRole('school_admin')) {
            $admin->assignRole('school_admin');
        }

        // Set users.school_id if null or mismatched
        if (! $admin->school_id || (int) $admin->school_id !== (int) $school->id) {
            $admin->update(['school_id' => $school->id]);
        }

        // Upsert UserSchoolRole to active
        if (class_exists(UserSchoolRole::class)) {
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
        }

        $auditLog->log('school_admin_access_enabled', $admin, $school, request: $request);

        return redirect()
            ->route('admin.schools.admins.index', $school)
            ->with('success', 'School Admin access enabled successfully.');
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
            abort(403, 'This user does not belong to the specified school.');
        }
    }
}
