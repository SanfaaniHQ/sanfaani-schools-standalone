<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\ScratchCard;
use App\Models\ScratchCardUsage;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScratchCardAccessService
{
    public function validateAndRecord(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType,
        string $serialNumber,
        string $pinCode,
        Request $request
    ): array {
        return DB::transaction(function () use (
            $school,
            $student,
            $academicSession,
            $term,
            $resultType,
            $serialNumber,
            $pinCode,
            $request
        ) {
            $card = ScratchCard::where('serial_number', trim($serialNumber))
                ->lockForUpdate()
                ->first();

            if (! $card || ! $card->pin_hash || ! hash_equals($card->pin_hash, hash('sha256', trim($pinCode)))) {
                return $this->failure(__('public_result.invalid_scratch_card'));
            }

            if ((int) $card->school_id !== (int) $school->id) {
                return $this->failure(__('public_result.invalid_scratch_card'));
            }

            if ($card->status === 'revoked' || $card->revoked_at) {
                return $this->failure(__('public_result.card_revoked'));
            }

            if ($card->status === 'expired') {
                return $this->failure(__('public_result.card_expired'));
            }

            if ($card->expires_at && $card->expires_at->isPast()) {
                return $this->failure(__('public_result.card_expired'));
            }

            if ((int) $card->used_count >= (int) $card->max_uses) {
                return $this->failure(__('public_result.card_usage_limit_reached'));
            }

            if ($card->academic_session_id && (int) $card->academic_session_id !== (int) $academicSession->id) {
                return $this->failure(__('public_result.card_not_valid_for_result'));
            }

            if ($card->term_id && (int) $card->term_id !== (int) $term->id) {
                return $this->failure(__('public_result.card_not_valid_for_result'));
            }

            if ($card->result_type && $card->result_type !== $resultType) {
                return $this->failure(__('public_result.card_not_valid_for_result'));
            }

            if ($card->school_class_id && (int) $card->school_class_id !== (int) $student->school_class_id) {
                return $this->failure(__('public_result.card_not_valid_for_result'));
            }

            if ($card->used_by_student_id && (int) $card->used_by_student_id !== (int) $student->id) {
                return $this->failure(__('public_result.card_used_by_another_student'));
            }

            $now = now();
            $newUsedCount = (int) $card->used_count + 1;

            $card->used_count = $newUsedCount;
            $card->used_by_student_id = $card->used_by_student_id ?: $student->id;
            $card->first_used_at = $card->first_used_at ?: $now;
            $card->last_used_at = $now;

            if ($newUsedCount >= (int) $card->max_uses) {
                $card->status = 'used';
            }

            $card->save();

            ScratchCardUsage::create([
                'scratch_card_id' => $card->id,
                'school_id' => $school->id,
                'student_id' => $student->id,
                'academic_session_id' => $academicSession->id,
                'term_id' => $term->id,
                'result_type' => $resultType,
                'used_at' => $now,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'metadata' => [
                    'access_method' => 'scratch_card',
                    'public_checker' => true,
                ],
            ]);

            return [
                'success' => true,
                'message' => null,
                'scratchCard' => $card->fresh(),
            ];
        });
    }

    private function failure(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
            'scratchCard' => null,
        ];
    }
}
