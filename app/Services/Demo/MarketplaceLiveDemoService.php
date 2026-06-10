<?php

namespace App\Services\Demo;

use App\Models\AcademicSession;
use App\Models\ClassSubjectAssignment;
use App\Models\DemoCredential;
use App\Models\DemoSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\Subject;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Standalone\StandaloneEditionService;
use App\Services\UserWorkspaceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

class MarketplaceLiveDemoService
{
    public function __construct(
        private StandaloneEditionService $standalone,
    ) {}

    public function enabled(): bool
    {
        if ($this->standalone->hidesDemoSurfaces() || $this->standalone->hidesMarketplaceSurfaces()) {
            return false;
        }

        return (bool) config('demo.marketplace.enabled', false);
    }

    public function autoLoginEnabled(): bool
    {
        return $this->enabled() && (bool) config('demo.marketplace.auto_login', false);
    }

    public function safeModeEnabled(): bool
    {
        return (bool) config('demo.marketplace.safe_mode', true);
    }

    public function resetHours(): int
    {
        return max(1, (int) config('demo.marketplace.reset_hours', 24));
    }

    public function accounts(bool $publicOnly = false): Collection
    {
        return collect(config('demo.marketplace.accounts', []))
            ->filter(fn (array $account): bool => ($account['enabled'] ?? true) !== false)
            ->when($publicOnly, fn (Collection $accounts): Collection => $accounts->filter(
                fn (array $account): bool => ($account['public'] ?? false) === true
            ))
            ->map(function (array $account, string $role): array {
                return array_merge($account, [
                    'role' => $role,
                    'assign_role' => $account['assign_role'] ?? $role,
                ]);
            })
            ->values();
    }

    public function publicAccounts(): Collection
    {
        return $this->accounts(publicOnly: true);
    }

    public function seed(): array
    {
        return DB::transaction(function (): array {
            $school = $this->seedSchool();
            $session = $this->seedSession($school);
            $academicContext = $this->seedAcademicContext($school);
            $users = $this->seedUsers($school, $session);

            if ($teacher = $users->get('teacher')) {
                $this->seedTeacherAssignments($teacher, $academicContext);
            }

            return [
                'school' => $school->fresh(),
                'session' => $session->fresh(['credentials.user']),
                'users' => $users,
                'credentials' => $session->fresh('credentials')->credentials,
            ];
        });
    }

    public function knownPublicDemoUser(string $role): ?User
    {
        if (! $this->autoLoginEnabled()) {
            return null;
        }

        $account = $this->accountForRole($role, publicOnly: true);

        if (! $account) {
            return null;
        }

        $user = User::where('email', $account['email'])->first();

        if (! $user || ! $this->userHasActiveMarketplaceCredential($user, $role)) {
            return null;
        }

        return $user;
    }

    public function selectWorkspace(User $user, string $role): void
    {
        $assignedRole = $this->accountForRole($role)['assign_role'] ?? $role;
        $key = $user->school_id ? "school:{$user->school_id}:{$assignedRole}" : null;

        if ($key && app(UserWorkspaceService::class)->selectByKey($user, $key)) {
            return;
        }

        app(UserWorkspaceService::class)->selectFirst($user);
    }

    public function school(): ?School
    {
        $slug = (string) config('demo.marketplace.school.slug', 'sanfaani-marketplace-demo');

        return School::where('slug', $slug)->first();
    }

    private function accountForRole(string $role, bool $publicOnly = false): ?array
    {
        return $this->accounts($publicOnly)
            ->firstWhere('role', $role);
    }

    private function seedSchool(): School
    {
        $schoolConfig = config('demo.marketplace.school', []);
        $slug = (string) ($schoolConfig['slug'] ?? 'sanfaani-marketplace-demo');
        $school = School::withTrashed()->where('slug', $slug)->first() ?: new School(['slug' => $slug]);

        if (method_exists($school, 'trashed') && $school->trashed()) {
            $school->restore();
        }

        $school->fill([
            'name' => $schoolConfig['name'] ?? '[DEMO] Sanfaani Marketplace Demo School',
            'school_code' => $schoolConfig['school_code'] ?? 'DEMO-MARKET',
            'email' => $schoolConfig['email'] ?? 'demo@sanfaani.net',
            'phone' => $schoolConfig['phone'] ?? null,
            'address' => $schoolConfig['address'] ?? null,
            'status' => 'active',
            'subscription_status' => 'demo',
            'default_language' => config('sanfaani.default_language', 'en'),
            'supports_rtl' => false,
        ]);
        $school->save();

        return $school;
    }

