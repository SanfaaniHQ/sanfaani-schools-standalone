<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolFeatureOverride;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolFeatureOverrideController extends Controller
{
    public function index()
    {
        return view('admin.feature-overrides.index', [
            'overrides' => SchoolFeatureOverride::with(['school', 'createdBy'])->latest()->paginate(15),
            'schools' => School::orderBy('name')->get(),
            'featureKeys' => SubscriptionPlanController::featureKeys(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['required', Rule::exists('schools', 'id')],
            'feature_key' => ['required', Rule::in(array_keys(SubscriptionPlanController::featureKeys()))],
            'is_enabled' => ['required', 'boolean'],
            'limit_value' => ['nullable', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        SchoolFeatureOverride::updateOrCreate(
            [
                'school_id' => $data['school_id'],
                'feature_key' => $data['feature_key'],
            ],
            $data + ['created_by' => auth()->id()]
        );

        return back()->with('success', 'Feature override saved.');
    }
}
