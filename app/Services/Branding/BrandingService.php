<?php

namespace App\Services\Branding;

use App\Models\BrandingSetting;
use App\Models\School;
use App\Models\User;
use App\Services\AuditLogService;

class BrandingService
{
    public function __construct(
        private BrandingResolver $resolver,
        private BrandingValidationService $validation,
        private AuditLogService $auditLog,
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
        $existing = $this->setting($scope, $school);
        $oldValues = $existing
            ? $existing->only(array_keys($payload))
            : [];

        $setting = BrandingSetting::query()->updateOrCreate(
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

        $changedFields = $this->changedFields($oldValues, $payload);

        if ($changedFields !== []) {
            $this->auditBrandingChange($setting, $school, $scope, $changedFields, $payload);
        }

        return $setting;
    }

    private function changedFields(array $oldValues, array $newValues): array
    {
        return collect($newValues)
            ->filter(fn (mixed $value, string $field): bool => ! array_key_exists($field, $oldValues) || $oldValues[$field] !== $value)
            ->keys()
            ->values()
            ->all();
    }

    private function auditBrandingChange(BrandingSetting $setting, ?School $school, string $scope, array $changedFields, array $payload): void
    {
        $metadata = [
            'school_id' => $school?->id,
            'scope' => $scope,
            'branding_setting_id' => $setting->id,
            'changed_fields' => $changedFields,
            'logo_updated' => in_array('logo_path', $changedFields, true),
            'favicon_updated' => in_array('favicon_path', $changedFields, true),
            'primary_color_updated' => in_array('primary_color', $changedFields, true),
            'secondary_color_updated' => in_array('secondary_color', $changedFields, true),
            'accent_color_updated' => in_array('accent_color', $changedFields, true),
            'white_label_enabled' => (bool) ($payload['white_label_enabled'] ?? $setting->white_label_enabled),
        ];

        $baseAction = $school ? 'school_branding_updated' : $scope.'_branding_updated';

        $this->auditLog->log($baseAction, $setting, $school, metadata: $metadata);

        if ($school && in_array('logo_path', $changedFields, true)) {
            $this->auditLog->log('school_branding_logo_updated', $setting, $school, metadata: $metadata);
        }

        if ($school && collect($changedFields)->intersect(['primary_color', 'secondary_color', 'accent_color'])->isNotEmpty()) {
            $this->auditLog->log('school_branding_colors_updated', $setting, $school, metadata: $metadata);
        }

        if ($school && in_array('white_label_enabled', $changedFields, true)) {
            $this->auditLog->log('school_branding_powered_by_updated', $setting, $school, metadata: $metadata);
        }
    }
}
