<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::latest()->paginate(10);

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
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'subscription_status' => ['required', Rule::in(['trial', 'active', 'expired'])],
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['name']);

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
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'subscription_status' => ['required', Rule::in(['trial', 'active', 'expired'])],
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['name'], $school->id);

        $school->update($data);

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'School updated successfully.');
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
            School::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
