<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\StoreScratchCardRequest;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\ScratchCardBatch;
use App\Models\Term;
use App\Notifications\ScratchCardRequestStatusNotification;
use App\Services\AuditLogService;
use App\Services\AuditService;
use App\Services\CurrentSchoolService;
use App\Services\NotificationPreferenceService;
use App\Services\ScratchAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScratchCardController extends Controller
{
    public function index(Request $request, ScratchAnalyticsService $analytics)
    {
        $school = $this->currentSchoolOrFail();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));

        $batches = $school->scratchCardBatches()
            ->with([
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
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('batch_code', 'like', "%{$search}%")
                        ->orWhere('payment_reference', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('school.scratch-cards.index', [
            'school' => $school,
            'batches' => $batches,
            'filters' => $filters,
            'scratchSummary' => $analytics->summary($school->id),
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.scratch-cards.create', [
            'school' => $school,
            'classes' => $this->classesForSchool($school),
            'academicSessions' => $this->academicSessionsForSchool($school),
            'terms' => $this->termsForSchool($school),
        ]);
    }

    public function store(StoreScratchCardRequest $request)
    {
        $school = $this->currentSchoolOrFail();
        $data = $request->validated();

        $batch = DB::transaction(function () use ($data, $school, $request) {
            $batch = ScratchCardBatch::create([
                'school_id' => $school->id,
                'requested_by' => $request->user()?->id,
                'school_class_id' => $data['school_class_id'] ?? null,
                'academic_session_id' => $data['academic_session_id'],
                'term_id' => $data['term_id'],
                'result_type' => $data['result_type'],
                'school_result_access_policy_id' => null,
                'title' => $data['title'] ?? null,
                'quantity' => $data['quantity'],
                'amount' => 0,
                'currency' => 'NGN',
                'payment_status' => filled($data['payment_method'] ?? null) ? 'manual_pending' : 'pending',
                'payment_method' => $data['payment_method'] ?? null,
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_confirmed_at' => null,
                'payment_confirmed_by' => null,
                'status' => 'pending_payment',
                'expires_at' => null,
                'generated_by' => null,
                'metadata' => [
                    'request_note' => $data['request_note'] ?? null,
                    'requested_by' => $request->user()?->id,
                    'requested_at' => now()->toDateTimeString(),
                ],
            ]);

            app(AuditLogService::class)->log('scratch_card_request_created', $batch, $school, metadata: [
                'quantity' => $batch->quantity,
                'payment_method' => $batch->payment_method,
            ], request: $request);

            AuditService::log('billing', 'scratch_card_request_submitted', [
                'school_id' => $school->id,
                'request_id' => $batch->id,
                'quantity' => $batch->quantity,
                'payment_method' => $batch->payment_method,
            ]);

            return $batch;
        });

        if (app(NotificationPreferenceService::class)->emailEnabled('scratch_card_request_submitted', $school, auth()->user(), 'school_admin')) {
            try {
                // Shared hosting worker: php artisan queue:work --queue=mail,exports --sleep=3 --tries=3 --timeout=60
                $request->user()->notify(new ScratchCardRequestStatusNotification(
                    'submitted',
                    'Your request is pending approval.'
                ));
            } catch (\Throwable $exception) {
                Log::warning('Scratch card submitted notification failed.', [
                    'batch_id' => $batch->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('school.scratch-cards.index')
            ->with('success', 'Scratch card request submitted successfully.');
    }

    public function show(ScratchCardBatch $batch)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeBatch($batch, $school);

        $cards = $batch->cards()
            ->with(['usedByStudent', 'revokedBy'])
            ->latest()
            ->paginate(25);

        return view('school.scratch-cards.show', [
            'school' => $school,
            'batch' => $batch->load([
                'schoolClass',
                'academicSession',
                'term',
                'generatedBy',
                'paymentConfirmedBy',
            ]),
            'cards' => $cards,
        ]);
    }

    public function download(ScratchCardBatch $batch)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeBatch($batch, $school);

        if ($batch->status !== 'generated' || ! $batch->cards()->exists()) {
            return back()->with('error', 'Cards are not available for download yet.');
        }

        $fileName = 'scratch-cards-batch-'.$batch->id.'-'.now()->format('YmdHis').'.csv';

        $batch->update([
            'last_exported_at' => now(),
            'last_exported_by' => auth()->id(),
        ]);

        return response()->streamDownload(function () use ($batch) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'batch_code',
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
                ->with(['batch', 'schoolClass', 'academicSession', 'term'])
                ->orderBy('id')
                ->chunk(200, function ($cards) use ($handle) {
                    foreach ($cards as $card) {
                        fputcsv($handle, [
                            $card->batch->batch_code ?? '',
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

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeBatch(ScratchCardBatch $batch, School $school): void
    {
        if ($batch->school_id !== $school->id) {
            abort(403, 'You cannot access this scratch card batch.');
        }
    }

    private function classesForSchool(School $school)
    {
        return SchoolClass::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('section')
            ->get();
    }

    private function academicSessionsForSchool(School $school)
    {
        return AcademicSession::where('school_id', $school->id)
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    private function termsForSchool(School $school)
    {
        return Term::where('school_id', $school->id)
            ->with('academicSession')
            ->where('status', 'active')
            ->latest()
            ->get();
    }
}
