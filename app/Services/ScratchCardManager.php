<?php

namespace App\Services;

use App\Models\ScratchCard;
use App\Models\ScratchCardBatch;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ScratchCardManager
{
    public function generateForBatch(ScratchCardBatch $batch, int $maxUses, ?int $actorId = null): ScratchCardBatch
    {
        try {
            return DB::transaction(function () use ($batch, $maxUses, $actorId) {
                $lockedBatch = ScratchCardBatch::query()
                    ->whereKey($batch->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->validateGenerationReadiness($lockedBatch);

                if (! $lockedBatch->batch_code) {
                    $lockedBatch->batch_code = $this->uniqueBatchCode($lockedBatch);
                    $lockedBatch->save();
                }

                for ($i = 1; $i <= $lockedBatch->quantity; $i++) {
                    $this->createCard($lockedBatch, $maxUses, $actorId);
                }

                $lockedBatch->update([
                    'status' => 'generated',
                    'generated_by' => $actorId,
                    'failed_generation_at' => null,
                    'failed_generation_reason' => null,
                    'metadata' => array_merge($lockedBatch->metadata ?? [], [
                        'generated_at' => now()->toDateTimeString(),
                        'generated_quantity' => $lockedBatch->quantity,
                        'max_uses' => $maxUses,
                    ]),
                ]);

                return $lockedBatch->fresh([
                    'school',
                    'schoolClass',
                    'academicSession',
                    'term',
                ]) ?? $lockedBatch;
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $this->markGenerationFailure($batch, $exception, $actorId);

            throw $exception;
        }
    }

    public function revokeBatch(ScratchCardBatch $batch, string $reason, ?int $actorId = null): ScratchCardBatch
    {
        return DB::transaction(function () use ($batch, $reason, $actorId) {
            $lockedBatch = ScratchCardBatch::query()
                ->whereKey($batch->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedBatch->update([
                'status' => 'revoked',
                'metadata' => array_merge($lockedBatch->metadata ?? [], [
                    'revoked_at' => now()->toDateTimeString(),
                    'revoked_by' => $actorId,
                    'revoke_reason' => $reason,
                ]),
            ]);

            $lockedBatch->cards()->update([
                'status' => 'revoked',
                'revoked_at' => now(),
                'revoked_by' => $actorId,
                'revoke_reason' => $reason,
            ]);

            return $lockedBatch->fresh() ?? $lockedBatch;
        });
    }

    public function revokeCard(ScratchCard $card, string $reason, ?int $actorId = null): ScratchCard
    {
        $card->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revoked_by' => $actorId,
            'revoke_reason' => $reason,
        ]);

        return $card->fresh() ?? $card;
    }

    private function validateGenerationReadiness(ScratchCardBatch $batch): void
    {
        if ($batch->status === 'revoked') {
            throw ValidationException::withMessages([
                'max_uses' => 'Cards cannot be generated for a revoked request.',
            ]);
        }

        if ($batch->payment_status !== 'paid' || ! $batch->payment_confirmed_at) {
            throw ValidationException::withMessages([
                'max_uses' => 'Confirm payment before generating scratch cards.',
            ]);
        }

        if ($batch->cards()->exists()) {
            throw ValidationException::withMessages([
                'max_uses' => 'This request already has generated cards.',
            ]);
        }
    }

    private function createCard(ScratchCardBatch $batch, int $maxUses, ?int $actorId): ScratchCard
    {
        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $pin = $this->generatePin();

            try {
                return ScratchCard::create([
                    'scratch_card_batch_id' => $batch->id,
                    'school_id' => $batch->school_id,
                    'school_class_id' => $batch->school_class_id,
                    'academic_session_id' => $batch->academic_session_id,
                    'term_id' => $batch->term_id,
                    'result_type' => $batch->result_type,
                    'serial_number' => $this->generateSerialNumber($batch),
                    'pin_code' => $pin,
                    'pin_hash' => hash('sha256', $pin),
                    'max_uses' => $maxUses,
                    'used_count' => 0,
                    'status' => 'unused',
                    'expires_at' => $batch->expires_at,
                    'generated_by' => $actorId,
                    'metadata' => [
                        'batch_title' => $batch->title,
                        'batch_code' => $batch->batch_code,
                    ],
                ]);
            } catch (QueryException $exception) {
                if (! $this->isDuplicateKey($exception) || $attempt === 10) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException('Scratch card generation failed after retrying unique serial creation.');
    }

    private function uniqueBatchCode(ScratchCardBatch $batch): string
    {
        $prefix = Str::of($batch->school?->school_code ?: 'SCH'.$batch->school_id)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->limit(12, '')
            ->toString();

        do {
            $code = 'SCB-'.$prefix.'-'.now()->format('Ymd').'-'.str_pad((string) $batch->id, 5, '0', STR_PAD_LEFT).'-'.Str::upper(Str::random(4));
        } while (ScratchCardBatch::where('batch_code', $code)->whereKeyNot($batch->id)->exists());

        return $code;
    }

    private function generatePin(): string
    {
        return str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    private function generateSerialNumber(ScratchCardBatch $batch): string
    {
        $code = Str::of($batch->batch_code ?: 'SCB-'.$batch->id)
            ->replace('-', '')
            ->limit(18, '')
            ->upper()
            ->toString();

        return 'SC-'.$code.'-'.Str::upper(Str::random(10));
    }

    private function isDuplicateKey(QueryException $exception): bool
    {
        return str_contains((string) $exception->getCode(), '23000')
            || str_contains($exception->getMessage(), 'Duplicate entry')
            || str_contains($exception->getMessage(), 'UNIQUE constraint failed');
    }

    private function markGenerationFailure(ScratchCardBatch $batch, Throwable $exception, ?int $actorId): void
    {
        try {
            $fresh = ScratchCardBatch::find($batch->id);

            if (! $fresh) {
                return;
            }

            $fresh->update([
                'failed_generation_at' => now(),
                'failed_generation_reason' => Str::limit($exception->getMessage(), 2000, ''),
                'metadata' => array_merge($fresh->metadata ?? [], [
                    'last_generation_failure_actor_id' => $actorId,
                    'last_generation_failure_at' => now()->toDateTimeString(),
                ]),
            ]);
        } catch (Throwable) {
            logger()->error('Unable to record scratch-card generation failure.', [
                'batch_id' => $batch->id,
                'original_error' => $exception->getMessage(),
            ]);
        }
    }
}
