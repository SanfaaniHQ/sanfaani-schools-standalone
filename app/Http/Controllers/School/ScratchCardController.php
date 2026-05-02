<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ScratchCardBatch;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScratchCardController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

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
            ->latest()
            ->paginate(10);

        return view('school.scratch-cards.index', [
            'school' => $school,
            'batches' => $batches,
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

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'school_class_id' => [
                'nullable',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'academic_session_id' => [
                'required',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')->where('school_id', $school->id),
            ],
            'result_type' => [
                'required',
                Rule::in(['term_result']),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:2000'],
            'payment_method' => [
                'nullable',
                Rule::in(['bank_transfer', 'cash', 'manual']),
            ],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'request_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! Term::where('id', $data['term_id'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $data['academic_session_id'])
            ->exists()) {
            throw ValidationException::withMessages([
                'term_id' => 'The selected term does not belong to the selected academic session.',
            ]);
        }

        ScratchCardBatch::create([
            'school_id' => $school->id,
            'school_class_id' => $data['school_class_id'] ?? null,
            'academic_session_id' => $data['academic_session_id'],
            'term_id' => $data['term_id'],
            'result_type' => 'term_result',
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
                'requested_by' => auth()->id(),
                'requested_at' => now()->toDateTimeString(),
            ],
        ]);

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

        $fileName = 'scratch-cards-batch-' . $batch->id . '-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($batch) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
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
                ->with(['schoolClass', 'academicSession', 'term'])
                ->orderBy('id')
                ->chunk(200, function ($cards) use ($handle) {
                    foreach ($cards as $card) {
                        fputcsv($handle, [
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

    private function currentSchoolOrFail(): School
    {
        $school = auth()->user()->school;

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
