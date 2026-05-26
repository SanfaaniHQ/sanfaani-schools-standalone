<?php

namespace App\Services;

use App\Contracts\BrandingInterface;
use App\Models\School;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use stdClass;

class BrandingService implements BrandingInterface
{
    public function current(): stdClass
    {
        return $this->forSchool(app(CurrentSchoolService::class)->get());
    }

    public function forSchool(?School $school = null): stdClass
    {
        if (! $school) {
            return (object) [
                'is_school_context' => false,
                'school_id' => null,
                'name' => null,
                'logo_path' => null,
                'logo_url' => null,
                'favicon_url' => null,
                'login_background_url' => null,
                'report_header_url' => null,
                'email_logo_url' => null,
                'initials' => null,
                'primary_color' => '#4f46e5',
                'secondary_color' => '#0f766e',
                'school_motto' => null,
                'sender_email' => null,
                'sender_name' => null,
                'custom_css' => null,
                'result_checker_slug' => null,
                'is_result_checker_enabled' => false,
            ];
        }

        $logoPath = $school->logo_path ?: $school->logo;

        return (object) [
            'is_school_context' => true,
            'school_id' => $school->id,
            'name' => $school->name,
            'logo_path' => $logoPath,
            'logo_url' => $this->assetUrl($logoPath) ?: $school->logoUrl(),
            'favicon_url' => $school->faviconUrl(),
            'login_background_url' => $school->loginBackgroundUrl(),
            'report_header_url' => $school->reportHeaderUrl(),
            'email_logo_url' => $school->emailLogoUrl(),
            'initials' => $school->initials(),
            'primary_color' => $school->primary_color ?: '#4f46e5',
            'secondary_color' => $school->secondary_color ?: '#0f766e',
            'school_motto' => $school->school_motto,
            'sender_email' => $school->sender_email ?: $school->email,
            'sender_name' => $school->sender_name ?: $school->name,
            'custom_css' => $school->custom_css,
            'result_checker_slug' => $school->result_checker_slug,
            'is_result_checker_enabled' => (bool) $school->is_result_checker_enabled,
        ];
    }

    private function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = str_replace('\\', '/', ltrim((string) $path, '/'));

        if (Str::contains($path, ['..', '.env', 'storage/app/private', ':'])) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
