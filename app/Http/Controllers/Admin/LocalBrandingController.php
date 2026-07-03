<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandingSetting;
use App\Models\School;
use App\Services\Branding\BrandingAssetService;
use App\Services\Branding\BrandingService;
use App\Services\Branding\BrandingValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class LocalBrandingController extends Controller
{
    public function __construct(
        private BrandingService $branding,
        private BrandingAssetService $assets,
        private BrandingValidationService $validation,
    ) {}

    public function edit(): View
    {
        $school = $this->localSchool();

        return view('admin.local-branding.edit', [
            'school' => $school,
            'branding' => $this->branding->forSchool($school),
            'setting' => $this->branding->setting(BrandingSetting::SCOPE_SCHOOL, $school),
            'whiteLabelAvailable' => $this->branding->whiteLabelEnabled($school),
            'storageLinkExists' => File::exists(public_path('storage')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $school = $this->localSchool();
        $data = $request->validate($this->validation->rules());
        unset($data['logo_path'], $data['favicon_path']);

        if ((bool) ($data['white_label_enabled'] ?? false) && ! $this->branding->whiteLabelEnabled($school)) {
            throw ValidationException::withMessages([
                'white_label_enabled' => 'White-label branding is not available for the current feature configuration.',
            ]);
        }

        $this->branding->updateSchoolBranding($school, $data, $request->user());

        return back()->with('success', 'Branding saved. Your portal will use the updated school identity where supported.');
    }

    public function logo(Request $request): RedirectResponse
    {
        $school = $this->localSchool();
        $request->validate($this->validation->assetRules('logo'));

        $path = $this->assets->store($request->file('asset'), 'logo', $school, BrandingSetting::SCOPE_SCHOOL);
        $this->branding->updateSchoolBranding($school, ['logo_path' => $path], $request->user());

        return back()->with('success', 'School logo uploaded.');
    }

    public function favicon(Request $request): RedirectResponse
    {
        $school = $this->localSchool();
        $request->validate($this->validation->assetRules('favicon'));

        $path = $this->assets->store($request->file('asset'), 'favicon', $school, BrandingSetting::SCOPE_SCHOOL);
        $this->branding->updateSchoolBranding($school, ['favicon_path' => $path], $request->user());

        return back()->with('success', 'Favicon uploaded.');
    }

    public function repairStorageLink(): RedirectResponse
    {
        try {
            Artisan::call('storage:link');
        } catch (Throwable) {
            return back()->with('error', 'Storage link could not be repaired automatically. Create the public storage link from your hosting file manager or ask your host to enable it.');
        }

        return back()->with('success', 'Public storage link checked. Uploaded branding assets can now load from the browser.');
    }

    private function localSchool(): School
    {
        $school = School::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        abort_unless($school, 404, 'Create the school profile before managing local branding.');

        return $school;
    }
}
