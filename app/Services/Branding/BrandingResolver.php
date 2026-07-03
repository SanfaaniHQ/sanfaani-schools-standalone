<?php

namespace App\Services\Branding;

use App\Models\BrandingSetting;
use App\Models\School;
use App\Services\Licensing\LicenseEntitlementService;
use App\Services\System\DeploymentModeService;
use App\Services\System\FeatureAccessService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class BrandingResolver
{
    public function __construct(
        private BrandingAssetService $assets,
        private DeploymentModeService $deployment,
        private FeatureAccessService $features,
        private LicenseEntitlementService $entitlements,
    ) {}

    public function resolve(?School $school = null): array
    {
        $defaults = $this->defaults();
        $platform = $this->fromSetting($this->activeSetting(BrandingSetting::SCOPE_PLATFORM));
        $whiteLabel = $this->whiteLabelAllowed($school)
            ? $this->fromSetting($this->activeSetting(BrandingSetting::SCOPE_WHITE_LABEL, $school))
            : [];
        $managed = $this->deployment->isManaged()
            ? $this->fromSetting($this->activeSetting(BrandingSetting::SCOPE_MANAGED_CLIENT, $school))
            : [];
        $schoolBranding = $school
            ? array_merge($this->fromSchoolColumns($school), $this->fromSetting($this->activeSetting(BrandingSetting::SCOPE_SCHOOL, $school)))
            : [];

        $resolved = array_merge($defaults, $platform, $whiteLabel, $managed, $schoolBranding);

        $resolved['initials'] = $this->initials((string) ($resolved['brand_name'] ?? $defaults['brand_name']));
        $resolved['logo_url'] = $this->assets->url($resolved['logo_path'] ?? null) ?: ($school?->logoUrl());
        $resolved['favicon_url'] = $this->assets->url($resolved['favicon_path'] ?? null) ?: ($school?->faviconUrl());
        $resolved['logo_filename'] = $this->assets->basename($resolved['logo_path'] ?? null);
        $resolved['favicon_filename'] = $this->assets->basename($resolved['favicon_path'] ?? null);

        return $resolved;
    }

    public function whiteLabelAllowed(?School $school = null): bool
    {
        if (! (bool) config('branding.white_label_enabled', false)) {
            return false;
        }

        if (! $this->features->enabled('white_label_branding', $school)) {
            return false;
        }

        if (! (bool) config('sanfaani.license_validation_enabled', false)) {
            return true;
        }

        foreach (['white_label_branding', 'website_customization'] as $feature) {
            if ($this->entitlements->explicitAccess($feature, $school) === true) {
                return true;
            }
        }

        return false;
    }

    private function activeSetting(string $scope, ?School $school = null): ?BrandingSetting
    {
        try {
            if (! Schema::hasTable('branding_settings')) {
                return null;
            }

            return BrandingSetting::query()
                ->where('scope', $scope)
                ->where('is_active', true)
                ->where(function ($query) use ($school) {
                    if ($school) {
                        $query->where('school_id', $school->id)->orWhereNull('school_id');
                    } else {
                        $query->whereNull('school_id');
                    }
                })
                ->latest()
                ->first();
        } catch (Throwable) {
            return null;
        }
    }

    private function fromSetting(?BrandingSetting $setting): array
    {
        if (! $setting) {
            return [];
        }

        return collect($setting->only([
            'brand_name',
            'logo_path',
            'favicon_path',
            'primary_color',
            'secondary_color',
            'accent_color',
            'email_footer_text',
            'login_heading',
            'login_subheading',
            'dashboard_heading',
            'report_footer_text',
            'white_label_enabled',
            'scope',
        ]))->filter(fn (mixed $value): bool => filled($value) || is_bool($value))->all() + [
            'setting_id' => $setting->id,
        ];
    }

    private function fromSchoolColumns(School $school): array
    {
        return collect([
            'brand_name' => $school->name,
            'logo_path' => $school->logo_path ?: $school->logo,
            'favicon_path' => $school->favicon_path,
            'primary_color' => $school->primary_color,
            'secondary_color' => $school->secondary_color,
            'scope' => BrandingSetting::SCOPE_SCHOOL,
        ])->filter(fn (mixed $value): bool => filled($value))->all();
    }

    private function defaults(): array
    {
        return [
            'brand_name' => (string) config('branding.defaults.brand_name', config('app.name', 'Sanfaani Schools')),
            'logo_path' => null,
            'favicon_path' => null,
            'primary_color' => (string) config('branding.defaults.primary_color', '#0f766e'),
            'secondary_color' => (string) config('branding.defaults.secondary_color', '#0f172a'),
            'accent_color' => (string) config('branding.defaults.accent_color', '#14b8a6'),
            'email_footer_text' => (string) config('branding.defaults.email_footer_text', 'Powered by Sanfaani Schools.'),
            'login_heading' => (string) config('branding.defaults.login_heading', 'Welcome back'),
            'login_subheading' => (string) config('branding.defaults.login_subheading', 'Sign in to continue.'),
            'dashboard_heading' => (string) config('branding.defaults.dashboard_heading', 'School Operations Command Center'),
            'report_footer_text' => (string) config('branding.defaults.report_footer_text', 'Generated securely by Sanfaani Schools.'),
            'white_label_enabled' => false,
            'scope' => 'default',
        ];
    }

    private function initials(string $name): string
    {
        return Str::of($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => mb_substr($word, 0, 1))
            ->implode('') ?: 'SS';
    }
}
