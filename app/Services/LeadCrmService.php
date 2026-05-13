<?php

namespace App\Services;

use App\Models\CommunicationLog;
use App\Models\LeadCommunicationRecord;
use App\Models\LeadNote;
use App\Models\LeadOwnershipHistory;
use App\Models\LeadRequest;
use App\Models\LeadTimelineEvent;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class LeadCrmService
{
    public function __construct(
        private AuditLogService $auditLog,
        private SchoolCodeGeneratorService $schoolCodes
    ) {}

    public function updateLead(LeadRequest $lead, User $actor, array $data, ?Request $request = null): LeadRequest
    {
        return DB::transaction(function () use ($lead, $actor, $data, $request) {
            $lead = LeadRequest::whereKey($lead->getKey())->lockForUpdate()->firstOrFail();

            $oldStatus = $lead->status;
            $oldAssignedTo = $lead->assigned_to;
            $oldFollowUp = $lead->next_follow_up_at?->toDateTimeString();

            $attributes = [];

            if (array_key_exists('status', $data)) {
                $attributes['status'] = $data['status'];
            }

            if (array_key_exists('assigned_to', $data)) {
                $attributes['assigned_to'] = filled($data['assigned_to']) ? (int) $data['assigned_to'] : null;
            }

            if (array_key_exists('next_follow_up_at', $data)) {
                $attributes['next_follow_up_at'] = filled($data['next_follow_up_at']) ? $data['next_follow_up_at'] : null;
            }

            if (array_key_exists('lost_reason', $data)) {
                $attributes['lost_reason'] = filled($data['lost_reason']) ? $data['lost_reason'] : null;
            }

            if (array_key_exists('notes', $data)) {
                $attributes['notes'] = filled($data['notes']) ? $data['notes'] : null;
            }

            $this->applyStatusSideEffects($lead, $actor, $attributes);
            $dirty = $this->dirtyValues($lead, $attributes);

            if ($dirty !== []) {
                $attributes['last_activity_at'] = now();
                $lead->fill($attributes)->save();
            }

            if (array_key_exists('assigned_to', $attributes) && (int) $oldAssignedTo !== (int) $attributes['assigned_to']) {
                $this->recordOwnershipChange($lead, $actor, $oldAssignedTo, $attributes['assigned_to'], $request);
            }

            if (array_key_exists('status', $attributes) && $oldStatus !== $attributes['status']) {
                $this->timeline($lead, $actor, 'status_changed', 'Status changed', $lead->statusLabel(), [
                    'old_status' => $oldStatus,
                    'new_status' => $attributes['status'],
                ]);
                $this->audit('lead_status_changed', $lead, $actor, [
                    'status' => $oldStatus,
                ], [
                    'status' => $attributes['status'],
                ], $request);
            }

            $newFollowUp = $lead->next_follow_up_at?->toDateTimeString();
            if ($oldFollowUp !== $newFollowUp) {
                $this->timeline($lead, $actor, 'follow_up_scheduled', 'Follow-up updated', $newFollowUp, [
                    'old_next_follow_up_at' => $oldFollowUp,
                    'new_next_follow_up_at' => $newFollowUp,
                ]);
                $this->audit('lead_follow_up_updated', $lead, $actor, [
                    'next_follow_up_at' => $oldFollowUp,
                ], [
                    'next_follow_up_at' => $newFollowUp,
                ], $request);
            }

            if (filled($data['note_body'] ?? null)) {
                $this->addNote($lead, $actor, $data['note_body'], ['source' => 'lead_update'], $request);
            }

            return $lead->fresh([
                'assignedTo',
                'convertedSchool',
                'internalNotes.user',
                'communicationRecords.user',
                'timelineEvents.user',
                'ownershipHistories.oldOwner',
                'ownershipHistories.newOwner',
            ]);
        });
    }

    public function addNote(
        LeadRequest $lead,
        User $actor,
        string $body,
        array $metadata = [],
        ?Request $request = null
    ): LeadNote {
        return DB::transaction(function () use ($lead, $actor, $body, $metadata, $request) {
            $lead = LeadRequest::whereKey($lead->getKey())->lockForUpdate()->firstOrFail();

            $note = LeadNote::create([
                'lead_request_id' => $lead->id,
                'user_id' => $actor->id,
                'note_type' => 'internal',
                'body' => $body,
                'metadata' => $metadata,
            ]);

            $lead->forceFill([
                'notes' => $body,
                'last_activity_at' => now(),
            ])->save();

            $this->timeline($lead, $actor, 'note_added', 'Internal note added', Str::limit($body, 180), [
                'lead_note_id' => $note->id,
            ]);
            $this->audit('lead_note_added', $lead, $actor, [], [
                'lead_note_id' => $note->id,
            ], $request);

            return $note;
        });
    }

    public function recordCommunication(
        LeadRequest $lead,
        ?User $actor,
        array $data,
        ?CommunicationLog $communicationLog = null,
        ?Request $request = null
    ): LeadCommunicationRecord {
        return DB::transaction(function () use ($lead, $actor, $data, $communicationLog, $request) {
            $lead = LeadRequest::whereKey($lead->getKey())->lockForUpdate()->firstOrFail();
            $communicatedAt = filled($data['communicated_at'] ?? null) ? $data['communicated_at'] : now();

            $record = LeadCommunicationRecord::create([
                'lead_request_id' => $lead->id,
                'user_id' => $actor?->id,
                'communication_log_id' => $communicationLog?->exists ? $communicationLog->id : null,
                'channel' => $data['channel'] ?? 'email',
                'direction' => $data['direction'] ?? 'outbound',
                'recipient' => $data['recipient'] ?? $communicationLog?->recipient ?? $lead->email ?? $lead->phone,
                'subject' => $data['subject'] ?? $communicationLog?->subject,
                'body' => $data['body'] ?? data_get($communicationLog?->metadata, 'original_message'),
                'status' => $data['status'] ?? $communicationLog?->status ?? 'recorded',
                'communicated_at' => $communicatedAt,
                'metadata' => array_filter($data['metadata'] ?? []),
            ]);

            $lead->forceFill([
                'contacted_at' => $lead->contacted_at ?? now(),
                'last_activity_at' => now(),
            ])->save();

            $this->timeline($lead, $actor, 'communication_recorded', 'Communication recorded', $record->subject ?: $record->channel, [
                'lead_communication_record_id' => $record->id,
                'communication_log_id' => $record->communication_log_id,
                'channel' => $record->channel,
                'direction' => $record->direction,
                'status' => $record->status,
            ]);
            $this->audit('lead_communication_recorded', $lead, $actor, [], [
                'lead_communication_record_id' => $record->id,
                'communication_log_id' => $record->communication_log_id,
            ], $request);

            return $record;
        });
    }

    public function convertToSchool(LeadRequest $lead, User $actor, array $data = [], ?Request $request = null): School
    {
        return DB::transaction(function () use ($lead, $actor, $data, $request) {
            $lead = LeadRequest::whereKey($lead->getKey())->lockForUpdate()->firstOrFail();

            if ($lead->convertedSchool) {
                return $lead->convertedSchool;
            }

            $oldStatus = $lead->status;
            $schoolName = trim((string) ($data['school_name'] ?? $lead->school_name ?? $lead->name));
            $schoolName = $schoolName !== '' ? $schoolName : 'New School '.$lead->id;

            $school = School::create([
                'name' => $schoolName,
                'slug' => $this->uniqueSchoolSlug($schoolName),
                'school_code' => $this->schoolCodes->generateForName($schoolName),
                'email' => $data['email'] ?? $lead->email,
                'phone' => $data['phone'] ?? $lead->phone,
                'address' => $data['address'] ?? null,
                'status' => 'active',
                'subscription_status' => 'trial',
                'default_language' => 'en',
                'supports_rtl' => false,
            ]);

            $metadata = $lead->metadata ?? [];
            data_set($metadata, 'conversion.original_lead_snapshot', $this->leadSnapshot($lead));
            data_set($metadata, 'conversion.converted_school_id', $school->id);
            data_set($metadata, 'conversion.converted_by', $actor->id);
            data_set($metadata, 'conversion.converted_at', now()->toDateTimeString());

            $lead->forceFill([
                'status' => LeadRequest::STATUS_CONVERTED,
                'converted_at' => now(),
                'converted_by' => $actor->id,
                'converted_school_id' => $school->id,
                'archived_at' => null,
                'metadata' => $metadata,
                'last_activity_at' => now(),
            ])->save();

            $this->timeline($lead, $actor, 'converted', 'Lead converted', $school->name, [
                'school_id' => $school->id,
            ]);
            $this->audit('lead_converted', $lead, $actor, [
                'status' => $oldStatus,
                'converted_school_id' => null,
            ], [
                'status' => LeadRequest::STATUS_CONVERTED,
                'converted_school_id' => $school->id,
            ], $request);

            return $school;
        });
    }

    public function recordSystemEvent(
        LeadRequest $lead,
        string $eventType,
        string $title,
        ?string $body = null,
        array $metadata = []
    ): ?LeadTimelineEvent {
        return $this->timeline($lead, null, $eventType, $title, $body, $metadata);
    }

    private function applyStatusSideEffects(LeadRequest $lead, User $actor, array &$attributes): void
    {
        $status = $attributes['status'] ?? null;

        if ($status === null) {
            return;
        }

        if ($status === LeadRequest::STATUS_CONTACTED && ! $lead->contacted_at) {
            $attributes['contacted_at'] = now();
        }

        if ($status === LeadRequest::STATUS_ARCHIVED) {
            $attributes['archived_at'] = now();
        } elseif ($lead->archived_at) {
            $attributes['archived_at'] = null;
        }

        if ($status === LeadRequest::STATUS_CONVERTED && ! $lead->converted_at) {
            $attributes['converted_at'] = now();
            $attributes['converted_by'] = $actor->id;
        }
    }

    private function recordOwnershipChange(
        LeadRequest $lead,
        User $actor,
        ?int $oldAssignedTo,
        ?int $newAssignedTo,
        ?Request $request = null
    ): void {
        $history = LeadOwnershipHistory::create([
            'lead_request_id' => $lead->id,
            'old_assigned_to' => $oldAssignedTo,
            'new_assigned_to' => $newAssignedTo,
            'changed_by' => $actor->id,
            'changed_at' => now(),
            'metadata' => ['source' => 'crm_update'],
        ]);

        $this->timeline($lead, $actor, 'ownership_changed', 'Owner changed', null, [
            'lead_ownership_history_id' => $history->id,
            'old_assigned_to' => $oldAssignedTo,
            'new_assigned_to' => $newAssignedTo,
        ]);
        $this->audit('lead_ownership_changed', $lead, $actor, [
            'assigned_to' => $oldAssignedTo,
        ], [
            'assigned_to' => $newAssignedTo,
        ], $request);
    }

    private function timeline(
        LeadRequest $lead,
        ?User $actor,
        string $eventType,
        string $title,
        ?string $body = null,
        array $metadata = []
    ): ?LeadTimelineEvent {
        if (! $this->tableIsReady('lead_timeline_events')) {
            return null;
        }

        try {
            return LeadTimelineEvent::create([
                'lead_request_id' => $lead->id,
                'user_id' => $actor?->id,
                'event_type' => $eventType,
                'title' => $title,
                'body' => $body,
                'metadata' => $metadata,
                'occurred_at' => now(),
            ]);
        } catch (Throwable) {
            return null;
        }
    }

    private function audit(
        string $action,
        LeadRequest $lead,
        ?User $actor,
        array $oldValues = [],
        array $newValues = [],
        ?Request $request = null
    ): void {
        if (! $this->tableIsReady('audit_logs')) {
            return;
        }

        try {
            $this->auditLog->log($action, $lead, null, $oldValues, $newValues, [
                'actor_id' => $actor?->id,
                'crm_workflow' => true,
            ], $request);
        } catch (Throwable) {
            //
        }
    }

    private function dirtyValues(LeadRequest $lead, array $attributes): array
    {
        return collect($attributes)
            ->filter(fn ($value, $key) => $lead->getAttribute($key) != $value)
            ->all();
    }

    private function leadSnapshot(LeadRequest $lead): array
    {
        return Arr::only($lead->getAttributes(), [
            'id',
            'type',
            'name',
            'school_name',
            'email',
            'phone',
            'role',
            'number_of_students',
            'school_type',
            'preferred_demo_time',
            'message',
            'source',
            'status',
            'created_at',
        ]);
    }

    private function uniqueSchoolSlug(string $schoolName): string
    {
        $base = Str::slug($schoolName) ?: 'school';
        $slug = $base;
        $counter = 2;

        while (School::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
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
