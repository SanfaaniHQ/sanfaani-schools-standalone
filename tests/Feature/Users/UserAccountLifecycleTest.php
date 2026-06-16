<?php

namespace Tests\Feature\Users;

use App\Mail\Transactional\StaffLifecycleMail;
use App\Models\AuditLog;
use App\Models\CommunicationLog;
use App\Models\MailSetting;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Users\UserAccountSetupNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserAccountLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $superAdmin;

    private User $schoolAdmin;

    private int $schoolCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'app.url' => 'https://portal.example.test',
            'mail.default' => 'log',
            'mail.from.address' => 'portal@example.test',
            'mail.from.name' => 'Sanfaani Schools',
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.installed' => true,
        ]);

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer'] as $role) {
            Role::findOrCreate($role);
        }

        $this->school = $this->createSchool('Stage A Academy');
        $this->superAdmin = $this->createSchoolAdmin($this->school, ['name' => 'Platform Owner']);
        $this->superAdmin->assignRole('super_admin');

        $this->schoolAdmin = $this->createSchoolAdmin($this->school, ['name' => 'School Admin']);
        $this->configurePlatformMailer();
    }

    public function test_local_school_admin_creation_sends_setup_email_without_manual_password(): void
    {
        Mail::fake();

        $response = $this->actingAsSuperAdmin()
            ->post(route('admin.local-admins.store'), [
                'name' => 'Second Local Admin',
                'email' => 'second-local-admin@example.test',
            ]);

        $response
            ->assertRedirect(route('admin.local-admins.index'))
            ->assertSessionHas('success', __('ui.school_admin_created_setup_sent'));

        $admin = User::where('email', 'second-local-admin@example.test')->firstOrFail();

        $this->assertTrue($admin->must_change_password);
        $this->assertTrue($admin->hasRole('school_admin'));
        $this->assertNotNull($admin->email_verified_at);
        $this->assertDatabaseHas('user_school_roles', [
            'user_id' => $admin->id,
            'school_id' => $this->school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        $this->assertSetupMailSent($admin->email, UserAccountSetupNotificationService::ACCOUNT_CREATED_SETUP_LINK);
        $this->assertSetupLog($admin->email, UserAccountSetupNotificationService::ACCOUNT_CREATED_SETUP_LINK, CommunicationLog::STATUS_SENT);
    }

    public function test_platform_school_admin_creation_sends_setup_email(): void
    {
        Mail::fake();
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
        ]);

        $response = $this->actingAsSuperAdmin()
            ->post(route('admin.schools.admins.store', $this->school), [
                'name' => 'Platform School Admin',
                'email' => 'platform-school-admin@example.test',
            ]);

        $response
            ->assertRedirect(route('admin.schools.admins.index', $this->school))
            ->assertSessionHas('success', __('ui.school_admin_created_setup_sent'));

        $admin = User::where('email', 'platform-school-admin@example.test')->firstOrFail();

        $this->assertTrue($admin->must_change_password);
        $this->assertTrue($admin->hasRole('school_admin'));
        $this->assertDatabaseHas('user_school_roles', [
            'user_id' => $admin->id,
            'school_id' => $this->school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        $this->assertSetupMailSent($admin->email, UserAccountSetupNotificationService::ACCOUNT_CREATED_SETUP_LINK);
    }

    public function test_account_creation_survives_setup_email_failure_and_shows_warning(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '',
            'mail.mailers.smtp.port' => 587,
            'mail.from.address' => 'portal@example.test',
            'mail.from.name' => 'Sanfaani Schools',
        ]);
        MailSetting::whereNull('school_id')->update(['is_enabled' => false]);

        $response = $this->actingAsSuperAdmin()
            ->post(route('admin.local-admins.store'), [
                'name' => 'Mail Failure Admin',
                'email' => 'mail-failure-admin@example.test',
            ]);

        $response
            ->assertRedirect(route('admin.local-admins.index'))
            ->assertSessionHas('warning', __('ui.account_created_setup_failed'));

        $admin = User::where('email', 'mail-failure-admin@example.test')->firstOrFail();

        $this->assertTrue($admin->hasRole('school_admin'));
        $this->assertSetupLog($admin->email, UserAccountSetupNotificationService::ACCOUNT_CREATED_SETUP_LINK, CommunicationLog::STATUS_FAILED);
    }

    public function test_setup_email_uses_reset_token_and_never_includes_raw_password(): void
    {
        Mail::fake();
        $rawPassword = 'known-secret-password';
        $staff = $this->createStaff('teacher', [
            'email' => 'teacher-setup@example.test',
            'password' => Hash::make($rawPassword),
            'staff_code' => 'STAFF-SETUP-001',
        ]);

        $result = app(UserAccountSetupNotificationService::class)->sendSetupLink(
            $staff,
            $this->school,
            UserAccountSetupNotificationService::ACCOUNT_SETUP_LINK_RESENT,
            'teacher',
            $this->schoolAdmin
        );

        $this->assertTrue($result['sent']);

        Mail::assertSent(StaffLifecycleMail::class, function (StaffLifecycleMail $mail) use ($rawPassword, $staff): bool {
            $rendered = $mail->render();

            return $mail->hasTo($staff->email)
                && data_get($mail->mailMetadata, 'event_key') === UserAccountSetupNotificationService::ACCOUNT_SETUP_LINK_RESENT
                && str_starts_with((string) data_get($mail->mailMetadata, 'action_url'), 'https://portal.example.test/reset-password/')
                && ! str_contains($rendered, $rawPassword)
                && str_contains($rendered, 'For your safety, no password is included in this email.');
        });
    }

    public function test_staff_accounts_can_be_disabled_enabled_archived_and_restored(): void
    {
        Mail::fake();
        $staff = $this->createStaff('teacher', ['email' => 'lifecycle-teacher@example.test']);

        $this->actingAsSchoolAdmin();

        $this->post(route('school.staff.disable', $staff))
            ->assertRedirect(route('school.staff.index', ['status' => 'disabled']))
            ->assertSessionHas('success', __('ui.account_disabled_success'));

        $staff = $staff->fresh();
        $this->assertNotNull($staff->disabled_at);
        $this->assertFalse($staff->hasRole('teacher'));
        $this->assertSchoolRoleStatus($staff, 'teacher', 'inactive');

        $this->post(route('school.staff.enable', $staff))
            ->assertRedirect(route('school.staff.index', ['status' => 'disabled']))
            ->assertSessionHas('success', __('ui.account_enabled_success'));

        $staff = $staff->fresh();
        $this->assertNull($staff->disabled_at);
        $this->assertTrue($staff->hasRole('teacher'));
        $this->assertSchoolRoleStatus($staff, 'teacher', 'active');

        $this->post(route('school.staff.archive', $staff))
            ->assertRedirect(route('school.staff.index', ['status' => 'archived']))
            ->assertSessionHas('success', __('ui.account_archived_success'));

        $staff = $staff->fresh();
        $this->assertNotNull($staff->archived_at);
        $this->assertFalse($staff->hasRole('teacher'));
        $this->assertSchoolRoleStatus($staff, 'teacher', 'archived');

        $this->post(route('school.staff.restore', $staff))
            ->assertRedirect(route('school.staff.index', ['status' => 'archived']))
            ->assertSessionHas('success', __('ui.account_restored_success'));

        $staff = $staff->fresh();
        $this->assertNull($staff->disabled_at);
        $this->assertNull($staff->archived_at);
        $this->assertTrue($staff->hasRole('teacher'));
        $this->assertSchoolRoleStatus($staff, 'teacher', 'active');

        foreach ([
            UserAccountSetupNotificationService::ACCOUNT_DISABLED,
            UserAccountSetupNotificationService::ACCOUNT_ENABLED,
            UserAccountSetupNotificationService::ACCOUNT_ARCHIVED,
            UserAccountSetupNotificationService::ACCOUNT_RESTORED,
        ] as $eventKey) {
            $this->assertLifecycleNoticeSent($staff->email, $eventKey);
        }
    }

    public function test_delete_archives_staff_account_when_linked_records_exist(): void
    {
        Mail::fake();
        $staff = $this->createStaff('teacher', ['email' => 'linked-teacher@example.test']);

        AuditLog::create([
            'user_id' => $staff->id,
            'school_id' => $this->school->id,
            'action' => 'teacher_result_reviewed',
        ]);

        $this->actingAsSchoolAdmin();

        $this->delete(route('school.staff.destroy', $staff))
            ->assertRedirect(route('school.staff.index', ['status' => 'archived']))
            ->assertSessionHas('success', __('ui.account_delete_archived_instead'));

        $this->assertDatabaseHas('users', ['id' => $staff->id]);
        $this->assertNotNull($staff->fresh()->archived_at);
        $this->assertSchoolRoleStatus($staff->fresh(), 'teacher', 'archived');
        $this->assertLifecycleNoticeSent($staff->email, UserAccountSetupNotificationService::ACCOUNT_ARCHIVED);
    }

    public function test_last_active_school_admin_cannot_be_disabled(): void
    {
        Mail::fake();
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
        ]);

        $school = $this->createSchool('Only Admin Academy');
        $onlyAdmin = $this->createSchoolAdmin($school, ['email' => 'only-admin@example.test']);

        $this->actingAsSuperAdmin()
            ->from(route('admin.schools.admins.index', $school))
            ->post(route('admin.schools.admins.disable', [$school, $onlyAdmin]))
            ->assertRedirect(route('admin.schools.admins.index', $school))
            ->assertSessionHasErrors('user');

        $this->assertNull($onlyAdmin->fresh()->disabled_at);
        $this->assertSchoolRoleStatus($onlyAdmin->fresh(), 'school_admin', 'active', $school);
    }

    public function test_disabled_and_archived_accounts_cannot_authenticate(): void
    {
        $disabled = User::factory()->create([
            'email' => 'disabled-login@example.test',
            'password' => Hash::make('password'),
            'disabled_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $disabled->email,
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();

        $archived = User::factory()->create([
            'email' => 'archived-login@example.test',
            'password' => Hash::make('password'),
            'archived_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $archived->email,
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_password_views_render_translation_ready_copy(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee(__('ui.forgot_password_heading'))
            ->assertSee(__('ui.email_password_setup_link'));

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee(__('ui.reset_password_heading'))
            ->assertSee(__('ui.save_new_password'));
    }

    public function test_new_ui_translation_keys_exist_for_supported_locales(): void
    {
        $keys = [
            'account_created_setup_failed',
            'account_delete_archived_instead',
            'account_disabled_success',
            'account_enabled_success',
            'account_not_active',
            'account_restored_success',
            'email_password_setup_link',
            'forgot_password_heading',
            'last_active_admin_blocked',
            'reset_password_heading',
            'school_admin_created_setup_sent',
            'send_setup_link',
            'setup_email_notice',
            'setup_link_sent',
            'staff_created_setup_sent',
            'staff_identity_rule_body',
            'staff_password_help',
            'staff_require_password_reset',
            'status',
        ];

        foreach (['en', 'fr', 'ar', 'ha', 'yo'] as $locale) {
            $translations = require base_path("lang/{$locale}/ui.php");

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $translations, "Missing {$locale}.ui.{$key}");
            }
        }
    }

    private function createSchool(string $name): School
    {
        $this->schoolCounter++;

        return School::create([
            'name' => $name,
            'slug' => str($name)->slug().'-'.$this->schoolCounter,
            'email' => 'school'.$this->schoolCounter.'@example.test',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function configurePlatformMailer(): void
    {
        MailSetting::updateOrCreate(
            ['school_id' => null],
            [
                'mailer' => 'log',
                'from_address' => 'portal@example.test',
                'from_name' => 'Sanfaani Schools',
                'is_enabled' => true,
            ]
        );
    }

    private function createSchoolAdmin(School $school, array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'school_id' => $school->id,
            'email' => 'admin'.$school->id.'-'.uniqid().'@example.test',
        ], $overrides));

        $user->assignRole('school_admin');
        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        return $user;
    }

    private function createStaff(string $role, array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'school_id' => $this->school->id,
            'email' => $role.'-'.uniqid().'@example.test',
            'staff_code' => strtoupper($role).'-001',
            'must_change_password' => true,
        ], $overrides));

        $user->assignRole($role);
        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $this->school->id,
            'role_name' => $role,
            'status' => 'active',
            'assigned_by' => $this->schoolAdmin->id,
        ]);

        return $user;
    }

    private function actingAsSchoolAdmin(): void
    {
        $this->actingAs($this->schoolAdmin);

        session([
            'active_school_id' => $this->school->id,
            'active_role_context' => 'school_admin',
        ]);
    }

    private function actingAsSuperAdmin(): self
    {
        $this->actingAs($this->superAdmin);

        session(['active_role_context' => 'super_admin']);

        return $this;
    }

    private function assertSetupMailSent(string $email, string $eventKey): void
    {
        Mail::assertSent(StaffLifecycleMail::class, fn (StaffLifecycleMail $mail): bool => $mail->hasTo($email)
            && data_get($mail->mailMetadata, 'event_key') === $eventKey
            && str_starts_with((string) data_get($mail->mailMetadata, 'action_url'), 'https://portal.example.test/reset-password/')
            && data_get($mail->mailMetadata, 'action_label') === __('ui.set_password'));
    }

    private function assertLifecycleNoticeSent(string $email, string $eventKey): void
    {
        $this->assertSetupLog($email, $eventKey, CommunicationLog::STATUS_SENT);
    }

    private function assertSetupLog(string $email, string $eventKey, string $status): void
    {
        $log = CommunicationLog::where('recipient', $email)
            ->where('type', $eventKey)
            ->firstOrFail();

        $this->assertSame($status, $log->status, (string) $log->failure_reason);

        if ($status === CommunicationLog::STATUS_SENT) {
            $this->assertSame($eventKey, data_get($log->metadata, 'event_key'));

            if (in_array($eventKey, [
                UserAccountSetupNotificationService::ACCOUNT_CREATED_SETUP_LINK,
                UserAccountSetupNotificationService::ACCOUNT_SETUP_LINK_RESENT,
            ], true)) {
                $this->assertStringStartsWith('https://portal.example.test/reset-password/', data_get($log->metadata, 'action_url'));
            }
        }
    }

    private function assertSchoolRoleStatus(User $user, string $role, string $status, ?School $school = null): void
    {
        $school ??= $this->school;

        $this->assertDatabaseHas('user_school_roles', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => $status,
        ]);
    }
}
