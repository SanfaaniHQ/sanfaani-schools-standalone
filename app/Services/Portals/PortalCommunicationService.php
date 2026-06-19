<?php

namespace App\Services\Portals;

use App\Models\PortalConversation;
use App\Models\PortalMessage;
use App\Models\School;
use App\Models\TeacherReview;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Notifications\PortalMessageNotification;
use App\Notifications\TeacherReviewSubmittedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PortalCommunicationService
{
    public function conversationsFor(User $user, School $school)
    {
        return PortalConversation::query()
            ->where('school_id', $school->id)
            ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->with(['creator', 'participants.user', 'messages.sender'])
            ->withCount('messages')
            ->latest('last_message_at')
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function eligibleRecipients(User $user, School $school): Collection
    {
        $allowedRoles = $this->allowedRecipientRoles($user);

        if ($allowedRoles === []) {
            return collect();
        }

        $userIds = UserSchoolRole::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->whereIn('role_name', $allowedRoles)
            ->pluck('user_id')
            ->unique()
            ->values();

        return User::query()
            ->whereIn('id', $userIds)
            ->whereKeyNot($user->id)
            ->activeAccount()
            ->orderBy('name')
            ->get();
    }

    public function teachersForReview(User $user, School $school): Collection
    {
        $teacherIds = UserSchoolRole::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->where('role_name', 'teacher')
            ->pluck('user_id')
            ->unique()
            ->values();

        return User::query()
            ->whereIn('id', $teacherIds)
            ->whereKeyNot($user->id)
            ->activeAccount()
            ->orderBy('name')
            ->get();
    }

    public function createConversation(User $creator, School $school, array $data): PortalConversation
    {
        $recipientIds = collect($data['recipient_user_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($recipientIds->isEmpty()) {
            $recipientIds = $this->eligibleRecipients($creator, $school)->pluck('id')->take(3)->values();
        }

        if ($recipientIds->isEmpty()) {
            throw ValidationException::withMessages([
                'recipient_user_ids' => 'No eligible recipient is available for this conversation.',
            ]);
        }

        $allowedRecipients = $this->eligibleRecipients($creator, $school)->pluck('id')->all();

        foreach ($recipientIds as $recipientId) {
            if (! in_array((int) $recipientId, array_map('intval', $allowedRecipients), true)) {
                throw ValidationException::withMessages([
                    'recipient_user_ids' => 'One or more selected recipients are not allowed for this school conversation.',
                ]);
            }
        }

        return DB::transaction(function () use ($creator, $school, $data, $recipientIds) {
            $conversation = PortalConversation::query()->create([
                'school_id' => $school->id,
                'created_by' => $creator->id,
                'subject' => $data['subject'],
                'conversation_type' => $data['conversation_type'] ?? 'general',
                'status' => PortalConversation::STATUS_OPEN,
                'last_message_at' => now(),
                'metadata' => [
                    'source' => 'stage_e_portal_chat',
                ],
            ]);

            $this->attachParticipant($conversation, $school, $creator);

            foreach ($recipientIds as $recipientId) {
                $recipient = User::query()->find($recipientId);

                if ($recipient) {
                    $this->attachParticipant($conversation, $school, $recipient);
                }
            }

            $message = $this->createMessage($conversation, $creator, $school, $data['body']);

            $this->notifyParticipants($conversation, $message, $creator);

            return $conversation->fresh(['participants.user', 'messages.sender']);
        });
    }

    public function createMessage(PortalConversation $conversation, User $sender, School $school, string $body): PortalMessage
    {
        if (! $this->canAccessConversation($sender, $conversation, $school)) {
            abort(403, 'You cannot send messages in this conversation.');
        }

        return DB::transaction(function () use ($conversation, $sender, $school, $body) {
            $message = PortalMessage::query()->create([
                'portal_conversation_id' => $conversation->id,
                'school_id' => $school->id,
                'sender_user_id' => $sender->id,
                'body' => $body,
                'status' => 'sent',
                'metadata' => [
                    'source' => 'stage_e_portal_chat',
                ],
            ]);

            $conversation->forceFill([
                'last_message_at' => now(),
                'status' => PortalConversation::STATUS_OPEN,
            ])->save();

            $conversation->participants()
                ->where('user_id', $sender->id)
                ->update(['last_read_at' => now()]);

            $this->notifyParticipants($conversation->fresh('participants.user'), $message, $sender);

            return $message;
        });
    }

    public function canAccessConversation(User $user, PortalConversation $conversation, School $school): bool
    {
        if ((int) $conversation->school_id !== (int) $school->id) {
            return false;
        }

        return $conversation->participants()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function submitTeacherReview(User $reviewer, School $school, array $data): TeacherReview
    {
        $teacher = User::query()->findOrFail($data['teacher_user_id']);

        if (! $this->teachersForReview($reviewer, $school)->pluck('id')->contains($teacher->id)) {
            throw ValidationException::withMessages([
                'teacher_user_id' => 'The selected teacher is not available for review.',
            ]);
        }

        $review = TeacherReview::query()->create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'reviewer_user_id' => $reviewer->id,
            'student_id' => $data['student_id'] ?? null,
            'rating' => (int) $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'status' => TeacherReview::STATUS_PENDING,
            'metadata' => [
                'source' => 'stage_e_teacher_review',
                'category_ratings' => collect($data['category_ratings'] ?? [])
                    ->only(array_keys(TeacherReview::CATEGORY_RATINGS))
                    ->filter(fn ($rating): bool => filled($rating))
                    ->map(fn ($rating): int => (int) $rating)
                    ->all(),
            ],
        ]);

        $this->notifySchoolAdmins($school, new TeacherReviewSubmittedNotification($review));

        return $review;
    }

    public function schoolAdmins(School $school): Collection
    {
        $adminIds = UserSchoolRole::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->whereIn('role_name', ['school_admin', 'result_officer'])
            ->pluck('user_id')
            ->unique()
            ->values();

        return User::query()
            ->whereIn('id', $adminIds)
            ->activeAccount()
            ->get();
    }

    private function allowedRecipientRoles(User $user): array
    {
        if ($user->hasRole('parent') || $user->hasRole('student')) {
            return ['school_admin', 'teacher', 'result_officer', 'accountant'];
        }

        if ($user->hasRole('teacher')) {
            return ['school_admin', 'result_officer'];
        }

        if ($user->hasRole('school_admin') || $user->hasRole('result_officer') || $user->hasRole('accountant') || $user->hasRole('super_admin')) {
            return ['parent', 'student', 'teacher', 'school_admin', 'result_officer', 'accountant'];
        }

        return [];
    }

    private function attachParticipant(PortalConversation $conversation, School $school, User $user): void
    {
        $conversation->participants()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'school_id' => $school->id,
            'participant_role' => $user->roles()->pluck('name')->first(),
            'last_read_at' => $conversation->created_by === $user->id ? now() : null,
        ]);
    }

    private function notifyParticipants(PortalConversation $conversation, PortalMessage $message, User $sender): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        $conversation->loadMissing('participants.user');

        foreach ($conversation->participants as $participant) {
            $user = $participant->user;

            if (! $user || (int) $user->id === (int) $sender->id) {
                continue;
            }

            $user->notify(new PortalMessageNotification($conversation, $message));
        }
    }

    private function notifySchoolAdmins(School $school, object $notification): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        foreach ($this->schoolAdmins($school) as $admin) {
            $admin->notify($notification);
        }
    }
}
