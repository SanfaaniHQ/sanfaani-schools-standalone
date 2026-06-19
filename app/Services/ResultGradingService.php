<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

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
        $grading = $this->calculateFromScales($this->activeScales($school), $score);
        $grading['is_pass'] = $score >= $this->passMark($school);

        return $grading;
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

    public function passMark(School $school): float
    {
        try {
            if (! Schema::hasTable('school_result_settings')) {
                return 40.0;
            }

            return (float) ($school->resultSetting()->value('pass_mark') ?? 40);
        } catch (Throwable) {
            return 40.0;
        }
    }
}
