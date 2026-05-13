<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SupportThread;
use App\Models\User;
use App\Services\CurrentSchoolService;
use App\Services\SupportRoutingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportThreadController extends Controller
{
    public function index(Request $request, SupportRoutingService $support)
    {
        $school = $this->currentSchoolOrFail();
        $role = $support->roleFor($request->user());

        $request->validate([
            'status' => ['nullable', Rule::in(SupportThread::STATUSES)],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $threads = $support->visibleSchoolThreadsQuery($school, $request->user(), $role)
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
            'role' => $role,
            'threads' => $threads,
            'statuses' => SupportThread::STATUSES,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(Request $request, SupportRoutingService $support)
    {
        $school = $this->currentSchoolOrFail();
        $role = $support->roleFor($request->user());

        return view('school.support.create', [
            'school' => $school,
            'role' => $role,
            'categories' => SupportThread::CATEGORIES,
            'priorities' => SupportThread::PRIORITIES,
            'canDirectEscalate' => $role === 'school_admin' || app(\App\Services\SchoolAuthorizationService::class)->can($request->user(), $school, 'support.direct_escalation'),
        ]);
    }

    public function store(Request $request, SupportRoutingService $support)
    {
        $school = $this->currentSchoolOrFail();
        $role = $support->roleFor($request->user());

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(SupportThread::CATEGORIES)],
            'priority' => ['required', Rule::in(SupportThread::PRIORITIES)],
            'message' => ['required', 'string', 'max:5000'],
            'route_to' => ['nullable', Rule::in([SupportThread::ROUTE_SCHOOL_ADMIN, SupportThread::ROUTE_SUPER_ADMIN])],
            'escalation_reason' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['route_to'] ??= $role === 'school_admin' ? SupportThread::ROUTE_SUPER_ADMIN : SupportThread::ROUTE_SCHOOL_ADMIN;

        $thread = $support->createThread($school, $request->user(), $role, $data, $request);

        return redirect()
            ->route('school.support.show', $thread)
            ->with('success', 'Support request submitted successfully.');
    }

    public function show(Request $request, SupportThread $thread, SupportRoutingService $support)
    {
        $school = $this->currentSchoolOrFail();
        $role = $support->roleFor($request->user());
        $this->authorizeThread($support, $thread, $school, $request->user(), $role);

        $thread->load([
            'creator',
            'assignedUser',
            'escalatedBy',
            'messages.sender',
            'events.actor',
            'escalationHistories.escalatedBy',
        ]);

        return view('school.support.show', [
            'school' => $school,
            'role' => $role,
            'thread' => $thread,
            'messages' => $support->visibleMessages($thread, $request->user(), $role),
            'assignees' => $this->schoolAssignees($school),
            'canAssign' => $role === 'school_admin' && $thread->routed_to_role !== SupportThread::ROUTE_SUPER_ADMIN,
            'canEscalate' => $support->canEscalate($thread, $request->user(), $school, $role),
        ]);
    }

    public function reply(Request $request, SupportThread $thread, SupportRoutingService $support)
    {
        $school = $this->currentSchoolOrFail();
        $role = $support->roleFor($request->user());
        $this->authorizeThread($support, $thread, $school, $request->user(), $role);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
        ]);

        $canUseInternalNote = $role === 'school_admin';
        $support->addReply($thread, $request->user(), $role, $data['message'], $canUseInternalNote && (bool) ($data['is_internal_note'] ?? false), $request);

        return back()->with('success', 'Reply sent successfully.');
    }

    public function assign(Request $request, SupportThread $thread, SupportRoutingService $support)
    {
        $school = $this->currentSchoolOrFail();
        $role = $support->roleFor($request->user());
        $this->authorizeThread($support, $thread, $school, $request->user(), $role);
        abort_unless($role === 'school_admin' && $thread->routed_to_role !== SupportThread::ROUTE_SUPER_ADMIN, 403);

        $data = $request->validate([
            'assigned_to' => ['nullable', Rule::exists('users', 'id')],
        ]);

        $assignee = null;
        if (filled($data['assigned_to'] ?? null)) {
            $assignee = $this->schoolAssignees($school)->firstWhere('id', (int) $data['assigned_to']);
            abort_unless($assignee, 422, 'The selected assignee is not active in this school.');
        }

        $support->assign($thread, $request->user(), $role, $assignee, $request);

        return back()->with('success', 'Thread assignment updated.');
    }

    public function escalate(Request $request, SupportThread $thread, SupportRoutingService $support)
    {
        $school = $this->currentSchoolOrFail();
        $role = $support->roleFor($request->user());
        $this->authorizeThread($support, $thread, $school, $request->user(), $role);
        abort_unless($support->canEscalate($thread, $request->user(), $school, $role), 403);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $support->escalate($thread, $request->user(), $role, $data['reason'] ?? null, $request);

        return back()->with('success', 'Thread escalated to Super Admin.');
    }

    public function close(Request $request, SupportThread $thread, SupportRoutingService $support)
    {
        $school = $this->currentSchoolOrFail();
        $role = $support->roleFor($request->user());
        $this->authorizeThread($support, $thread, $school, $request->user(), $role);

        $support->updateStatus($thread, $request->user(), $role, SupportThread::STATUS_CLOSED, null, $request);

        return back()->with('success', 'Thread closed successfully.');
    }

    private function authorizeThread(SupportRoutingService $support, SupportThread $thread, School $school, User $user, string $role): void
    {
        abort_unless($support->visibleSchoolThreadsQuery($school, $user, $role)->whereKey($thread->getKey())->exists(), 403, 'You cannot access this support thread.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function schoolAssignees(School $school)
    {
        return User::query()
            ->select('id', 'name', 'email', 'school_id')
            ->where(function ($query) use ($school) {
                $query->where('school_id', $school->id)
                    ->orWhereHas('activeSchoolRoles', fn ($roles) => $roles->where('school_id', $school->id));
            })
            ->orderBy('name')
            ->get();
    }
}
