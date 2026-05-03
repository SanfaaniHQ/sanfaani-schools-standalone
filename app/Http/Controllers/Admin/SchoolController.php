<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\AuditLogService;
use App\Services\SchoolCodeGeneratorService;
use Illuminate\Http\Request;
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
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'subscription_status' => ['required', Rule::in(['trial', 'active', 'expired'])],
            'default_language' => ['required', Rule::in(['en', 'fr', 'ar'])],
            'supports_rtl' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['school_code'] = filled($data['school_code'] ?? null)
            ? Str::upper(trim($data['school_code']))
            : app(SchoolCodeGeneratorService::class)->generateForName($data['name']);
        $data['supports_rtl'] = (bool) ($data['supports_rtl'] ?? false);

        School::create($data);

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
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'subscription_status' => ['required', Rule::in(['trial', 'active', 'expired'])],
            'default_language' => ['required', Rule::in(['en', 'fr', 'ar'])],
            'supports_rtl' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['name'], $school->id);
        $data['school_code'] = filled($data['school_code'] ?? null)
            ? Str::upper(trim($data['school_code']))
            : app(SchoolCodeGeneratorService::class)->generateForName($data['name']);
        $data['supports_rtl'] = (bool) ($data['supports_rtl'] ?? false);

        $school->update($data);

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'School updated successfully.');
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
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
