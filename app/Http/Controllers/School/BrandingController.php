<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\Branding\BrandingAssetService;
use App\Services\Branding\BrandingService;
use App\Services\Branding\BrandingValidationService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function __construct(
        private CurrentSchoolService $currentSchool,
        private BrandingService $branding,
        private BrandingAssetService $assets,
        private BrandingValidationService $validation,
    ) {}

    public function edit(): View
    {
        $school = $this->schoolOrFail();

        return view('school.branding.edit', [
            'school' => $school,
            'branding' => $this->branding->forSchool($school),
            'setting' => $this->branding->setting('school', $school),
            'whiteLabelAvailable' => $this->branding->whiteLabelEnabled($school),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $school = $this->schoolOrFail();
        $data = $request->validate($this->validation->rules());
        unset($data['logo_path'], $data['favicon_path']);

        if ((bool) ($data['white_label_enabled'] ?? false) && ! $this->branding->whiteLabelEnabled($school)) {
            throw ValidationException::withMessages([
                'white_label_enabled' => 'White-label branding requires the white-label feature and a valid entitlement.',
            ]);
        }

        $this->branding->updateSchoolBranding($school, $data, $request->user());

        return back()->with('success', 'School branding saved safely.');
    }

    public function logo(Request $request): RedirectResponse
    {
        $school = $this->schoolOrFail();
        $request->validate($this->validation->assetRules('logo'));

        $path = $this->assets->store($request->file('asset'), 'logo', $school, 'school');
        $this->branding->updateSchoolBranding($school, ['logo_path' => $path], $request->user());

        return back()->with('success', 'School logo uploaded safely.');
    }

    public function favicon(Request $request): RedirectResponse
    {
        $school = $this->schoolOrFail();
        $request->validate($this->validation->assetRules('favicon'));

        $path = $this->assets->store($request->file('asset'), 'favicon', $school, 'school');
        $this->branding->updateSchoolBranding($school, ['favicon_path' => $path], $request->user());

        return back()->with('success', 'School favicon uploaded safely.');
    }

    private function schoolOrFail(): School
    {
        $school = $this->currentSchool->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
