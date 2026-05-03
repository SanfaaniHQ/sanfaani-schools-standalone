<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Str;

class StaffCodeGeneratorService
{
    public function generateForSchool(School $school, string $role): string
    {
        $prefix = $this->schoolPrefix($school);
        $roleCode = $this->roleCode($role);
        $year = now()->format('Y');
        $nextNumber = User::where('school_id', $school->id)
            ->where('staff_code', 'like', "{$prefix}/{$roleCode}/{$year}/%")
            ->count() + 1;

        do {
            $candidate = "{$prefix}/{$roleCode}/{$year}/" . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (User::where('staff_code', $candidate)->exists());

        return $candidate;
    }

    private function roleCode(string $role): string
    {
        return match ($role) {
            'result_officer' => 'RO',
            'teacher' => 'TCH',
            'school_admin' => 'ADM',
            default => 'STAFF',
        };
    }

    private function schoolPrefix(School $school): string
    {
        if ($school->school_code) {
            return Str::upper(Str::before($school->school_code, '-'));
        }

        $words = preg_split('/[\s\-_]+/', $school->slug ?: $school->name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = collect($words)
            ->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))
            ->join('');

        return $initials !== '' ? Str::substr($initials, 0, 5) : 'SCH';
    }
}
