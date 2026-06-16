<?php

namespace App\Services\Users;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\AuditLogService;
use App\Services\SuperAdminAccountProtectionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserLifecycleService
{
    private const IMPORTANT_REFERENCES = [
        'academic_sessions' => ['created_by', 'updated_by'],
        'admission_documents' => ['reviewed_by'],
        'admission_notes' => ['user_id'],
        'admission_payments' => ['confirmed_by'],
        'admission_status_logs' => ['changed_by'],
        'audit_logs' => ['user_id', 'actor_id'],
        'backup_logs' => ['created_by'],
        'backups' => ['created_by'],
        'branding_settings' => ['created_by', 'updated_by'],
        'bulk_communication_batches' => ['sender_id'],
        'cbt_attempts' => ['user_id'],
        'cbt_event_logs' => ['user_id'],
        'cbt_exam_questions' => ['created_by', 'updated_by'],
        'cbt_exams' => ['created_by', 'updated_by', 'published_by'],
        'cbt_marking_records' => ['marked_by', 'moderated_by'],
        'cbt_question_banks' => ['created_by', 'updated_by'],
        'cbt_questions' => ['created_by', 'updated_by'],
        'cbt_result_publications' => ['published_by', 'revoked_by'],
        'class_subject_assignments' => ['assigned_by'],
        'communication_logs' => ['sender_id'],
        'demo_activities' => ['user_id'],
        'demo_credentials' => ['user_id'],
        'demo_sessions' => ['created_by'],
        'finance_fee_assignments' => ['created_by'],
        'finance_fee_items' => ['created_by'],
        'lead_notes' => ['user_id'],
        'lead_ownership_histories' => ['old_assigned_to', 'new_assigned_to', 'changed_by'],
        'lead_requests' => ['assigned_to', 'converted_by'],
        'lead_timeline_events' => ['user_id'],
        'live_classes' => ['teacher_user_id', 'created_by', 'updated_by'],
        'lms_cbt_activities' => ['created_by', 'updated_by'],
        'lms_classrooms' => ['created_by', 'updated_by'],
        'lms_materials' => ['teacher_user_id', 'created_by', 'updated_by'],
        'lms_resources' => ['uploaded_by'],
        'marketing_campaigns' => ['created_by', 'updated_by'],
        'marketing_email_templates' => ['created_by', 'updated_by'],
        'onboarding_event_logs' => ['user_id'],
        'onboarding_progress' => ['user_id'],
        'payment_transactions' => ['confirmed_by'],
        'pdf_snapshots' => ['generated_by'],
        'report_card_snapshots' => ['generated_by'],
        'result_publications' => ['published_by', 'unpublished_by', 'created_by'],
        'result_verifications' => ['revoked_by'],
        'sales_tasks' => ['assigned_to'],
        'school_feature_overrides' => ['created_by'],
        'school_notification_logs' => ['created_by'],
        'school_notification_templates' => ['created_by', 'updated_by'],
        'school_result_access_policies' => ['created_by'],
        'school_subscriptions' => ['created_by'],
        'scratch_card_batches' => ['created_by', 'approved_by', 'rejected_by'],
        'scratch_cards' => ['generated_by', 'last_exported_by'],
        'student_attendance_records' => ['recorded_by'],
        'student_class_enrollments' => ['created_by', 'updated_by'],
        'student_fee_assignments' => ['created_by'],
        'student_fee_invoices' => ['created_by'],
        'student_fee_payments' => ['received_by'],
        'student_promotion_batches' => ['created_by'],
        'student_results' => ['recorded_by', 'updated_by', 'approved_by'],
        'support_escalation_histories' => ['escalated_by'],
        'support_message_attachments' => ['uploaded_by'],
        'support_messages' => ['sender_id'],
        'support_thread_events' => ['actor_id'],
        'support_threads' => ['created_by', 'assigned_to', 'escalated_by'],
        'teacher_class_assignments' => ['teacher_user_id', 'assigned_by'],
        'teacher_result_submissions' => ['teacher_user_id', 'reviewed_by', 'approved_by', 'published_by', 'returned_by'],
        'teacher_subject_assignments' => ['teacher_user_id', 'assigned_by'],
        'update_logs' => ['created_by'],
        'update_packages' => ['uploaded_by'],
        'user_onboarding_progress' => ['user_id'],
    ];

    public function __construct(
        private AuditLogService $auditLog,
        private SuperAdminAccountProtectionService $superAdminProtection
    ) {}

    public function disable(User $user, ?School $school, array $roles, ?User $actor, ?Request $request = null): void
    {
        $this->assertCanRemoveActiveAdmin($user, $school, $roles, 'disable');

        if ($roles !== [] && $school) {
            UserSchoolRole::query()
                ->where('user_id', $user->id)
                ->where('school_id', $school->id)
                ->whereIn('role_name', $roles)
                ->update(['status' => 'inactive']);
        }

        if (! $this->hasOtherActiveSchoolRoles($user, $school)) {
            $user->forceFill(['disabled_at' => now()])->save();
        }
    }

    public function enable(User $user, ?School $school, array $roles, ?User $actor, ?Request $request = null): void
    {
        $user->forceFill(['disabled_at' => null])->save();

        if ($roles !== [] && $school) {
            UserSchoolRole::query()
                ->where('user_id', $user->id)
                ->where('school_id', $school->id)
                ->whereIn('role_name', $roles)
                ->update([
                    'status' => 'active',
                    'assigned_by' => $actor?->id,
                ]);
        }
    }

    public function archive(User $user, ?School $school, array $roles, ?User $actor, ?Request $request = null): void
    {
        $this->assertCanRemoveActiveAdmin($user, $school, $roles, 'archive');

        $user->forceFill([
            'disabled_at' => $user->disabled_at ?: now(),
            'archived_at' => now(),
        ])->save();

        if ($roles !== [] && $school) {
            UserSchoolRole::query()
                ->where('user_id', $user->id)
                ->where('school_id', $school->id)
                ->whereIn('role_name', $roles)
                ->update(['status' => 'archived']);
        }
    }

    public function restore(User $user, ?School $school, array $roles, ?User $actor, ?Request $request = null): void
    {
        $user->forceFill([
            'disabled_at' => null,
            'archived_at' => null,
        ])->save();

        if ($roles !== [] && $school) {
            UserSchoolRole::query()
                ->where('user_id', $user->id)
                ->where('school_id', $school->id)
                ->whereIn('role_name', $roles)
                ->update([
                    'status' => 'active',
                    'assigned_by' => $actor?->id,
                ]);
        }
    }

    /**
     * @return array{deleted: bool, archived: bool, reason: ?string}
     */
    public function deleteOrArchive(User $user, ?School $school, array $roles, ?User $actor, ?Request $request = null): array
    {
        $this->assertCanRemoveActiveAdmin($user, $school, $roles, 'delete');

        if ($this->hasImportantRecords($user)) {
            $this->archive($user, $school, $roles, $actor, $request);

            return [
                'deleted' => false,
                'archived' => true,
                'reason' => 'important_records_exist',
            ];
        }

        $this->superAdminProtection->assertCanDelete($user, $actor, request: $request);

        DB::transaction(function () use ($user): void {
            $user->roles()->detach();
            $user->schoolRoles()->delete();
            $user->delete();
        });

        return [
            'deleted' => true,
            'archived' => false,
            'reason' => null,
        ];
    }

    public function assertCanRemoveActiveAdmin(User $user, ?School $school, array $roles, string $action): void
    {
        if ($user->hasRole('super_admin')) {
            $this->superAdminProtection->assertCanDelete($user, auth()->user(), request: request());
        }

        if (! $school || ! in_array('school_admin', $roles, true)) {
            return;
        }

        if (! $this->isActiveSchoolAdmin($user, $school)) {
            return;
        }

        $remaining = $this->activeSchoolAdminQuery($school)
            ->whereKeyNot($user->id)
            ->exists();

        if ($remaining) {
            return;
        }

        try {
            $this->auditLog->log('school_admin_'.$action.'_blocked_last_active', $user, $school, metadata: [
                'action' => $action,
                'target_id' => $user->id,
            ]);
        } catch (Throwable) {
            //
        }

        throw ValidationException::withMessages([
            'user' => __('ui.last_active_admin_blocked'),
        ]);
    }

    public function hasImportantRecords(User $user): bool
    {
        foreach (self::IMPORTANT_REFERENCES as $table => $columns) {
            if (! $this->tableReady($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! $this->columnReady($table, $column)) {
                    continue;
                }

                if (DB::table($table)->where($column, $user->id)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasOtherActiveSchoolRoles(User $user, ?School $school): bool
    {
        return UserSchoolRole::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->when($school, fn (Builder $query) => $query->where('school_id', '!=', $school->id))
            ->exists();
    }

    private function isActiveSchoolAdmin(User $user, School $school): bool
    {
        return $user->isActiveAccount()
            && $user->hasRole('school_admin')
            && $user->schoolRoles()
                ->where('school_id', $school->id)
                ->where('role_name', 'school_admin')
                ->where('status', 'active')
                ->exists();
    }

    private function activeSchoolAdminQuery(School $school): Builder
    {
        return User::query()
            ->activeAccount()
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'school_admin'))
            ->whereHas('schoolRoles', fn (Builder $query) => $query
                ->where('school_id', $school->id)
                ->where('role_name', 'school_admin')
                ->where('status', 'active'));
    }

    private function tableReady(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function columnReady(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (Throwable) {
            return false;
        }
    }
}
