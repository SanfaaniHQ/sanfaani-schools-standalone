<?php

namespace App\Services;

use App\Models\School;
use App\Support\Ui\BrandingUiTokens;

class TenantThemeResolver
{
    public function __construct(
        private BrandingService $branding,
        private BrandingUiTokens $tokens,
    ) {}

    public function current(): array
    {
        return $this->forBranding($this->branding->current());
    }

    public function forSchool(?School $school): array
    {
        return $this->forBranding($this->branding->forSchool($school));
    }

    public function cssVariables(?object $branding = null): string
    {
        $theme = $branding ? $this->forBranding($branding) : $this->current();

        return $this->tokens->cssVariables($theme);
    }

    public function forBranding(object $branding): array
    {
        return [
            'name' => $branding->name,
            'primary_color' => $this->safeHex($branding->primary_color ?? null, '#4f46e5'),
            'secondary_color' => $this->safeHex($branding->secondary_color ?? null, '#0f766e'),
            'logo_url' => $branding->logo_url ?? null,
            'favicon_url' => $branding->favicon_url ?? null,
            'login_background_url' => $branding->login_background_url ?? null,
            'report_header_url' => $branding->report_header_url ?? null,
            'email_logo_url' => $branding->email_logo_url ?? null,
            'school_motto' => $branding->school_motto ?? null,
            'custom_css' => $branding->custom_css ?? null,
        ];
    }

    private function safeHex(?string $color, string $fallback): string
    {
        if (is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return $color;
        }

        return $fallback;
    }
}
