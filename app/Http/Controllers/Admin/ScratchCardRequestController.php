<?php

namespace App\Http\Controllers\Admin;

use App\Events\StudentTransactionalEmailRequested;
use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\School;
use App\Models\ScratchCard;
use App\Models\ScratchCardBatch;
use App\Models\Student;
use App\Notifications\ScratchCardRequestStatusNotification;
use App\Services\AuditLogService;
use App\Services\NotificationPreferenceService;
use App\Services\ScratchAnalyticsService;
use App\Services\ScratchCardManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScratchCardRequestController extends Controller
{
    public function index(Request $request, ScratchAnalyticsService $analytics)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'school_id' => ['nullable', 'integer', Rule::exists('schools', 'id')],
            'status' => ['nullable', Rule::in(['pending_payment', 'pending_approval', 'generated', 'revoked'])],
            'payment_status' => ['nullable', Rule::in(['pending', 'manual_pending', 'paid', 'failed', 'refunded'])],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));

        $batches = ScratchCardBatch::query()
            ->with([
                'school',
                'schoolClass',
                'academicSession',
                'term',
                'requestedBy',
                'generatedBy',
                'paymentConfirmedBy',
            ])
            ->withCount([
                'cards',
                'cards as unused_cards_count' => fn ($query) => $query->where('status', 'unused'),
                'cards as used_cards_count' => fn ($query) => $query->where('status', 'used'),
                'cards as revoked_cards_count' => fn ($query) => $query->where('status', 'revoked'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('batch_code', 'like', "%{$search}%")
                        ->orWhere('payment_reference', 'like', "%{$search}%")
                        ->orWhereHas('school', fn ($schoolQuery) => $schoolQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['school_id'] ?? null, fn ($query, $schoolId) => $query->where('school_id', $schoolId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.scratch-card-requests.index', [
            'batches' => $batches,
            'filters' => $filters,
            'schools' => School::orderBy('name')->get(['id', 'name']),
            'scratchSummary' => $analytics->summary(),
        ]);
    }

    public function show(ScratchCardBatch $batch)
    {
        $batch->load([
            'school',
            'schoolClass',
            'academicSession',
            'term',
            'requestedBy',
            'generatedBy',
            'paymentConfirmedBy',
            'approvedBy',
            'rejectedBy',
            'lastExportedBy',
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

    public function generate(Request $request, ScratchCardBatch $batch, ScratchCardManager $manager)
    {
        $data = $request->validate([
            'max_uses' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $generatedBatch = $manager->generateForBatch($batch, (int) $data['max_uses'], auth()->id());

        app(AuditLogService::class)->log('scratch_card_batch_generated', $generatedBatch, $generatedBatch->school, metadata: [
            'quantity' => $generatedBatch->quantity,
            'max_uses' => $data['max_uses'],
        ], request: $request);

        $this->notifySchoolAdmins(
            $batch->refresh(),
            'cards_generated',
            'Scratch cards have been generated and are ready for download.'
        );

        $this->notifyGuardiansAboutGeneratedCards($batch->refresh());

        return back()->with('success', 'Scratch cards generated successfully.');
    }

    public function download(ScratchCardBatch $batch)
    {
        if ($batch->status !== 'generated' || ! $batch->cards()->exists()) {
            return back()->with('error', 'Cards are not available for download yet.');
        }

        $batch->update([
            'last_exported_at' => now(),
            'last_exported_by' => auth()->id(),
        ]);

        $fileName = 'scratch-cards-batch-'.$batch->id.'-'.now()->format('YmdHis').'.csv';

        return response()->streamDownload(function () use ($batch) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'batch_code',
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
                ->with(['batch', 'school', 'schoolClass', 'academicSession', 'term'])
                ->orderBy('id')
                ->chunk(200, function ($cards) use ($handle) {
                    foreach ($cards as $card) {
                        fputcsv($handle, [
                            $card->batch->batch_code ?? '',
                            $card->school->name ?? '',
                            $card->serial_number,
                            $card->pin_code,
                            $card->status,
                            $card->max_uses,
                            $card->used_count,
                            trim(($card->schoolClass->name ?? '').' '.($card->schoolClass->section ?? '')),
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

    public function revokeBatch(Request $request, ScratchCardBatch $batch, ScratchCardManager $manager)
    {
        $data = $request->validate([
            'revoke_reason' => ['required', 'string', 'max:1000'],
        ]);

        $revokedBatch = $manager->revokeBatch($batch, $data['revoke_reason'], auth()->id());

        app(AuditLogService::class)->log('scratch_card_batch_revoked', $revokedBatch, $revokedBatch->school, metadata: [
            'reason' => $data['revoke_reason'],
        ], request: $request);

        $this->notifySchoolAdmins(
            $batch->refresh(),
            'batch_revoked',
            'Scratch card batch was revoked. Reason: '.$data['revoke_reason']
        );

        return back()->with('success', 'Scratch card batch revoked successfully.');
    }

    public function revokeCard(Request $request, ScratchCard $card, ScratchCardManager $manager)
    {
        $data = $request->validate([
            'revoke_reason' => ['required', 'string', 'max:1000'],
        ]);

        $card = $manager->revokeCard($card, $data['revoke_reason'], auth()->id());

        app(AuditLogService::class)->log('scratch_card_revoked', $card, $card->school, metadata: [
            'reason' => $data['revoke_reason'],
        ], request: $request);

        return back()->with('success', 'Scratch card revoked successfully.');
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
                    $message = trim(($note ?: 'Scratch card request status updated.').' Request #'.$batch->id.'.');

                    $user->notify(new ScratchCardRequestStatusNotification($status, $message));
                } catch (\Throwable $exception) {
                    Log::warning('Scratch card status notification failed.', [
                        'batch_id' => $batch->id,
                        'user_id' => $user->id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            });
    }

    private function notifyGuardiansAboutGeneratedCards(ScratchCardBatch $batch): void
    {
        $batch->loadMissing('school');

        if (! $batch->school || ! $batch->school_class_id) {
            return;
        }

        Student::where('school_id', $batch->school_id)
            ->where('school_class_id', $batch->school_class_id)
            ->whereNotNull('guardian_email')
            ->with('school')
            ->chunkById(100, function ($students) use ($batch) {
                foreach ($students as $student) {
                    StudentTransactionalEmailRequested::dispatch(
                        StudentTransactionalEmailRequested::scratchCardGenerated($student, $batch)
                    );
                }
            });
    }
}
