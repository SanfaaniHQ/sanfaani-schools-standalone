<?php

namespace App\Http\Controllers\Admin;

use App\Events\SchoolNotificationRequested;
use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportThread;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportThreadController extends Controller
{
    public function index(Request $request)
    {
        $threads = SupportThread::query()
            ->with(['school', 'creator', 'assignedUser', 'latestMessage.sender'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->input('priority')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search) {
                    $query->where('subject', 'like', "%{$search}%")
                        ->orWhereHas('school', fn ($schoolQuery) => $schoolQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE WHEN status IN ('open', 'awaiting_response', 'in_progress') THEN 0 ELSE 1 END")
            ->latest('last_message_at')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.support-threads.index', [
            'threads' => $threads,
            'statuses' => SupportThread::STATUSES,
            'priorities' => SupportThread::PRIORITIES,
            'filters' => $request->only(['status', 'priority', 'search']),
        ]);
    }

    public function show(SupportThread $thread)
    {
        return view('admin.support-threads.show', [
            'thread' => $thread->load(['school', 'creator', 'assignedUser', 'messages.sender']),
            'statuses' => SupportThread::STATUSES,
            'priorities' => SupportThread::PRIORITIES,
            'assignees' => User::role('super_admin')->orderBy('name')->get(),
        ]);
    }

    public function reply(Request $request, SupportThread $thread, AuditLogService $auditLog)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
        ]);

        SupportMessage::create([
            'support_thread_id' => $thread->id,
            'school_id' => $thread->school_id,
            'sender_id' => auth()->id(),
            'sender_role' => 'super_admin',
            'message' => $data['message'],
            'is_internal_note' => (bool) ($data['is_internal_note'] ?? false),
        ]);

        $thread->update([
            'status' => 'awaiting_response',
            'last_message_at' => now(),
        ]);

        $auditLog->log('support_thread_reply_posted', $thread, $thread->school, metadata: [
            'is_internal_note' => (bool) ($data['is_internal_note'] ?? false),
        ], request: $request);

        if (! (bool) ($data['is_internal_note'] ?? false) && $thread->school) {
            event(SchoolNotificationRequested::supportTicketUpdated(
                $thread->refresh(),
                'reply_posted',
                ['message' => 'A support team member replied to your ticket.']
            ));
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    public function status(Request $request, SupportThread $thread, AuditLogService $auditLog)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(SupportThread::STATUSES)],
            'priority' => ['nullable', Rule::in(SupportThread::PRIORITIES)],
        ]);

        $thread->update([
            'status' => $data['status'],
            'priority' => $data['priority'] ?? $thread->priority,
        ]);

        $auditLog->log('support_thread_status_updated', $thread, $thread->school, metadata: [
            'status' => $thread->status,
            'priority' => $thread->priority,
        ], request: $request);

        if ($thread->school) {
            event(SchoolNotificationRequested::supportTicketUpdated(
                $thread->refresh(),
                'status_updated',
                ['message' => 'Ticket status is now '.str_replace('_', ' ', $thread->status).'.']
            ));
        }

        return back()->with('success', 'Thread status updated.');
    }

    public function assign(Request $request, SupportThread $thread, AuditLogService $auditLog)
    {
        $data = $request->validate([
            'assigned_to' => ['nullable', Rule::exists('users', 'id')],
        ]);

        $assignee = null;
        if (filled($data['assigned_to'] ?? null)) {
            $assignee = User::role('super_admin')->findOrFail($data['assigned_to']);
        }

        $thread->update([
            'assigned_to' => $assignee?->id,
            'status' => $thread->status === 'open' ? 'in_progress' : $thread->status,
        ]);

        $auditLog->log('support_thread_assigned', $thread, $thread->school, metadata: [
            'assigned_to' => $thread->assigned_to,
        ], request: $request);

        return back()->with('success', 'Thread assignment updated.');
    }
}
