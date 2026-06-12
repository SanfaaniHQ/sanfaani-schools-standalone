<?php

namespace App\Services;

use App\Contracts\BrandingInterface;
use App\Models\School;
use App\Services\Branding\BrandingService as SchoolBrandingService;
use stdClass;

class BrandingService implements BrandingInterface
{
    public function __construct(private SchoolBrandingService $branding) {}

    public function current(): stdClass
    {
        return $this->forSchool(app(CurrentSchoolService::class)->get());
    }

    public function forSchool(?School $school = null): stdClass
    {
        $resolved = $school
            ? $this->branding->forSchool($school)
            : $this->branding->platform();

        if (! $school) {
            return (object) [
                'is_school_context' => false,
                'school_id' => null,
                'name' => $resolved['brand_name'] ?? null,
                'brand_name' => $resolved['brand_name'] ?? null,
                'logo_path' => $resolved['logo_path'] ?? null,
                'logo_url' => $resolved['logo_url'] ?? null,
                'favicon_url' => $resolved['favicon_url'] ?? null,
                'login_background_url' => null,
                'report_header_url' => null,
                'email_logo_url' => $resolved['logo_url'] ?? null,
                'initials' => $resolved['initials'] ?? 'SS',
                'primary_color' => $resolved['primary_color'] ?? '#0f766e',
                'secondary_color' => $resolved['secondary_color'] ?? '#0f172a',
                'accent_color' => $resolved['accent_color'] ?? '#14b8a6',
                'school_motto' => null,
                'sender_email' => null,
                'sender_name' => null,
                'custom_css' => null,
                'result_checker_slug' => null,
                'is_result_checker_enabled' => false,
                'login_heading' => $resolved['login_heading'] ?? null,
                'login_subheading' => $resolved['login_subheading'] ?? null,
                'dashboard_heading' => $resolved['dashboard_heading'] ?? null,
                'email_footer_text' => $resolved['email_footer_text'] ?? null,
                'report_footer_text' => $resolved['report_footer_text'] ?? null,
                'white_label_enabled' => (bool) ($resolved['white_label_enabled'] ?? false),
            ];
        }

        return (object) [
            'is_school_context' => true,
            'school_id' => $school->id,
            'name' => $resolved['brand_name'] ?? $school->name,
            'brand_name' => $resolved['brand_name'] ?? $school->name,
            'logo_path' => $resolved['logo_path'] ?? ($school->logo_path ?: $school->logo),
            'logo_url' => $resolved['logo_url'] ?? $school->logoUrl(),
            'favicon_url' => $resolved['favicon_url'] ?? $school->faviconUrl(),
            'login_background_url' => $school->loginBackgroundUrl(),
            'report_header_url' => $school->reportHeaderUrl(),
            'email_logo_url' => $resolved['logo_url'] ?? $school->emailLogoUrl(),
            'initials' => $resolved['initials'] ?? $school->initials(),
            'primary_color' => $resolved['primary_color'] ?? ($school->primary_color ?: '#0f766e'),
            'secondary_color' => $resolved['secondary_color'] ?? ($school->secondary_color ?: '#0f172a'),
            'accent_color' => $resolved['accent_color'] ?? '#14b8a6',
            'school_motto' => $school->school_motto,
            'sender_email' => $school->sender_email ?: $school->email,
            'sender_name' => $school->sender_name ?: $school->name,
            'custom_css' => $school->custom_css,
            'result_checker_slug' => $school->result_checker_slug,
            'is_result_checker_enabled' => (bool) $school->is_result_checker_enabled,
            'login_heading' => $resolved['login_heading'] ?? null,
            'login_subheading' => $resolved['login_subheading'] ?? null,
            'dashboard_heading' => $resolved['dashboard_heading'] ?? null,
            'email_footer_text' => $resolved['email_footer_text'] ?? null,
            'report_footer_text' => $resolved['report_footer_text'] ?? null,
            'white_label_enabled' => (bool) ($resolved['white_label_enabled'] ?? false),
        ];
    }
}
