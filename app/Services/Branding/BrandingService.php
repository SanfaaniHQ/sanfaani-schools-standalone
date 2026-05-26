<?php

namespace App\Services\Branding;

use App\Models\BrandingSetting;
use App\Models\School;
use App\Models\User;

class BrandingService
{
    public function __construct(
        private BrandingResolver $resolver,
        private BrandingValidationService $validation,
    ) {}

    public function current(?School $school = null): array
    {
        return $this->resolver->resolve($school);
    }

    public function forSchool(School $school): array
    {
        return $this->resolver->resolve($school);
    }

    public function platform(): array
    {
        return $this->resolver->resolve();
    }

    public function updatePlatformBranding(array $data, ?User $user = null): BrandingSetting
    {
        return $this->update(BrandingSetting::SCOPE_PLATFORM, null, $data, $user);
    }

    public function updateManagedBranding(array $data, ?User $user = null): BrandingSetting
    {
        return $this->update(BrandingSetting::SCOPE_MANAGED_CLIENT, null, $data, $user);
    }

    public function updateSchoolBranding(School $school, array $data, ?User $user = null): BrandingSetting
    {
        return $this->update(BrandingSetting::SCOPE_SCHOOL, $school, $data, $user);
    }

    public function whiteLabelEnabled(?School $school = null): bool
    {
        return $this->resolver->whiteLabelAllowed($school);
    }

    public function emailFooter(?School $school = null): string
    {
        return (string) data_get($this->current($school), 'email_footer_text', '');
    }

    public function reportFooter(?School $school = null): string
    {
        return (string) data_get($this->current($school), 'report_footer_text', '');
    }

    public function setting(string $scope, ?School $school = null): ?BrandingSetting
    {
        return BrandingSetting::query()
            ->where('scope', $scope)
            ->where('is_active', true)
            ->when($school, fn ($query) => $query->where('school_id', $school->id), fn ($query) => $query->whereNull('school_id'))
            ->latest()
            ->first();
    }

    private function update(string $scope, ?School $school, array $data, ?User $user): BrandingSetting
    {
        $payload = $this->validation->sanitize($data);

        return BrandingSetting::query()->updateOrCreate(
            [
                'scope' => $scope,
                'school_id' => $school?->id,
            ],
            array_merge($payload, [
                'is_active' => true,
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ]),
        );
    }
}
