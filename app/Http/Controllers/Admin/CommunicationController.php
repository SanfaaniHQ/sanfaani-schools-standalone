<?php

namespace App\Http\Controllers\Admin;

use App\Events\SchoolNotificationRequested;
use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use App\Models\LeadRequest;
use App\Models\School;
use App\Services\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;

class CommunicationController extends Controller
{
    public function index(Request $request)
    {
        $logs = new LengthAwarePaginator([], 0, 20);
        if (Schema::hasTable('communication_logs')) {
            $logs = CommunicationLog::whereNull('school_id')
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        return view('admin.communications.index', [
            'schools' => School::orderBy('name')->get(['id', 'name', 'email', 'subscription_status', 'status']),
            'logs' => $logs,
            'leads' => LeadRequest::latest()->limit(50)->get(['id', 'email', 'name', 'status']),
            'status' => $request->input('status'),
            'type' => $request->input('type'),
        ]);
    }

    public function send(Request $request, CommunicationService $communications)
    {
        $data = $request->validate([
            'target' => ['required', Rule::in(['school', 'trial_schools', 'expired_schools', 'lead'])],
            'school_id' => ['nullable', Rule::exists('schools', 'id')],
            'lead_id' => ['nullable', Rule::exists('lead_requests', 'id')],
            'target_roles' => ['nullable', 'array'],
            'target_roles.*' => ['string', Rule::in(['school_admin', 'result_officer', 'teacher'])],
            'include_school_contact' => ['nullable', 'boolean'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $targetRoles = $this->targetRoles($data['target_roles'] ?? []);
        $includeSchoolContact = $request->boolean('include_school_contact');

        if ($data['target'] === 'school' && filled($data['school_id'] ?? null)) {
            $school = School::findOrFail($data['school_id']);
            event(SchoolNotificationRequested::systemAnnouncement($school, $data['subject'], $data['message'], $targetRoles, $includeSchoolContact, 'single_school'));
        }

        if ($data['target'] === 'trial_schools') {
            School::where('subscription_status', 'trial')->chunkById(50, function ($schools) use ($data, $targetRoles, $includeSchoolContact) {
                foreach ($schools as $school) {
                    event(SchoolNotificationRequested::systemAnnouncement($school, $data['subject'], $data['message'], $targetRoles, $includeSchoolContact, 'trial_schools'));
                }
            });
        }

        if ($data['target'] === 'expired_schools') {
            School::where('subscription_status', 'expired')->chunkById(50, function ($schools) use ($data, $targetRoles, $includeSchoolContact) {
                foreach ($schools as $school) {
                    event(SchoolNotificationRequested::systemAnnouncement($school, $data['subject'], $data['message'], $targetRoles, $includeSchoolContact, 'expired_schools'));
                }
            });
        }

        if ($data['target'] === 'lead' && filled($data['lead_id'] ?? null)) {
            $lead = LeadRequest::findOrFail($data['lead_id']);
            if (filled($lead->email)) {
                $communications->sendPlatformEmail($lead->email, $data['subject'], 'Lead follow-up', $data['message'], 'lead_followup', ['lead_id' => $lead->id], 'platform_transactional');
            }
        }

        return back()->with('success', 'Communication dispatch completed.');
    }

    private function targetRoles(array $roles): array
    {
        $roles = collect($roles)
            ->filter(fn ($role) => in_array($role, ['school_admin', 'result_officer', 'teacher'], true))
            ->unique()
            ->values()
            ->all();

        return $roles === [] ? ['school_admin'] : $roles;
    }

    public function resend(CommunicationLog $communicationLog, CommunicationService $communications)
    {
        if (! Schema::hasTable('communication_logs')) {
            return back()->with('error', 'Communication logs table is not ready yet. Run migrations.');
        }

        if ($communicationLog->school_id !== null) {
            abort(403);
        }

        $communications->sendPlatformEmail(
            $communicationLog->recipient,
            $communicationLog->subject,
            'Resent platform communication',
            (string) data_get($communicationLog->metadata, 'original_message', 'Resent platform communication.'),
            $communicationLog->type,
            array_merge($communicationLog->metadata ?? [], ['resend_of' => $communicationLog->id])
        );

        return back()->with('success', 'Platform resend submitted.');
    }

    public function retryFailed(CommunicationService $communications)
    {
        if (! Schema::hasTable('communication_logs')) {
            return back()->with('error', 'Communication logs table is not ready yet. Run migrations.');
        }

        CommunicationLog::whereNull('school_id')
            ->where('status', 'failed')
            ->latest('id')
            ->limit(300)
            ->get()
            ->each(function ($log) use ($communications) {
                $communications->sendPlatformEmail(
                    $log->recipient,
                    $log->subject,
                    'Retry failed platform communication',
                    (string) data_get($log->metadata, 'original_message', 'Retry from failed queue.'),
                    $log->type,
                    array_merge($log->metadata ?? [], ['retry_of' => $log->id])
                );
            });

        return back()->with('success', 'Failed platform emails retry started.');
    }

    public function export(Request $request): StreamedResponse
    {
        if (! Schema::hasTable('communication_logs')) {
            abort(404, 'Communication logs table is not ready yet.');
        }

        $fileName = 'platform-communication-logs-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Recipient', 'Subject', 'Type', 'Status', 'Failure Reason', 'Sent At', 'Created At']);

            CommunicationLog::whereNull('school_id')
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
                ->orderByDesc('id')
                ->chunk(300, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->id,
                            $row->recipient,
                            $row->subject,
                            $row->type,
                            $row->status,
                            $row->failure_reason,
                            $row->sent_at?->toDateTimeString(),
                            $row->created_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
