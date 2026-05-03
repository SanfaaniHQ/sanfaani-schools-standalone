<?php

namespace App\Services;

use App\Models\AdmissionNumberSetting;
use App\Models\School;
use App\Models\Student;
use Illuminate\Support\Str;

class AdmissionNumberGeneratorService
{
    public function generateForSchool(School $school): string
    {
        $setting = AdmissionNumberSetting::where('school_id', $school->id)
            ->lockForUpdate()
            ->first();

        if (! $setting) {
            $setting = AdmissionNumberSetting::create([
                'school_id' => $school->id,
                'prefix' => $this->defaultPrefix($school),
                'separator' => '/',
                'year_format' => 'Y',
                'next_number' => 1,
                'padding_length' => 3,
                'status' => 'active',
            ]);

            $setting = AdmissionNumberSetting::whereKey($setting->id)
                ->lockForUpdate()
                ->firstOrFail();
        }

        $setting = $this->applyResetCycle($setting);

        $nextNumber = max(1, (int) $setting->next_number);
        $paddingLength = max(1, (int) $setting->padding_length);

        do {
            $candidate = $this->formatNumber($setting, $nextNumber, $paddingLength);
            $exists = Student::withTrashed()
                ->where('school_id', $school->id)
                ->where('admission_number', $candidate)
                ->exists();

            $nextNumber++;
        } while ($exists);

        $setting->update(['next_number' => $nextNumber]);

        return $candidate;
    }

    public function previewForSetting(AdmissionNumberSetting $setting): string
    {
        return $this->formatNumber(
            $setting,
            max(1, (int) $setting->next_number),
            max(1, (int) $setting->padding_length)
        );
    }

    public function defaultPrefixForSchool(School $school): string
    {
        return $this->defaultPrefix($school);
    }

    private function formatNumber(AdmissionNumberSetting $setting, int $nextNumber, int $paddingLength): string
    {
        $separator = $setting->separator ?? '/';
        $parts = array_filter([
            $setting->prefix ? Str::upper($setting->prefix) : null,
            $this->yearSegment($setting->year_format),
            str_pad((string) $nextNumber, $paddingLength, '0', STR_PAD_LEFT),
            $setting->suffix ? Str::upper($setting->suffix) : null,
        ], fn ($part) => $part !== null && $part !== '');

        return implode($separator, $parts);
    }

    private function applyResetCycle(AdmissionNumberSetting $setting): AdmissionNumberSetting
    {
        if ($setting->reset_cycle !== 'yearly') {
            return $setting;
        }

        $resetKey = now()->format('Y');
        $metadata = $setting->metadata ?? [];

        if (! array_key_exists('last_reset_key', $metadata)) {
            $metadata['last_reset_key'] = $resetKey;
            $setting->update(['metadata' => $metadata]);

            return $setting->fresh();
        }

        if (($metadata['last_reset_key'] ?? null) === $resetKey) {
            return $setting;
        }

        $metadata['last_reset_key'] = $resetKey;

        $setting->update([
            'next_number' => 1,
            'metadata' => $metadata,
        ]);

        return $setting->fresh();
    }

    private function yearSegment(?string $format): ?string
    {
        $format = trim((string) $format);

        if ($format === '' || $format === 'none') {
            return null;
        }

        return now()->format($format);
    }

    private function defaultPrefix(School $school): string
    {
        $source = $school->slug ?: $school->name;
        $words = preg_split('/[\s\-_]+/', (string) $source, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = collect($words)
            ->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))
            ->join('');

        if ($initials === '') {
            $initials = Str::upper(Str::substr(Str::slug($school->name, ''), 0, 4));
        }

        return $initials !== '' ? Str::substr($initials, 0, 5) : 'SCH';
    }
}
