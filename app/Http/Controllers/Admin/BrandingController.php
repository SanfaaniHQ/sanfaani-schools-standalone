<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandingSetting;
use App\Services\Branding\BrandingAssetService;
use App\Services\Branding\BrandingService;
use App\Services\Branding\BrandingValidationService;
use App\Services\System\DeploymentModeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function __construct(
        private BrandingService $branding,
        private BrandingAssetService $assets,
        private BrandingValidationService $validation,
        private DeploymentModeService $deployment,
    ) {}

    public function edit(): View
    {
        $scope = $this->scope();

        return view('admin.branding.edit', [
            'label' => (string) config('branding.labels.'.$this->deployment->mode(), 'Platform Branding'),
            'scope' => $scope,
            'branding' => $this->branding->current(),
            'setting' => $this->branding->setting($scope),
            'whiteLabelAvailable' => $this->branding->whiteLabelEnabled(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate($this->validation->rules());
        unset($data['logo_path'], $data['favicon_path']);

        if ((bool) ($data['white_label_enabled'] ?? false) && ! $this->branding->whiteLabelEnabled()) {
            throw ValidationException::withMessages([
                'white_label_enabled' => 'White-label branding requires the white-label feature and a valid entitlement.',
            ]);
        }

        $this->updateSetting($data, $request->user());

        return back()->with('success', 'Branding settings saved safely.');
    }

    public function logo(Request $request): RedirectResponse
    {
        $request->validate($this->validation->assetRules('logo'));
        $path = $this->assets->store($request->file('asset'), 'logo', scope: $this->scope());
        $this->updateSetting(['logo_path' => $path], $request->user());

        return back()->with('success', 'Logo uploaded safely.');
    }

    public function favicon(Request $request): RedirectResponse
    {
        $request->validate($this->validation->assetRules('favicon'));
        $path = $this->assets->store($request->file('asset'), 'favicon', scope: $this->scope());
        $this->updateSetting(['favicon_path' => $path], $request->user());

        return back()->with('success', 'Favicon uploaded safely.');
    }

    private function updateSetting(array $data, $user): BrandingSetting
    {
        if ($this->scope() === BrandingSetting::SCOPE_MANAGED_CLIENT) {
            return $this->branding->updateManagedBranding($data, $user);
        }

        return $this->branding->updatePlatformBranding($data, $user);
    }

    private function scope(): string
    {
        return $this->deployment->isManaged()
            ? BrandingSetting::SCOPE_MANAGED_CLIENT
            : BrandingSetting::SCOPE_PLATFORM;
    }
}
