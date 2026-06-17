<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Services\CurrentSchoolService;
use App\Services\SchoolRoleFeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureControlController extends Controller
{
    public function __construct(
        private CurrentSchoolService $currentSchool,
        private SchoolRoleFeatureService $features
    ) {}

    public function index(): View
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $featuresByRole = [];

        foreach ($this->features->roleNames() as $roleName) {
            $featuresByRole[$roleName] = $this->features->getFeatures($school->id, $roleName);
        }

        return view('school.feature-control.index', [
            'school' => $school,
            'roleNames' => $this->features->roleNames(),
            'catalog' => $this->features->groupedCatalog(),
            'featuresByRole' => $featuresByRole,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $payload = $request->input('features', []);

        foreach ($this->features->roleNames() as $roleName) {
            $enabledKeys = array_keys((array) ($payload[$roleName] ?? []));
            $this->features->updateFeatures($school->id, $roleName, $enabledKeys, $request->user()->id);
        }

        return back()->with('success', __('ui.feature_controls_updated'));
    }
}
