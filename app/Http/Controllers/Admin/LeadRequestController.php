<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadRequest;
use App\Models\User;
use App\Services\LeadCrmService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadRequestController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => ['nullable', Rule::in(['demo', 'contact'])],
            'status' => ['nullable', Rule::in(LeadRequest::ACCEPTED_STATUSES)],
            'assigned_to' => ['nullable', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value !== 'unassigned' && ! User::whereKey($value)->exists()) {
                    $fail('The selected lead owner is invalid.');
                }
            }],
            'conversion' => ['nullable', Rule::in(['converted', 'unconverted'])],
            'follow_up' => ['nullable', Rule::in(['overdue', 'upcoming'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $filters = $request->only([
            'type',
            'status',
            'assigned_to',
            'conversion',
            'follow_up',
            'date_from',
            'date_to',
            'search',
        ]);

        $leads = LeadRequest::query()
            ->with(['assignedTo:id,name,email', 'convertedSchool:id,name'])
            ->search($request->input('search'))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('assigned_to'), function ($query) use ($request) {
                return $request->input('assigned_to') === 'unassigned'
                    ? $query->whereNull('assigned_to')
                    : $query->where('assigned_to', $request->integer('assigned_to'));
            })
            ->when($request->input('conversion') === 'converted', fn ($query) => $query->converted())
            ->when($request->input('conversion') === 'unconverted', fn ($query) => $query->converted(false))
            ->when($request->input('follow_up') === 'overdue', function ($query) {
                $query->whereNotNull('next_follow_up_at')
                    ->where('next_follow_up_at', '<', now())
                    ->whereNotIn('status', [
                        LeadRequest::STATUS_CONVERTED,
                        LeadRequest::STATUS_LOST,
                        LeadRequest::STATUS_ARCHIVED,
                    ]);
            })
            ->when($request->input('follow_up') === 'upcoming', fn ($query) => $query->where('next_follow_up_at', '>=', now()))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.lead-requests.index', [
            'leads' => $leads,
            'filters' => $filters,
            'statuses' => LeadRequest::ACCEPTED_STATUSES,
            'owners' => $this->assignableOwners(),
        ]);
    }

    public function show(LeadRequest $leadRequest)
    {
        return view('admin.lead-requests.show', [
            'lead' => $leadRequest->load([
                'assignedTo:id,name,email',
                'convertedBy:id,name,email',
                'convertedSchool:id,name,slug',
                'ownershipHistories.oldOwner:id,name,email',
                'ownershipHistories.newOwner:id,name,email',
                'ownershipHistories.changedBy:id,name,email',
                'internalNotes.user:id,name,email',
                'communicationRecords.user:id,name,email',
                'communicationRecords.communicationLog:id,recipient,subject,status,sent_at',
                'timelineEvents.user:id,name,email',
            ]),
            'statuses' => LeadRequest::ACCEPTED_STATUSES,
            'owners' => $this->assignableOwners(),
        ]);
    }

    public function update(Request $request, LeadRequest $leadRequest, LeadCrmService $leadCrm)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(LeadRequest::ACCEPTED_STATUSES)],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')],
            'next_follow_up_at' => ['nullable', 'date'],
            'lost_reason' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'note_body' => ['nullable', 'string', 'max:5000'],
        ]);

        $leadCrm->updateLead($leadRequest, $request->user(), $data, $request);

        return redirect()
            ->route('admin.lead-requests.show', $leadRequest)
            ->with('success', 'Lead request updated.');
    }

    public function storeNote(Request $request, LeadRequest $leadRequest, LeadCrmService $leadCrm)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $leadCrm->addNote($leadRequest, $request->user(), $data['body'], ['source' => 'manual_note'], $request);

        return back()->with('success', 'Internal note added.');
    }

    public function storeCommunication(Request $request, LeadRequest $leadRequest, LeadCrmService $leadCrm)
    {
        $data = $request->validate([
            'channel' => ['required', Rule::in(['email', 'phone', 'sms', 'whatsapp', 'in_app', 'manual'])],
            'direction' => ['required', Rule::in(['outbound', 'inbound'])],
            'recipient' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['recorded', 'sent', 'failed', 'pending'])],
            'communicated_at' => ['nullable', 'date'],
        ]);

        $leadCrm->recordCommunication($leadRequest, $request->user(), $data, null, $request);

        return back()->with('success', 'Communication history recorded.');
    }

    public function convert(Request $request, LeadRequest $leadRequest, LeadCrmService $leadCrm)
    {
        $data = $request->validate([
            'school_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $school = $leadCrm->convertToSchool($leadRequest, $request->user(), $data, $request);

        return redirect()
            ->route('admin.schools.edit', $school)
            ->with('success', 'Lead converted into school onboarding.');
    }

    private function assignableOwners()
    {
        return User::query()
            ->select('id', 'name', 'email', 'school_id')
            ->where(function ($query) {
                $query->whereNull('school_id')
                    ->orWhereHas('roles', fn ($roles) => $roles->whereIn('name', ['super_admin', 'admin', 'support_staff']));
            })
            ->orderBy('name')
            ->limit(250)
            ->get();
    }
}
