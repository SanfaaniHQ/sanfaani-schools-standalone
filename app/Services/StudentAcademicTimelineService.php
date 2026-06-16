<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentPromotionItem;
use Illuminate\Support\Collection;

class StudentAcademicTimelineService
{
    public function build(School $school, Student $student, int $limit = 60): Collection
    {
        $audits = AuditLog::query()
            ->where('auditable_type', Student::class)
            ->where('auditable_id', $student->id)
            ->where('school_id', $school->id)
            ->with('user:id,name')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (AuditLog $audit) => [
                'type' => 'audit',
                'title' => str($audit->action)->replace('_', ' ')->title()->toString(),
                'description' => $this->auditDescription($audit),
                'occurred_at' => $audit->created_at,
                'actor' => $audit->user?->name ?? 'System',
                'details' => $this->detailsFromArray($audit->metadata ?? []),
            ]);

        $enrollments = $student->classEnrollments()
            ->where('school_id', $school->id)
            ->with(['schoolClass', 'academicSession', 'startTerm', 'endTerm', 'createdBy'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (StudentClassEnrollment $enrollment) => [
                'type' => 'enrollment',
                'title' => 'Enrollment '.$this->label($enrollment->status),
                'description' => trim(($enrollment->schoolClass?->name ?? 'Class').' '.($enrollment->schoolClass?->section ?? '')).' / '.($enrollment->academicSession?->name ?? 'No session'),
                'occurred_at' => $enrollment->enrolled_at ?? $enrollment->created_at,
                'actor' => $enrollment->createdBy?->name ?? 'System',
                'details' => array_values(array_filter([
                    $enrollment->startTerm?->name ? 'Start term: '.$enrollment->startTerm->name : null,
                    $enrollment->endTerm?->name ? 'End term: '.$enrollment->endTerm->name : null,
                    data_get($enrollment->metadata, 'source') ? 'Source: '.$this->label((string) data_get($enrollment->metadata, 'source')) : null,
                ])),
            ]);

        $promotions = $student->promotionItems()
            ->where('school_id', $school->id)
            ->with(['fromClass', 'toClass', 'fromSession', 'toSession', 'batch.createdBy'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (StudentPromotionItem $promotion) => [
                'type' => 'lifecycle',
                'title' => 'Student '.$this->label($promotion->action),
                'description' => trim(($promotion->fromClass?->name ?? 'Previous class').' to '.($promotion->toClass?->name ?? $this->label($promotion->action))),
                'occurred_at' => $promotion->created_at,
                'actor' => $promotion->batch?->createdBy?->name ?? 'System',
                'details' => array_values(array_filter([
                    $promotion->fromSession?->name ? 'From: '.$promotion->fromSession->name : null,
                    $promotion->toSession?->name ? 'To: '.$promotion->toSession->name : null,
                    $promotion->status ? 'Status: '.$this->label($promotion->status) : null,
                ])),
            ]);

        $scratchCards = $student->scratchCardUsages()
            ->where('school_id', $school->id)
            ->with(['scratchCard', 'academicSession', 'term'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($usage) => [
                'type' => 'result_access',
                'title' => 'Result Accessed',
                'description' => 'Scratch card '.($usage->scratchCard?->serial_number ?? 'N/A'),
                'occurred_at' => $usage->used_at ?? $usage->created_at,
                'actor' => 'Public checker',
                'details' => array_values(array_filter([
                    $usage->academicSession?->name,
                    $usage->term?->name,
                    $usage->result_type ? $this->label($usage->result_type) : null,
                    $usage->ip_address ? 'IP: '.$usage->ip_address : null,
                ])),
            ]);

        return $audits
            ->toBase()
            ->merge($enrollments->toBase())
            ->merge($promotions->toBase())
            ->merge($scratchCards->toBase())
            ->sortByDesc(fn (array $event) => optional($event['occurred_at'])->timestamp ?? 0)
            ->take($limit)
            ->values();
    }

    private function auditDescription(AuditLog $audit): string
    {
        return match ($audit->action) {
            'student_archived' => 'Archived safely; academic records remain available.',
            'student_restored' => 'Restored from archive without rebuilding historical records.',
            'student_promoted', 'student_repeated', 'student_demoted' => 'Class placement changed through the lifecycle workflow.',
            'student_graduated', 'student_transferred', 'student_withdrawn' => 'Academic status changed while preserving results and enrollment history.',
            default => 'Student record activity',
        };
    }

    private function detailsFromArray(array $metadata): array
    {
        return collect($metadata)
            ->reject(fn ($value) => is_array($value) || is_object($value))
            ->take(6)
            ->map(fn ($value, string $key) => $this->label($key).': '.$this->label((string) $value))
            ->values()
            ->all();
    }

    private function label(?string $value): string
    {
        return str((string) $value)->replace('_', ' ')->title()->toString();
    }
}
