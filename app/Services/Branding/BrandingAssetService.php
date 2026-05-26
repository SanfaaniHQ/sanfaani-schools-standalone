<?php

namespace App\Services\Branding;

use App\Models\BrandingSetting;
use App\Models\School;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandingAssetService
{
    public function __construct(private BrandingValidationService $validation) {}

    public function store(UploadedFile $file, string $type, ?School $school = null, string $scope = BrandingSetting::SCOPE_PLATFORM): string
    {
        $this->validation->validateAsset($file, $type);

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $filename = $type.'-'.Str::uuid().'.'.$extension;

        return $file->storeAs($this->directory($scope, $school), $filename, $this->disk());
    }

    public function url(?string $path): ?string
    {
        if (! $this->isPublicBrandingPath($path)) {
            return null;
        }

        return Storage::disk($this->disk())->url(ltrim((string) $path, '/'));
    }

    public function basename(?string $path): ?string
    {
        if (! $this->isPublicBrandingPath($path)) {
            return null;
        }

        return basename((string) $path);
    }

    public function isPublicBrandingPath(?string $path): bool
    {
        if (! filled($path)) {
            return false;
        }

        $path = str_replace('\\', '/', ltrim((string) $path, '/'));

        return ! Str::contains($path, ['..', '.env', 'storage/app/private'])
            && Str::startsWith($path, 'branding/');
    }

    private function directory(string $scope, ?School $school): string
    {
        if ($school) {
            return trim(config('branding.storage.school_path', 'branding/schools'), '/').'/'.$school->id;
        }

        if ($scope === BrandingSetting::SCOPE_MANAGED_CLIENT) {
            return trim(config('branding.storage.managed_path', 'branding/managed'), '/');
        }

        return trim(config('branding.storage.platform_path', 'branding/platform'), '/');
    }

    private function disk(): string
    {
        return (string) config('branding.storage.disk', 'public');
    }
}
