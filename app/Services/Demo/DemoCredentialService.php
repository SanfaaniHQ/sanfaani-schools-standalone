<?php

namespace App\Services\Demo;

use App\Mail\DemoCredentialsMail;
use App\Models\DemoCredential;
use App\Models\DemoSession;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoCredentialService
{
    public function __construct(private DemoActivityService $activity) {}

    public function generateForSession(DemoSession $session): Collection
    {
        $session->loadMissing('school');

        return collect(config('demo.roles', []))
            ->map(fn (array $definition, string $roleName): DemoCredential => $this->createCredential($session, $roleName, $definition))
            ->values();
    }

    public function revealOnce(DemoCredential $credential): ?string
    {
        $password = $credential->revealTemporaryPassword();

        if ($password !== null) {
            $this->activity->log(
                $credential->demoSession,
                'demo.credentials_viewed',
                'Temporary credential was viewed once.',
                context: ['credential_id' => $credential->id, 'role_name' => $credential->role_name]
            );
        }

        return $password;
    }

    public function emailCredentials(DemoSession $session, string $recipientEmail): void
    {
        if (! (bool) config('demo.email_enabled', true)) {
            return;
        }

        $session->loadMissing(['credentials.user', 'school', 'demoRequest']);

        Mail::to($recipientEmail)->send(new DemoCredentialsMail($session));

        $this->activity->log($session, 'demo.email_sent', 'Demo credentials email was queued for delivery.', context: [
            'recipient' => $recipientEmail,
        ]);
    }

    private function createCredential(DemoSession $session, string $roleName, array $definition): DemoCredential
    {
        $assignedRole = (string) ($definition['assign_role'] ?? $roleName);
        Role::findOrCreate($assignedRole);

        $password = $this->temporaryPassword();
        $email = $this->emailFor($session, $roleName);

        $user = User::create([
            'school_id' => $session->school_id,
            'staff_code' => 'DEMO-'.strtoupper(Str::random(10)),
            'name' => $definition['label'] ?? str($roleName)->replace('_', ' ')->title().' demo',
            'email' => $email,
            'password' => Hash::make($password),
            'must_change_password' => false,
        ]);
        $user->assignRole($assignedRole);

        if ($session->school_id) {
            UserSchoolRole::create([
                'user_id' => $user->id,
                'school_id' => $session->school_id,
                'role_name' => $assignedRole,
                'status' => 'active',
                'metadata' => [
                    'demo_session_id' => $session->id,
                    'demo_role_name' => $roleName,
                ],
            ]);
        }

        $credential = DemoCredential::create([
            'demo_session_id' => $session->id,
            'user_id' => $user->id,
            'role_name' => $roleName,
            'label' => $definition['label'] ?? str($roleName)->replace('_', ' ')->title().' demo',
            'email' => $email,
            'temporary_password_encrypted' => $password,
            'expires_at' => $session->expires_at,
            'status' => DemoCredential::STATUS_ACTIVE,
            'metadata' => array_filter([
                'assigned_role' => $assignedRole,
                'simulated_role' => $roleName,
                'note' => $definition['note'] ?? null,
            ]),
        ]);

        $this->activity->log($session, 'demo.credentials_generated', 'Demo credential generated.', $user, [
            'credential_id' => $credential->id,
            'role_name' => $roleName,
            'assigned_role' => $assignedRole,
        ]);

        return $credential;
    }

    private function emailFor(DemoSession $session, string $roleName): string
    {
        return sprintf('demo+%d.%s@demo.sanfaani.test', $session->id, str($roleName)->replace('_', '-')->toString());
    }

    private function temporaryPassword(): string
    {
        return Str::password((int) config('demo.password_length', 14));
    }
}