    private function seedSession(School $school): DemoSession
    {
        $session = DemoSession::where('school_id', $school->id)
            ->whereNull('demo_request_id')
            ->first() ?: new DemoSession([
                'school_id' => $school->id,
                'demo_request_id' => null,
            ]);

        $session->fill([
            'status' => DemoSession::STATUS_ACTIVE,
            'starts_at' => $session->starts_at ?: now(),
            'expires_at' => now()->addHours($this->resetHours()),
            'last_activity_at' => now(),
            'metadata' => array_merge($session->metadata ?? [], [
                'marketplace_live_demo' => true,
                'demo_school' => true,
                'safe_mode' => $this->safeModeEnabled(),
                'reset_hours' => $this->resetHours(),
            ]),
        ]);
        $session->save();

        return $session;
    }

    private function seedUsers(School $school, DemoSession $session): Collection
    {
        return $this->accounts()
            ->mapWithKeys(function (array $account) use ($school, $session): array {
                $role = (string) $account['role'];
                $assignedRole = (string) $account['assign_role'];
                Role::findOrCreate($assignedRole);

                $user = User::where('email', $account['email'])->first();

                if ($user && (int) $user->school_id !== (int) $school->id && ! $user->demoCredentials()->exists()) {
                    throw new RuntimeException("Refusing to convert existing non-demo user {$account['email']} into a public demo user.");
                }

                $user ??= new User(['email' => $account['email']]);
                $user->forceFill([
                    'school_id' => $school->id,
                    'staff_code' => $user->staff_code ?: 'MDEMO-'.Str::upper(Str::random(8)),
                    'name' => $account['label'].' Demo',
                    'password' => Hash::make((string) $account['password']),
                    'must_change_password' => false,
                    'email_verified_at' => now(),
                ]);
                $user->save();
                $user->assignRole($assignedRole);

                UserSchoolRole::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'school_id' => $school->id,
                        'role_name' => $assignedRole,
                    ],
                    [
                        'status' => 'active',
                        'metadata' => [
                            'marketplace_live_demo' => true,
                            'demo_session_id' => $session->id,
                            'demo_role_name' => $role,
                        ],
                    ]
                );

                DemoCredential::updateOrCreate(
                    [
                        'demo_session_id' => $session->id,
                        'role_name' => $role,
                    ],
                    [
                        'user_id' => $user->id,
                        'label' => $account['label'],
                        'email' => $account['email'],
                        'temporary_password_encrypted' => (string) $account['password'],
                        'password_viewed_at' => null,
                        'expires_at' => $session->expires_at,
                        'status' => DemoCredential::STATUS_ACTIVE,
                        'metadata' => [
                            'marketplace_live_demo' => true,
                            'assigned_role' => $assignedRole,
                            'public' => (bool) ($account['public'] ?? false),
                        ],
                    ]
                );

                return [$role => $user->fresh()];
            });
    }

    private function seedAcademicContext(School $school): array
    {
        $session = $this->upsert(AcademicSession::class, [
            'school_id' => $school->id,
            'name' => '2025/2026 Demo Session',
        ], [
            'starts_at' => now()->startOfYear(),
            'ends_at' => now()->endOfYear(),
            'is_active' => true,
            'status' => 'active',
        ]);

        $term = $this->upsert(Term::class, [
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term',
        ], [
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonths(2),
            'is_active' => true,
            'status' => 'active',
        ]);

        $classes = collect([
            ['name' => 'JSS 1 Demo', 'code' => 'MDEMO-JSS1'],
            ['name' => 'JSS 2 Demo', 'code' => 'MDEMO-JSS2'],
        ])->map(fn (array $class): SchoolClass => $this->upsert(SchoolClass::class, [
            'school_id' => $school->id,
            'code' => $class['code'],
        ], [
            'name' => $class['name'],
            'section' => 'A',
            'status' => 'active',
        ]));

        $subjects = collect([
            ['name' => 'Mathematics', 'code' => 'MDEMO-MTH'],
            ['name' => 'English Language', 'code' => 'MDEMO-ENG'],
        ])->map(fn (array $subject): Subject => $this->upsert(Subject::class, [
            'school_id' => $school->id,
            'code' => $subject['code'],
        ], [
            'name' => $subject['name'],
            'assignment_type' => 'core',
            'is_elective' => false,
            'status' => 'active',
        ]));

        foreach ($classes as $class) {
            foreach ($subjects as $subject) {
                $this->upsert(ClassSubjectAssignment::class, [
                    'school_id' => $school->id,
                    'school_class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'academic_session_id' => $session->id,
                    'term_id' => $term->id,
                ], [
                    'assignment_type' => 'core',
                    'is_elective' => false,
                    'is_required' => true,
                    'status' => 'active',
                    'metadata' => ['marketplace_live_demo' => true],
                ]);
            }
        }

        collect([
            ['admission_number' => 'MDEMO-001', 'first_name' => 'Amina', 'last_name' => 'Okafor', 'gender' => 'female', 'class' => $classes[0]],
            ['admission_number' => 'MDEMO-002', 'first_name' => 'David', 'last_name' => 'Bello', 'gender' => 'male', 'class' => $classes[0]],
            ['admission_number' => 'MDEMO-003', 'first_name' => 'Fatima', 'last_name' => 'Lawal', 'gender' => 'female', 'class' => $classes[1]],
        ])->each(function (array $student) use ($school, $session, $term): void {
            $record = $this->upsert(Student::class, [
                'school_id' => $school->id,
                'admission_number' => $student['admission_number'],
            ], [
                'school_class_id' => $student['class']->id,
                'first_name' => $student['first_name'],
                'last_name' => $student['last_name'],
                'gender' => $student['gender'],
                'guardian_name' => 'Demo Guardian',
                'guardian_phone' => '+234 800 000 0000',
                'guardian_email' => Str::lower($student['admission_number']).'@demo.sanfaani.net',
                'address' => 'Sample demo address',
                'status' => 'active',
            ]);

            $this->upsert(StudentClassEnrollment::class, [
                'school_id' => $school->id,
                'student_id' => $record->id,
                'academic_session_id' => $session->id,
            ], [
                'school_class_id' => $student['class']->id,
                'start_term_id' => $term->id,
                'status' => 'active',
                'enrolled_at' => now(),
                'metadata' => ['marketplace_live_demo' => true],
            ]);
        });

        return compact('school', 'session', 'term', 'classes', 'subjects');
    }

    private function seedTeacherAssignments(User $teacher, array $context): void
    {
        $class = $context['classes']->first();
        $subject = $context['subjects']->first();

        if (! $class || ! $subject) {
            return;
        }

        $this->upsert(TeacherClassAssignment::class, [
            'school_id' => $context['school']->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
        ], [
            'role_type' => 'class_teacher',
            'status' => 'active',
            'metadata' => ['marketplace_live_demo' => true],
        ]);

        $this->upsert(TeacherSubjectAssignment::class, [
            'school_id' => $context['school']->id,
            'teacher_user_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
        ], [
            'role_type' => 'subject_teacher',
            'status' => 'active',
            'metadata' => ['marketplace_live_demo' => true],
        ]);
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    private function upsert(string $model, array $attributes, array $values): Model
    {
        $record = $model::where($attributes)->first() ?: new $model($attributes);
        $record->fill($values);
        $record->save();

        return $record;
    }

    private function userHasActiveMarketplaceCredential(User $user, string $role): bool
    {
        return $user->demoCredentials()
            ->where('role_name', $role)
            ->where('status', DemoCredential::STATUS_ACTIVE)
            ->whereHas('demoSession', function ($query): void {
                $query->where('status', DemoSession::STATUS_ACTIVE)
                    ->where(function ($query): void {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            })
            ->exists();
    }
}
