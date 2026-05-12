<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Collection;

class ResultGradingService
{
    public function activeScales(School $school): Collection
    {
        return $school->gradingScales()
            ->where('status', 'active')
            ->orderByDesc('min_score')
            ->get();
    }

    public function calculate(School $school, float $score): array
    {
        return $this->calculateFromScales($this->activeScales($school), $score);
    }

    public function calculateFromScales(iterable $gradingScales, float $score): array
    {
        foreach ($gradingScales as $gradingScale) {
            if ((float) $gradingScale->min_score <= $score && (float) $gradingScale->max_score >= $score) {
                return [
                    'grade' => $gradingScale->grade,
                    'remark' => $gradingScale->remark,
                    'is_pass' => $gradingScale->is_pass,
                ];
            }
        }

        return [
            'grade' => null,
            'remark' => null,
            'is_pass' => null,
        ];
    }
}
