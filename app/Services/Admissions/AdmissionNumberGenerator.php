<?php

namespace App\Services\Admissions;

use App\Models\Admissions\AdmissionApplication;
use App\Models\School;
use Illuminate\Support\Str;

class AdmissionNumberGenerator
{
    public function generate(School $school): string
    {
        $schoolCode = Str::upper(Str::substr(
            preg_replace('/[^A-Za-z0-9]/', '', (string) ($school->school_code ?: $school->slug ?: 'SCH')),
            0,
            8
        ));

        do {
            $candidate = sprintf(
                'APP-%s-%s-%s',
                $schoolCode ?: 'SCH',
                now()->format('Y'),
                Str::upper(Str::random(8))
            );
        } while (AdmissionApplication::where('application_number', $candidate)->exists());

        return $candidate;
    }
}
