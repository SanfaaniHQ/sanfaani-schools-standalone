<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Str;

class SchoolCodeGeneratorService
{
    public function generateForName(string $schoolName): string
    {
        $prefix = $this->prefixFromName($schoolName);
        $nextNumber = School::withTrashed()
            ->where('school_code', 'like', "{$prefix}-SCH-%")
            ->count() + 1;

        do {
            $candidate = "{$prefix}-SCH-".str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (School::withTrashed()->where('school_code', $candidate)->exists());

        return $candidate;
    }

    public function generateForSchool(School $school): string
    {
        return $this->generateForName($school->name);
    }

    private function prefixFromName(string $schoolName): string
    {
        $words = preg_split('/[\s\-_]+/', $schoolName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = collect($words)
            ->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))
            ->join('');

        if ($initials === '') {
            $initials = Str::upper(Str::substr(Str::slug($schoolName, ''), 0, 4));
        }

        return $initials !== '' ? Str::substr($initials, 0, 5) : 'SCH';
    }
}
