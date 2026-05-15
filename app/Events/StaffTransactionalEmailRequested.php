<?php

namespace App\Events;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StaffTransactionalEmailRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public School $school,
        public User $staff,
        public string $eventKey,
        public string $recipient,
        public string $subject,
        public string $headline,
        public string $body,
        public array $metadata = [],
        public string $role = 'staff',
        public string $type = 'staff_transactional',
        public bool $respectPreferences = true
    ) {}

    public static function accountCreated(User $staff, School $school, string $role, bool $wasExistingUser = false): self
    {
        $roleLabel = self::roleLabel($role);
        $eventKey = $role === 'result_officer' ? 'result_officer_account_created' : 'teacher_account_created';
        $loginId = $staff->staff_code ?: $staff->email;

        return new self(
            $school,
            $staff,
            $eventKey,
            (string) $staff->email,
            $roleLabel.' account ready',
            'Staff account update',
            ($wasExistingUser
                ? 'Your existing account has been granted access to '.$school->name.'.'
                : 'A '.$roleLabel.' account has been created for you.')
                ."\nSchool: ".$school->name
                ."\nRole: ".$roleLabel
                ."\nLogin ID: ".$loginId
                ."\nUse the password provided by your school admin, or request a password reset if you cannot sign in.",
            [
                'staff_id' => $staff->id,
                'role' => $role,
                'role_label' => $roleLabel,
                'was_existing_user' => $wasExistingUser,
                'login_id' => $loginId,
                'action_url' => url('/login'),
                'action_label' => 'Open Login',
            ],
            $role,
            $eventKey
        );
    }

    public static function roleUpdated(User $staff, School $school, string $previousRole, string $newRole): self
    {
        $previousLabel = self::roleLabel($previousRole);
        $newLabel = self::roleLabel($newRole);

        return new self(
            $school,
            $staff,
            'staff_role_updated',
            (string) $staff->email,
            'Your staff role was updated',
            'Role update',
            'Your staff role for '.$school->name.' has been updated.'
                ."\nPrevious role: ".$previousLabel
                ."\nCurrent role: ".$newLabel,
            [
                'staff_id' => $staff->id,
                'previous_role' => $previousRole,
                'previous_role_label' => $previousLabel,
                'new_role' => $newRole,
                'new_role_label' => $newLabel,
            ],
            $newRole,
            'staff_role_updated'
        );
    }

    public static function accountStatusChanged(User $staff, School $school, string $role, bool $enabled): self
    {
        $roleLabel = self::roleLabel($role);
        $status = $enabled ? 'enabled' : 'disabled';

        return new self(
            $school,
            $staff,
            'staff_account_'.$status,
            (string) $staff->email,
            'Your school access was '.$status,
            'Account access update',
            'Your '.$roleLabel.' access for '.$school->name.' has been '.$status.'.'
                .($enabled
                    ? "\nYou can sign in again with your existing login details."
                    : "\nContact your school admin if you believe this change is incorrect."),
            [
                'staff_id' => $staff->id,
                'role' => $role,
                'role_label' => $roleLabel,
                'status' => $status,
                'action_url' => $enabled ? url('/login') : null,
                'action_label' => $enabled ? 'Open Login' : null,
            ],
            $role,
            'staff_account_'.$status
        );
    }

    private static function roleLabel(string $role): string
    {
        return ucwords(str_replace('_', ' ', $role ?: 'staff'));
    }
}
