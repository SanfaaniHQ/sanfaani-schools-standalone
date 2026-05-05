<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SupportMessage;
use App\Models\SupportThread;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportThreadController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $threads = SupportThread::where('school_id', $school->id)
            ->with(['creator', 'assignedUser', 'latestMessage.sender'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where('subject', 'like', "%{$search}%");
            })
            ->latest('last_message_at')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('school.support.index', [
            'school' => $school,
            'threads' => $threads,
            'statuses' => SupportThread::STATUSES,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.support.create', [
            'school' => $school,
            'categories' => SupportThread::CATEGORIES,
            'priorities' => SupportThread::PRIORITIES,
        ]);
    }

    public function store(Request $request, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(SupportThread::CATEGORIES)],
            'priority' => ['required', Rule::in(SupportThread::PRIORITIES)],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $thread = SupportThread::create([
            'school_id' => $school->id,
            'created_by' => auth()->id(),
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open',
            'visibility' => 'internal',
            'last_message_at' => now(),
        ]);

        SupportMessage::create([
            'support_thread_id' => $thread->id,
            'school_id' => $school->id,
            'sender_id' => auth()->id(),
            'sender_role' => 'school',
            'message' => $data['message'],
            'is_internal_note' => false,
        ]);

        $auditLog->log('support_thread_created', $thread, $school, metadata: [
            'category' => $thread->category,
            'priority' => $thread->priority,
        ], request: $request);

        return redirect()
            ->route('school.support.show', $thread)
            ->with('success', 'Support request submitted successfully.');
    }

    public function show(SupportThread $thread)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeThread($thread, $school);

        return view('school.support.show', [
            'school' => $school,
            'thread' => $thread->load(['creator', 'assignedUser', 'messages.sender']),
        ]);
    }

    public function reply(Request $request, SupportThread $thread, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeThread($thread, $school);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        SupportMessage::create([
            'support_thread_id' => $thread->id,
            'school_id' => $school->id,
            'sender_id' => auth()->id(),
            'sender_role' => 'school',
            'message' => $data['message'],
            'is_internal_note' => false,
        ]);

        $thread->update([
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $auditLog->log('support_thread_reply_posted', $thread, $school, request: $request);

        return back()->with('success', 'Reply sent successfully.');
    }

    public function close(Request $request, SupportThread $thread, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeThread($thread, $school);

        $thread->update(['status' => 'closed']);

        $auditLog->log('support_thread_closed', $thread, $school, request: $request);

        return back()->with('success', 'Thread closed successfully.');
    }

    private function authorizeThread(SupportThread $thread, School $school): void
    {
        if ((int) $thread->school_id !== (int) $school->id) {
            abort(403, 'You cannot access this support thread.');
        }
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
