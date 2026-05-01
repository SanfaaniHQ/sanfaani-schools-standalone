<?php

namespace App\Services;

use App\Models\School;

class ResultGradingService
{
    public function calculate(School $school, float $score): array
    {
        $gradingScale = $school->gradingScales()
            ->where('status', 'active')
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->orderByDesc('min_score')
            ->first();

        if ($gradingScale) {
            return [
                'grade' => $gradingScale->grade,
                'remark' => $gradingScale->remark,
                'is_pass' => $gradingScale->is_pass,
            ];
        }

        return $this->fallback($score);
    }

    private function fallback(float $score): array
    {
        return match (true) {
            $score >= 70 => [
                'grade' => 'A',
                'remark' => 'Excellent',
                'is_pass' => true,
            ],
            $score >= 60 => [
                'grade' => 'B',
                'remark' => 'Very Good',
                'is_pass' => true,
            ],
            $score >= 50 => [
                'grade' => 'C',
                'remark' => 'Good',
                'is_pass' => true,
            ],
            $score >= 45 => [
                'grade' => 'D',
                'remark' => 'Fair',
                'is_pass' => true,
            ],
            $score >= 40 => [
                'grade' => 'E',
                'remark' => 'Pass',
                'is_pass' => true,
            ],
            default => [
                'grade' => 'F',
                'remark' => 'Fail',
                'is_pass' => false,
            ],
        };
    }
}