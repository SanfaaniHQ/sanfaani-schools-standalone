<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\AuditLogService;
use App\Services\AuditService;
use App\Services\CommunicationService;
use App\Services\NotificationPreferenceService;
use App\Services\SchoolCodeGeneratorService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::withTrashed()->latest()->paginate(10);

        return view('admin.schools.index', [
            'schools' => $schools,
        ]);
    }

    public function create()
    {
        return view('admin.schools.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'school_code' => ['nullable', 'string', 'max:50', Rule::unique('schools', 'school_code')],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:255'],
            'logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'subscription_status' => ['required', Rule::in(['trial', 'active', 'expired'])],
            'default_language' => ['required', Rule::in(['en', 'fr', 'ar'])],
            'supports_rtl' => ['nullable', 'boolean'],
        ]);

        unset($data['logo_upload']);

        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['school_code'] = filled($data['school_code'] ?? null)
            ? Str::upper(trim($data['school_code']))
            : app(SchoolCodeGeneratorService::class)->generateForName($data['name']);
        $data['supports_rtl'] = (bool) ($data['supports_rtl'] ?? false);

        if ($request->hasFile('logo_upload')) {
            $data['logo'] = $request->file('logo_upload')->store('schools/logos', 'public');
        }

        $school = School::create($data);

        if (
            filled($school->email)
            && app(NotificationPreferenceService::class)->emailEnabled('school_created', $school)
        ) {
            app(CommunicationService::class)->sendPlatformEmail(
                $school->email,
                'Your school workspace is ready',
                'School onboarding',
                'Your school profile has been created successfully. You can now proceed with school admin onboarding and setup.',
                'school_onboarding',
                ['school_id' => $school->id, 'school_name' => $school->name],
                'platform_transactional'
            );
        }

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'School created successfully.');
    }

    public function edit(School $school)
    {
        return view('admin.schools.edit', [
            'school' => $school,
        ]);
    }

    public function update(Request $request, School $school)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'school_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('schools', 'school_code')->ignore($school->id),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:255'],
            'logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'subscription_status' => ['required', Rule::in(['trial', 'active', 'expired'])],
            'default_language' => ['required', Rule::in(['en', 'fr', 'ar'])],
            'supports_rtl' => ['nullable', 'boolean'],
        ]);

        unset($data['logo_upload']);

        $data['slug'] = $this->generateUniqueSlug($data['name'], $school->id);
        $data['school_code'] = filled($data['school_code'] ?? null)
            ? Str::upper(trim($data['school_code']))
            : app(SchoolCodeGeneratorService::class)->generateForName($data['name']);
        $data['supports_rtl'] = (bool) ($data['supports_rtl'] ?? false);

        if ($request->hasFile('logo_upload')) {
            $this->deleteStoredFile($school->logo);
            $data['logo'] = $request->file('logo_upload')->store('schools/logos', 'public');
        }

        $school->update($data);

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'School updated successfully.');
    }

    public function startSupportAccess(School $school, AuditLogService $auditLog, Request $request)
    {
        if ($school->status !== 'active') {
            return back()->with('error', 'Support access is available only for active schools.');
        }

        $data = $request->validate([
            'role_context' => ['nullable', Rule::in(['school_admin', 'result_officer', 'teacher'])],
            'support_reason' => ['nullable', 'string', 'max:500'],
        ]);

        session([
            'is_support_session' => true,
            'support_school_id' => $school->id,
            'support_role_context' => $data['role_context'] ?? 'school_admin',
            'support_reason' => $data['support_reason'] ?? 'Platform support review',
            'support_access_started_by' => auth()->id(),
            'support_access_started_at' => now()->toDateTimeString(),
            'support_access_last_confirmed_at' => now()->toDateTimeString(),
        ]);

        TenantContext::set($school->id, session('support_role_context'));

        $auditLog->log('support_access_started', $school, $school, metadata: [
            'support_school_id' => $school->id,
            'role_context' => session('support_role_context'),
            'reason' => session('support_reason'),
        ], request: $request);

        AuditService::log('support', 'support_access_started', [
            'school_id' => $school->id,
            'role_context' => session('support_role_context'),
            'reason' => session('support_reason'),
        ]);

        return redirect()
            ->route('school.dashboard')
            ->with('success', 'Support access started for '.$school->name.'.');
    }

    public function continueSupportAccess(AuditLogService $auditLog, Request $request)
    {
        $school = session('support_school_id') ? School::find(session('support_school_id')) : null;

        if (! $school) {
            return redirect()
                ->route('admin.schools.index')
                ->with('error', 'Support access is no longer active.');
        }

        session(['support_access_last_confirmed_at' => now()->toDateTimeString()]);

        $auditLog->log('support_access_continued', $school, $school, metadata: [
            'support_school_id' => $school->id,
            'role_context' => session('support_role_context', 'school_admin'),
            'reason' => session('support_reason'),
        ], request: $request);

        return back()->with('success', 'Support access continued. This action is logged for security.');
    }

    public function stopSupportAccess(AuditLogService $auditLog, Request $request)
    {
        $school = session('support_school_id') ? School::find(session('support_school_id')) : null;

        $auditLog->log('support_access_stopped', $school, $school, metadata: [
            'support_school_id' => session('support_school_id'),
            'role_context' => session('support_role_context'),
            'reason' => session('support_reason'),
            'started_at' => session('support_access_started_at'),
            'last_confirmed_at' => session('support_access_last_confirmed_at'),
        ], request: $request);

        AuditService::log('support', 'support_access_stopped', [
            'school_id' => session('support_school_id'),
            'role_context' => session('support_role_context'),
            'reason' => session('support_reason'),
        ]);

        session()->forget([
            'is_support_session',
            'support_school_id',
            'support_role_context',
            'support_reason',
            'support_access_started_by',
            'support_access_started_at',
            'support_access_last_confirmed_at',
        ]);

        TenantContext::clear();

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'Support access ended.');
    }

    public function revokeSupportSession(AuditLogService $auditLog, Request $request)
    {
        return $this->stopSupportAccess($auditLog, $request);
    }

    public function archive(School $school, AuditLogService $auditLog, Request $request)
    {
        $oldValues = $school->only(['status']);

        $school->update(['status' => 'inactive']);
        $school->delete();

        $auditLog->log('school_archived', $school, $school, $oldValues, ['status' => 'inactive'], request: $request);

        return back()->with('success', 'School archived safely.');
    }

    public function restore(int $school, AuditLogService $auditLog, Request $request)
    {
        $school = School::onlyTrashed()->findOrFail($school);
        $school->restore();
        $school->update(['status' => 'active']);

        $auditLog->log('school_restored', $school, $school, newValues: ['status' => 'active'], request: $request);

        return back()->with('success', 'School restored successfully.');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'school';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            School::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function deleteStoredFile(?string $path): void
    {
        if (! filled($path) || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
