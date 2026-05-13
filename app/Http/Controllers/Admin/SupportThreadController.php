<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportThread;
use App\Models\User;
use App\Services\SupportRoutingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportThreadController extends Controller
{
    public function index(Request $request, SupportRoutingService $support)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(SupportThread::STATUSES)],
            'priority' => ['nullable', Rule::in(SupportThread::PRIORITIES)],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $threads = $support->visiblePlatformThreadsQuery()
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
            ->orderByRaw("CASE WHEN status IN ('open', 'pending', 'escalated', 'awaiting_response', 'in_progress') THEN 0 ELSE 1 END")
            ->latest('last_message_at')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.support-threads.index', [
            'threads' => $threads,
            'statuses' => SupportThread::STATUSES,
            'workflowStatuses' => SupportThread::WORKFLOW_STATUSES,
            'priorities' => SupportThread::PRIORITIES,
            'filters' => $request->only(['status', 'priority', 'search']),
        ]);
    }

    public function show(SupportThread $thread, SupportRoutingService $support)
    {
        abort_unless($support->visiblePlatformThreadsQuery()->whereKey($thread->getKey())->exists(), 403);

        $thread->load([
            'school',
            'creator',
            'assignedUser',
            'escalatedBy',
            'messages.sender',
            'events.actor',
            'escalationHistories.escalatedBy',
        ]);

        return view('admin.support-threads.show', [
            'thread' => $thread,
            'messages' => $support->visibleMessages($thread, request()->user(), 'super_admin'),
            'statuses' => SupportThread::WORKFLOW_STATUSES,
            'priorities' => SupportThread::PRIORITIES,
            'assignees' => User::role('super_admin')->orderBy('name')->get(),
        ]);
    }

    public function reply(Request $request, SupportThread $thread, SupportRoutingService $support)
    {
        abort_unless($support->visiblePlatformThreadsQuery()->whereKey($thread->getKey())->exists(), 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
        ]);

        $support->addReply($thread, $request->user(), 'super_admin', $data['message'], (bool) ($data['is_internal_note'] ?? false), $request);

        return back()->with('success', 'Reply sent successfully.');
    }

    public function status(Request $request, SupportThread $thread, SupportRoutingService $support)
    {
        abort_unless($support->visiblePlatformThreadsQuery()->whereKey($thread->getKey())->exists(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(SupportThread::WORKFLOW_STATUSES)],
            'priority' => ['nullable', Rule::in(SupportThread::PRIORITIES)],
        ]);

        $support->updateStatus($thread, $request->user(), 'super_admin', $data['status'], $data['priority'] ?? null, $request);

        return back()->with('success', 'Thread status updated.');
    }

    public function assign(Request $request, SupportThread $thread, SupportRoutingService $support)
    {
        abort_unless($support->visiblePlatformThreadsQuery()->whereKey($thread->getKey())->exists(), 403);

        $data = $request->validate([
            'assigned_to' => ['nullable', Rule::exists('users', 'id')],
        ]);

        $assignee = null;
        if (filled($data['assigned_to'] ?? null)) {
            $assignee = User::role('super_admin')->findOrFail($data['assigned_to']);
        }

        $support->assign($thread, $request->user(), 'super_admin', $assignee, $request);

        return back()->with('success', 'Thread assignment updated.');
    }
}
