<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\ScratchCard;
use App\Models\ScratchCardBatch;
use App\Notifications\ScratchCardRequestStatusNotification;
use App\Services\AuditLogService;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScratchCardRequestController extends Controller
{
    public function index()
    {
        $batches = ScratchCardBatch::query()
            ->with([
                'school',
                'schoolClass',
                'academicSession',
                'term',
                'generatedBy',
                'paymentConfirmedBy',
            ])
            ->withCount([
                'cards',
                'cards as unused_cards_count' => fn ($query) => $query->where('status', 'unused'),
                'cards as used_cards_count' => fn ($query) => $query->where('status', 'used'),
                'cards as revoked_cards_count' => fn ($query) => $query->where('status', 'revoked'),
            ])
            ->latest()
            ->paginate(15);

        return view('admin.scratch-card-requests.index', [
            'batches' => $batches,
        ]);
    }

    public function show(ScratchCardBatch $batch)
    {
        $batch->load([
            'school',
            'schoolClass',
            'academicSession',
            'term',
            'generatedBy',
            'paymentConfirmedBy',
        ])->loadCount([
            'cards',
            'cards as unused_cards_count' => fn ($query) => $query->where('status', 'unused'),
            'cards as used_cards_count' => fn ($query) => $query->where('status', 'used'),
            'cards as revoked_cards_count' => fn ($query) => $query->where('status', 'revoked'),
        ]);

        $cards = $batch->cards()
            ->with(['usedByStudent', 'revokedBy'])
            ->latest()
            ->paginate(25);

        return view('admin.scratch-card-requests.show', [
            'batch' => $batch,
            'cards' => $cards,
        ]);
    }

    public function confirmPayment(Request $request, ScratchCardBatch $batch)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'payment_method' => ['required', Rule::in(['bank_transfer', 'cash', 'manual'])],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        if ($batch->status === 'revoked') {
            throw ValidationException::withMessages([
                'amount' => 'Payment cannot be confirmed for a revoked request.',
            ]);
        }

        $batch->update([
            'amount' => $data['amount'],
            'currency' => strtoupper($data['currency']),
            'payment_method' => $data['payment_method'],
            'payment_reference' => $data['payment_reference'] ?? null,
            'payment_status' => 'paid',
            'payment_confirmed_at' => now(),
            'payment_confirmed_by' => auth()->id(),
        ]);

        PaymentTransaction::updateOrCreate(
            [
                'payable_type' => ScratchCardBatch::class,
                'payable_id' => $batch->id,
            ],
            [
                'school_id' => $batch->school_id,
                'amount' => $data['amount'],
                'currency' => strtoupper($data['currency']),
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'status' => 'paid',
                'paid_at' => now(),
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id(),
                'metadata' => [
                    'source' => 'scratch_card_batch_confirmation',
                ],
            ]
        );

        app(AuditLogService::class)->log('scratch_card_payment_confirmed', $batch, $batch->school, request: $request);

        $this->notifySchoolAdmins(
            $batch->refresh(),
            'payment_confirmed',
            'Payment has been confirmed. Card generation can now proceed.'
        );

        return back()->with('success', 'Payment confirmed successfully.');
    }

    public function generate(Request $request, ScratchCardBatch $batch)
    {
        $data = $request->validate([
            'max_uses' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        DB::transaction(function () use ($batch, $data) {
            $lockedBatch = ScratchCardBatch::query()
                ->whereKey($batch->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBatch->status === 'revoked') {
                throw ValidationException::withMessages([
                    'max_uses' => 'Cards cannot be generated for a revoked request.',
                ]);
            }

            if ($lockedBatch->payment_status !== 'paid' || ! $lockedBatch->payment_confirmed_at) {
                throw ValidationException::withMessages([
                    'max_uses' => 'Confirm payment before generating scratch cards.',
                ]);
            }

            if ($lockedBatch->cards()->exists()) {
                throw ValidationException::withMessages([
                    'max_uses' => 'This request already has generated cards.',
                ]);
            }

            for ($i = 1; $i <= $lockedBatch->quantity; $i++) {
                $pin = $this->generatePin();

                ScratchCard::create([
                    'scratch_card_batch_id' => $lockedBatch->id,
                    'school_id' => $lockedBatch->school_id,
                    'school_class_id' => $lockedBatch->school_class_id,
                    'academic_session_id' => $lockedBatch->academic_session_id,
                    'term_id' => $lockedBatch->term_id,
                    'result_type' => $lockedBatch->result_type,
                    'serial_number' => $this->generateSerialNumber($lockedBatch),
                    'pin_code' => $pin,
                    'pin_hash' => hash('sha256', $pin),
                    'max_uses' => $data['max_uses'],
                    'used_count' => 0,
                    'status' => 'unused',
                    'expires_at' => $lockedBatch->expires_at,
                    'generated_by' => auth()->id(),
                    'metadata' => [
                        'batch_title' => $lockedBatch->title,
                    ],
                ]);
            }

            $lockedBatch->update([
                'status' => 'generated',
                'generated_by' => auth()->id(),
            ]);

            app(AuditLogService::class)->log('scratch_card_batch_generated', $lockedBatch, $lockedBatch->school, metadata: [
                'quantity' => $lockedBatch->quantity,
                'max_uses' => $data['max_uses'],
            ], request: request());
        });

        $this->notifySchoolAdmins(
            $batch->refresh(),
            'cards_generated',
            'Scratch cards have been generated and are ready for download.'
        );

        return back()->with('success', 'Scratch cards generated successfully.');
    }

    public function download(ScratchCardBatch $batch)
    {
        if ($batch->status !== 'generated' || ! $batch->cards()->exists()) {
            return back()->with('error', 'Cards are not available for download yet.');
        }

        $fileName = 'scratch-cards-batch-' . $batch->id . '-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($batch) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'school',
                'serial_number',
                'pin_code',
                'status',
                'max_uses',
                'used_count',
                'class',
                'academic_session',
                'term',
                'result_type',
                'expires_at',
            ]);

            $batch->cards()
                ->with(['school', 'schoolClass', 'academicSession', 'term'])
                ->orderBy('id')
                ->chunk(200, function ($cards) use ($handle) {
                    foreach ($cards as $card) {
                        fputcsv($handle, [
                            $card->school->name ?? '',
                            $card->serial_number,
                            $card->pin_code,
                            $card->status,
                            $card->max_uses,
                            $card->used_count,
                            trim(($card->schoolClass->name ?? '') . ' ' . ($card->schoolClass->section ?? '')),
                            $card->academicSession->name ?? '',
                            $card->term->name ?? '',
                            $card->result_type,
                            $card->expires_at?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function revokeBatch(Request $request, ScratchCardBatch $batch)
    {
        $data = $request->validate([
            'revoke_reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($batch, $data) {
            $lockedBatch = ScratchCardBatch::query()
                ->whereKey($batch->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedBatch->update([
                'status' => 'revoked',
                'metadata' => array_merge($lockedBatch->metadata ?? [], [
                    'revoked_at' => now()->toDateTimeString(),
                    'revoked_by' => auth()->id(),
                    'revoke_reason' => $data['revoke_reason'],
                ]),
            ]);

            $lockedBatch->cards()->update([
                'status' => 'revoked',
                'revoked_at' => now(),
                'revoked_by' => auth()->id(),
                'revoke_reason' => $data['revoke_reason'],
            ]);

            app(AuditLogService::class)->log('scratch_card_batch_revoked', $lockedBatch, $lockedBatch->school, metadata: [
                'reason' => $data['revoke_reason'],
            ], request: request());
        });

        $this->notifySchoolAdmins(
            $batch->refresh(),
            'batch_revoked',
            'Scratch card batch was revoked. Reason: '.$data['revoke_reason']
        );

        return back()->with('success', 'Scratch card batch revoked successfully.');
    }

    public function revokeCard(Request $request, ScratchCard $card)
    {
        $data = $request->validate([
            'revoke_reason' => ['required', 'string', 'max:1000'],
        ]);

        $card->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revoked_by' => auth()->id(),
            'revoke_reason' => $data['revoke_reason'],
        ]);

        app(AuditLogService::class)->log('scratch_card_revoked', $card, $card->school, metadata: [
            'reason' => $data['revoke_reason'],
        ], request: $request);

        return back()->with('success', 'Scratch card revoked successfully.');
    }

    private function generatePin(): string
    {
        return str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    private function generateSerialNumber(ScratchCardBatch $batch): string
    {
        do {
            $serial = 'SC-' . $batch->school_id . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
        } while (ScratchCard::where('serial_number', $serial)->exists());

        return $serial;
    }

    private function notifySchoolAdmins(ScratchCardBatch $batch, string $status, ?string $note = null): void
    {
        $school = $batch->school;

        if (! $school || ! app(NotificationPreferenceService::class)->emailEnabled('scratch_card_'.$status, $school)) {
            return;
        }

        $school->users()
            ->whereNotNull('email')
            ->whereHas('roles', fn ($query) => $query->where('name', 'school_admin'))
            ->get()
            ->each(function ($user) use ($batch, $status, $note) {
                try {
                    $user->notify(new ScratchCardRequestStatusNotification($batch, $status, $note));
                } catch (\Throwable $exception) {
                    Log::warning('Scratch card status notification failed.', [
                        'batch_id' => $batch->id,
                        'user_id' => $user->id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            });
    }
}
