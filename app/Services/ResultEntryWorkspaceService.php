<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ResultEntryWorkspaceService
{
    public const MAX_CA_SCORE = 40;

    public const MAX_EXAM_SCORE = 60;

    public function __construct(
        private ResultGradingService $gradingService
    ) {}

    public function activeGradingScales(School $school): Collection
    {
        return $this->gradingService->activeScales($school);
    }

    public function gradingLookup(Collection $gradingScales): array
    {
        return $gradingScales
            ->map(fn ($scale) => [
                'min_score' => (float) $scale->min_score,
                'max_score' => (float) $scale->max_score,
                'grade' => $scale->grade,
                'remark' => $scale->remark,
                'is_pass' => (bool) $scale->is_pass,
            ])
            ->values()
            ->all();
    }

    public function maxScores(): array
    {
        return [
            'ca' => self::MAX_CA_SCORE,
            'exam' => self::MAX_EXAM_SCORE,
            'total' => self::MAX_CA_SCORE + self::MAX_EXAM_SCORE,
        ];
    }

    public function editableRemarkFields(?string $roleContext): array
    {
        return [
            'teacher_remark' => $roleContext === 'teacher',
            'officer_remark' => $roleContext === 'result_officer',
            'admin_remark' => in_array($roleContext, ['school_admin', 'super_admin'], true),
        ];
    }

    public function displayRows(Collection $students, iterable $scores, Collection $gradingScales): Collection
    {
        $scoresByStudentId = collect($scores)->keyBy(fn ($row) => (int) ($row['student_id'] ?? 0));

        return $students->map(function ($student) use ($scoresByStudentId, $gradingScales) {
            $row = $scoresByStudentId->get((int) $student->id, []);
            $ca = $this->nullableScore($row['ca_score'] ?? null);
            $exam = $this->nullableScore($row['exam_score'] ?? null);
            $calculation = $ca === null || $exam === null
                ? ['total_score' => null, 'grade' => null, 'remark' => null, 'is_pass' => null]
                : $this->calculate($gradingScales, $ca, $exam);

            return [
                'student' => $student,
                'student_id' => $student->id,
                'ca_score' => $ca,
                'exam_score' => $exam,
                'total_score' => $calculation['total_score'],
                'grade' => $calculation['grade'],
                'remark' => $calculation['remark'],
                'teacher_remark' => $row['teacher_remark'] ?? null,
                'officer_remark' => $row['officer_remark'] ?? null,
                'admin_remark' => $row['admin_remark'] ?? null,
            ];
        });
    }

    public function validateRows(
        Request $request,
        Collection $students,
        Collection $gradingScales,
        ?string $roleContext,
        iterable $existingScores = []
    ): array {
        $studentsById = $students->keyBy('id');
        $existingByStudentId = collect($existingScores)->keyBy(fn ($row) => (int) ($row['student_id'] ?? 0));
        $editableRemarks = $this->editableRemarkFields($roleContext);
        $errors = [];
        $rows = [];

        foreach ($request->input('scores', []) as $studentId => $score) {
            $studentId = (int) $studentId;
            $rowErrors = [];

            if (! $studentsById->has($studentId)) {
                continue;
            }

            $student = $studentsById->get($studentId);
            $existing = $existingByStudentId->get($studentId, []);
            $ca = trim((string) ($score['ca_score'] ?? ''));
            $exam = trim((string) ($score['exam_score'] ?? ''));

            if ($ca === '' && $exam === '') {
                continue;
            }

            if ($ca === '' || $exam === '') {
                $errors[] = $student->fullName().': CA and exam scores must both be filled.';
                continue;
            }

            if (! is_numeric($ca) || (float) $ca < 0 || (float) $ca > self::MAX_CA_SCORE) {
                $rowErrors[] = $student->fullName().': CA score must be between 0 and '.self::MAX_CA_SCORE.'.';
            }

            if (! is_numeric($exam) || (float) $exam < 0 || (float) $exam > self::MAX_EXAM_SCORE) {
                $rowErrors[] = $student->fullName().': exam score must be between 0 and '.self::MAX_EXAM_SCORE.'.';
            }

            foreach (['teacher_remark', 'officer_remark', 'admin_remark'] as $remarkField) {
                $value = $this->remarkValue($score, $existing, $remarkField, $editableRemarks[$remarkField] ?? false);

                if ($value !== null && mb_strlen($value) > 500) {
                    $rowErrors[] = $student->fullName().': '.str_replace('_', ' ', $remarkField).' must not exceed 500 characters.';
                }
            }

            if ($rowErrors !== []) {
                array_push($errors, ...$rowErrors);
                continue;
            }

            $caScore = round((float) $ca, 2);
            $examScore = round((float) $exam, 2);
            $calculation = $this->calculate($gradingScales, $caScore, $examScore);

            $rows[] = [
                'student_id' => $studentId,
                'ca_score' => $caScore,
                'exam_score' => $examScore,
                'total_score' => $calculation['total_score'],
                'grade' => $calculation['grade'],
                'remark' => $calculation['remark'],
                'is_pass' => $calculation['is_pass'],
                'teacher_remark' => $this->remarkValue($score, $existing, 'teacher_remark', $editableRemarks['teacher_remark']),
                'officer_remark' => $this->remarkValue($score, $existing, 'officer_remark', $editableRemarks['officer_remark']),
                'admin_remark' => $this->remarkValue($score, $existing, 'admin_remark', $editableRemarks['admin_remark']),
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['scores' => implode(' ', $errors)]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages(['scores' => 'Enter at least one complete student score.']);
        }

        return $rows;
    }

    private function calculate(Collection $gradingScales, float $caScore, float $examScore): array
    {
        $total = round($caScore + $examScore, 2);
        $grading = $this->gradingService->calculateFromScales($gradingScales, $total);

        return [
            'total_score' => $total,
            'grade' => $grading['grade'],
            'remark' => $grading['remark'],
            'is_pass' => $grading['is_pass'],
        ];
    }

    private function nullableScore(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return round((float) $value, 2);
    }

    private function remarkValue(array $input, array $existing, string $field, bool $canEdit): ?string
    {
        if (! $canEdit) {
            return $this->cleanRemark($existing[$field] ?? null);
        }

        return $this->cleanRemark($input[$field] ?? null);
    }

    private function cleanRemark(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
