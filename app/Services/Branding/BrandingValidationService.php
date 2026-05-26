<?php

namespace App\Services\Branding;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class BrandingValidationService
{
    public function rules(): array
    {
        return [
            'brand_name' => ['nullable', 'string', 'max:120'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'favicon_path' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'email_footer_text' => ['nullable', 'string', 'max:500'],
            'login_heading' => ['nullable', 'string', 'max:160'],
            'login_subheading' => ['nullable', 'string', 'max:240'],
            'dashboard_heading' => ['nullable', 'string', 'max:160'],
            'report_footer_text' => ['nullable', 'string', 'max:500'],
            'white_label_enabled' => ['nullable', 'boolean'],
        ];
    }

    public function assetRules(string $type): array
    {
        $max = $type === 'favicon'
            ? (int) config('branding.uploads.max_favicon_kb', 128)
            : (int) config('branding.uploads.max_logo_kb', 512);

        return [
            'asset' => [
                'required',
                'file',
                'mimetypes:'.implode(',', (array) config('branding.uploads.allowed_mimetypes', [])),
                'mimes:'.implode(',', (array) config('branding.uploads.allowed_extensions', [])),
                'max:'.$max,
            ],
        ];
    }

    public function validateAsset(UploadedFile $file, string $type): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mime = strtolower((string) $file->getMimeType());
        $allowedExtensions = array_map('strtolower', (array) config('branding.uploads.allowed_extensions', []));
        $allowedMimes = array_map('strtolower', (array) config('branding.uploads.allowed_mimetypes', []));
        $maxKb = $type === 'favicon'
            ? (int) config('branding.uploads.max_favicon_kb', 128)
            : (int) config('branding.uploads.max_logo_kb', 512);

        if (! in_array($extension, $allowedExtensions, true) || ! in_array($mime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'asset' => 'Branding assets must be PNG, JPG, WEBP, or ICO images. SVG and executable files are not allowed.',
            ]);
        }

        if ($file->getSize() > ($maxKb * 1024)) {
            throw ValidationException::withMessages([
                'asset' => "The {$type} may not be greater than {$maxKb} kilobytes.",
            ]);
        }
    }

    public function sanitize(array $data): array
    {
        $allowed = array_keys($this->rules());

        return collect(Arr::only($data, $allowed))
            ->map(function (mixed $value, string $key): mixed {
                if ($value === null) {
                    return null;
                }

                if ($key === 'white_label_enabled') {
                    return (bool) $value;
                }

                if (str_ends_with($key, '_color')) {
                    return strtolower((string) $value);
                }

                return trim(strip_tags((string) $value));
            })
            ->all();
    }
}
