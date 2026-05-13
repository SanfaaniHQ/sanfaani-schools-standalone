<?php

namespace App\Services;

use App\Events\SchoolNotificationRequested;
use App\Models\CommunicationLog;
use App\Models\School;
use App\Models\SupportEscalationHistory;
use App\Models\SupportMessage;
use App\Models\SupportThread;
use App\Models\SupportThreadEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SupportRoutingService
{
    public function __construct(
        private AuditLogService $auditLog,
        private CurrentSchoolService $currentSchool,
        private SchoolAuthorizationService $authorization,
        private CommunicationService $communications
    ) {}

    public function roleFor(User $user): string
    {
        return $this->currentSchool->roleContext($user) ?: ($user->hasRole('super_admin') ? 'super_admin' : 'school');
    }

    public function visibleSchoolThreadsQuery(School $school, User $user, ?string $role = null): Builder
    {
        $role ??= $this->roleFor($user);

        return SupportThread::query()
            ->where('school_id', $school->id)
            ->when(! in_array($role, ['school_admin', 'super_admin'], true), fn (Builder $query) => $query->where('created_by', $user->id));
    }

    public function visiblePlatformThreadsQuery(): Builder
    {
        return SupportThread::query()
            ->where(function (Builder $query) {
                $query->whereNull('school_id')
                    ->orWhereNull('routed_to_role')
                    ->orWhere('routed_to_role', SupportThread::ROUTE_SUPER_ADMIN)
                    ->orWhere('escalation_level', '>', 0)
                    ->orWhereIn('visibility', [SupportThread::VISIBILITY_ESCALATED, SupportThread::VISIBILITY_PLATFORM]);
            });
    }

    public function createThread(School $school, User $actor, string $actorRole, array $data, ?Request $request = null): SupportThread
    {
        return DB::transaction(function () use ($school, $actor, $actorRole, $data, $request) {
            $routeToPlatform = $this->shouldRouteToPlatform($school, $actor, $actorRole, $data);
            $status = $routeToPlatform ? SupportThread::STATUS_ESCALATED : SupportThread::STATUS_OPEN;
            $route = $routeToPlatform ? SupportThread::ROUTE_SUPER_ADMIN : SupportThread::ROUTE_SCHOOL_ADMIN;

            $thread = SupportThread::create([
                'school_id' => $school->id,
                'created_by' => $actor->id,
                'creator_role' => $actorRole,
                'assigned_to' => null,
                'routed_to_role' => $route,
                'subject' => $data['subject'],
                'category' => $data['category'],
                'priority' => $data['priority'],
                'status' => $status,
                'visibility' => $routeToPlatform ? SupportThread::VISIBILITY_ESCALATED : SupportThread::VISIBILITY_INTERNAL,
                'escalation_level' => $routeToPlatform ? 1 : 0,
                'escalated_at' => $routeToPlatform ? now() : null,
                'escalated_by' => $routeToPlatform ? $actor->id : null,
                'last_message_at' => now(),
                'metadata' => [
                    'routing' => [
                        'queue_ready' => true,
                        'notification_ready' => true,
                        'created_via' => 'school_support_portal',
                    ],
                ],
            ]);

            SupportMessage::create([
                'support_thread_id' => $thread->id,
                'school_id' => $school->id,
                'sender_id' => $actor->id,
                'sender_role' => $actorRole,
                'message' => $data['message'],
                'is_internal_note' => false,
            ]);

            $this->event($thread, $actor, $actorRole, 'created', 'Ticket created', $thread->subject, [], [
                'status' => $status,
                'routed_to_role' => $route,
            ]);

            if ($routeToPlatform) {
                $this->recordEscalation($thread, $actor, $actorRole, $data['escalation_reason'] ?? 'Created as platform escalation.');
            }

            $this->audit('support_thread_created', $thread, $school, $actor, $actorRole, [], [
                'category' => $thread->category,
                'priority' => $thread->priority,
                'routed_to_role' => $thread->routed_to_role,
                'escalation_level' => $thread->escalation_level,
            ], $request);

            $this->notifyCreated($thread, $actorRole);

            return $thread;
        });
    }

    public function addReply(
        SupportThread $thread,
        User $actor,
        string $actorRole,
        string $message,
        bool $isInternalNote = false,
        ?Request $request = null
    ): SupportMessage {
        return DB::transaction(function () use ($thread, $actor, $actorRole, $message, $isInternalNote, $request) {
            $thread = SupportThread::whereKey($thread->getKey())->lockForUpdate()->firstOrFail();

            $supportMessage = SupportMessage::create([
                'support_thread_id' => $thread->id,
                'school_id' => $thread->school_id,
                'sender_id' => $actor->id,
                'sender_role' => $actorRole,
                'message' => $message,
                'is_internal_note' => $isInternalNote,
                'metadata' => ['notification_ready' => ! $isInternalNote],
            ]);

            $oldStatus = $thread->status;
            $newStatus = $isInternalNote ? $thread->status : $this->statusAfterReply($thread, $actorRole);

            $thread->forceFill([
                'status' => $newStatus,
                'last_message_at' => now(),
            ])->save();

            $this->event($thread, $actor, $actorRole, 'reply_posted', $isInternalNote ? 'Internal note added' : 'Reply posted', null, [
                'status' => $oldStatus,
            ], [
                'status' => $newStatus,
                'support_message_id' => $supportMessage->id,
                'is_internal_note' => $isInternalNote,
            ]);

            $this->audit('support_thread_reply_posted', $thread, $thread->school, $actor, $actorRole, [
                'status' => $oldStatus,
            ], [
                'status' => $newStatus,
                'is_internal_note' => $isInternalNote,
            ], $request);

            if (! $isInternalNote) {
                $this->notifyReply($thread->fresh(['school']) ?? $thread, $actorRole, $message);
            }

            return $supportMessage;
        });
    }

    public function updateStatus(
        SupportThread $thread,
        User $actor,
        string $actorRole,
        string $status,
        ?string $priority = null,
        ?Request $request = null
    ): SupportThread {
        return DB::transaction(function () use ($thread, $actor, $actorRole, $status, $priority, $request) {
            $thread = SupportThread::whereKey($thread->getKey())->lockForUpdate()->firstOrFail();
            $oldValues = $thread->only(['status', 'priority']);
            $attributes = [
                'status' => $status,
                'priority' => $priority ?: $thread->priority,
                'resolved_at' => $status === SupportThread::STATUS_RESOLVED ? ($thread->resolved_at ?? now()) : $thread->resolved_at,
                'closed_at' => $status === SupportThread::STATUS_CLOSED ? ($thread->closed_at ?? now()) : $thread->closed_at,
            ];

            if ($status === SupportThread::STATUS_ESCALATED && $thread->routed_to_role !== SupportThread::ROUTE_SUPER_ADMIN) {
                $attributes['routed_to_role'] = SupportThread::ROUTE_SUPER_ADMIN;
                $attributes['visibility'] = SupportThread::VISIBILITY_ESCALATED;
                $attributes['escalation_level'] = max(1, (int) $thread->escalation_level);
                $attributes['escalated_at'] = $thread->escalated_at ?? now();
                $attributes['escalated_by'] = $thread->escalated_by ?? $actor->id;
            }

            $thread->forceFill($attributes)->save();

            $this->event($thread, $actor, $actorRole, 'status_changed', 'Status updated', null, $oldValues, $thread->only(['status', 'priority']));
            $this->audit('support_thread_status_updated', $thread, $thread->school, $actor, $actorRole, $oldValues, $thread->only(['status', 'priority']), $request);
            $this->notifyStatus($thread->fresh(['school']) ?? $thread);

            return $thread;
        });
    }

    public function assign(
        SupportThread $thread,
        User $actor,
        string $actorRole,
        ?User $assignee,
        ?Request $request = null
    ): SupportThread {
        return DB::transaction(function () use ($thread, $actor, $actorRole, $assignee, $request) {
            $thread = SupportThread::whereKey($thread->getKey())->lockForUpdate()->firstOrFail();
            $oldAssignedTo = $thread->assigned_to;

            $thread->forceFill(['assigned_to' => $assignee?->id])->save();

            $this->event($thread, $actor, $actorRole, 'assigned', 'Assignment updated', null, [
                'assigned_to' => $oldAssignedTo,
            ], [
                'assigned_to' => $thread->assigned_to,
            ]);
            $this->audit('support_thread_assigned', $thread, $thread->school, $actor, $actorRole, [
                'assigned_to' => $oldAssignedTo,
            ], [
                'assigned_to' => $thread->assigned_to,
            ], $request);

            return $thread;
        });
    }

    public function escalate(
        SupportThread $thread,
        User $actor,
        string $actorRole,
        ?string $reason = null,
        ?Request $request = null
    ): SupportThread {
        return DB::transaction(function () use ($thread, $actor, $actorRole, $reason, $request) {
            $thread = SupportThread::whereKey($thread->getKey())->lockForUpdate()->firstOrFail();

            if ($thread->routed_to_role === SupportThread::ROUTE_SUPER_ADMIN && (int) $thread->escalation_level > 0) {
                return $thread;
            }

            $oldValues = $thread->only(['status', 'routed_to_role', 'visibility', 'escalation_level']);

            $thread->forceFill([
                'status' => SupportThread::STATUS_ESCALATED,
                'routed_to_role' => SupportThread::ROUTE_SUPER_ADMIN,
                'visibility' => SupportThread::VISIBILITY_ESCALATED,
                'escalation_level' => max(1, (int) $thread->escalation_level + 1),
                'escalated_at' => now(),
                'escalated_by' => $actor->id,
            ])->save();

            $history = $this->recordEscalation($thread, $actor, $actorRole, $reason);

            $this->event($thread, $actor, $actorRole, 'escalated', 'Escalated to Super Admin', $reason, $oldValues, [
                'status' => $thread->status,
                'routed_to_role' => $thread->routed_to_role,
                'visibility' => $thread->visibility,
                'escalation_level' => $thread->escalation_level,
                'support_escalation_history_id' => $history?->id,
            ]);
            $this->audit('support_thread_escalated', $thread, $thread->school, $actor, $actorRole, $oldValues, [
                'status' => $thread->status,
                'routed_to_role' => $thread->routed_to_role,
                'escalation_level' => $thread->escalation_level,
                'reason' => $reason,
            ], $request);
            $this->notifySuperAdmins($thread->fresh(['school']) ?? $thread, 'Support ticket escalated: '.$thread->subject, $reason ?: 'A support ticket was escalated to Super Admin.');

            return $thread;
        });
    }

    public function visibleMessages(SupportThread $thread, User $user, string $role): Collection
    {
        $messages = $thread->relationLoaded('messages')
            ? $thread->messages
            : $thread->messages()->with('sender')->oldest()->get();

        if ($role === 'super_admin') {
            return $messages;
        }

        if ($role === 'school_admin') {
            return $messages->filter(fn (SupportMessage $message) => ! $message->is_internal_note || $message->sender_role !== 'super_admin')->values();
        }

        return $messages->filter(fn (SupportMessage $message) => ! $message->is_internal_note)->values();
    }

    public function canEscalate(SupportThread $thread, User $user, School $school, string $role): bool
    {
        if ((int) $thread->school_id !== (int) $school->id || $thread->routed_to_role === SupportThread::ROUTE_SUPER_ADMIN) {
            return false;
        }

        if ($role === 'school_admin') {
            return true;
        }

        return (int) $thread->created_by === (int) $user->id
            && $this->authorization->can($user, $school, 'support.direct_escalation');
    }

    private function shouldRouteToPlatform(School $school, User $actor, string $actorRole, array $data): bool
    {
        if (($data['route_to'] ?? null) === SupportThread::ROUTE_SUPER_ADMIN) {
            return $actorRole === 'school_admin'
                || $this->authorization->can($actor, $school, 'support.direct_escalation');
        }

        return $actorRole === 'school_admin' && ($data['route_to'] ?? SupportThread::ROUTE_SUPER_ADMIN) === SupportThread::ROUTE_SUPER_ADMIN;
    }

    private function statusAfterReply(SupportThread $thread, string $actorRole): string
    {
        if ($actorRole === 'super_admin') {
            return SupportThread::STATUS_PENDING;
        }

        if ($actorRole === 'school_admin' && $thread->routed_to_role !== SupportThread::ROUTE_SUPER_ADMIN) {
            return SupportThread::STATUS_PENDING;
        }

        return $thread->routed_to_role === SupportThread::ROUTE_SUPER_ADMIN
            ? SupportThread::STATUS_ESCALATED
            : SupportThread::STATUS_OPEN;
    }

    private function recordEscalation(SupportThread $thread, User $actor, string $actorRole, ?string $reason): ?SupportEscalationHistory
    {
        if (! $this->tableIsReady('support_escalation_histories')) {
            return null;
        }

        return SupportEscalationHistory::create([
            'support_thread_id' => $thread->id,
            'school_id' => $thread->school_id,
            'escalated_by' => $actor->id,
            'from_role' => $actorRole,
            'to_role' => SupportThread::ROUTE_SUPER_ADMIN,
            'from_level' => max(0, (int) $thread->escalation_level - 1),
            'to_level' => max(1, (int) $thread->escalation_level),
            'reason' => $reason,
            'escalated_at' => now(),
            'metadata' => ['queue_ready' => true, 'notification_ready' => true],
        ]);
    }

    private function event(
        SupportThread $thread,
        ?User $actor,
        ?string $actorRole,
        string $eventType,
        string $title,
        ?string $body = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): ?SupportThreadEvent {
        if (! $this->tableIsReady('support_thread_events')) {
            return null;
        }

        return SupportThreadEvent::create([
            'support_thread_id' => $thread->id,
            'school_id' => $thread->school_id,
            'actor_id' => $actor?->id,
            'actor_role' => $actorRole,
            'event_type' => $eventType,
            'title' => $title,
            'body' => $body,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'metadata' => array_merge(['queue_ready' => true], $metadata),
            'occurred_at' => now(),
        ]);
    }

    private function audit(
        string $action,
        SupportThread $thread,
        ?School $school,
        ?User $actor,
        ?string $actorRole,
        array $oldValues = [],
        array $newValues = [],
        ?Request $request = null
    ): void {
        try {
            $this->auditLog->log($action, $thread, $school, $oldValues, $newValues, [
                'actor_id' => $actor?->id,
                'actor_role' => $actorRole,
                'support_routing' => true,
            ], $request);
        } catch (Throwable) {
            //
        }
    }

    private function notifyCreated(SupportThread $thread, string $actorRole): void
    {
        if ($thread->routed_to_role === SupportThread::ROUTE_SUPER_ADMIN) {
            $this->notifySuperAdmins($thread, 'Support ticket created: '.$thread->subject, 'A support ticket was routed to Super Admin.');

            return;
        }

        if ($thread->school) {
            event(SchoolNotificationRequested::supportTicketUpdated($thread, 'created', [
                'message' => 'A staff support ticket was created and routed to School Admin.',
                'target_actor_role' => $actorRole,
            ]));
        }
    }

    private function notifyReply(SupportThread $thread, string $actorRole, string $message): void
    {
        if ($thread->routed_to_role === SupportThread::ROUTE_SUPER_ADMIN && $actorRole !== 'super_admin') {
            $this->notifySuperAdmins($thread, 'Support ticket reply: '.$thread->subject, 'A school user replied to escalated support ticket #'.$thread->id.'.');

            return;
        }

        if ($thread->school && $actorRole === 'super_admin') {
            event(SchoolNotificationRequested::supportTicketUpdated($thread, 'reply_posted', [
                'message' => $message,
            ]));
        }
    }

    private function notifyStatus(SupportThread $thread): void
    {
        if ($thread->school) {
            event(SchoolNotificationRequested::supportTicketUpdated($thread, 'status_updated', [
                'message' => 'Ticket status is now '.str_replace('_', ' ', $thread->status).'.',
            ]));
        }
    }

    private function notifySuperAdmins(SupportThread $thread, string $subject, string $message): void
    {
        User::role('super_admin')
            ->whereNotNull('email')
            ->select('id', 'email')
            ->chunkById(50, function ($admins) use ($thread, $subject, $message) {
                foreach ($admins as $admin) {
                    $log = $this->communications->sendPlatformEmail(
                        $admin->email,
                        $subject,
                        'Support update',
                        $message,
                        'support_ticket_escalation',
                        ['thread_id' => $thread->id, 'school_id' => $thread->school_id],
                        CommunicationService::CATEGORY_PLATFORM_TRANSACTIONAL
                    );

                    if ($log instanceof CommunicationLog) {
                        $this->event($thread, null, 'system', 'notification_prepared', 'Super Admin notification prepared', null, [], [
                            'communication_log_id' => $log->id,
                            'communication_status' => $log->status,
                        ]);
                    }
                }
            });
    }

    private function tableIsReady(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }
}
