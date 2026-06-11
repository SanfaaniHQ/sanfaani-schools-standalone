<?php

namespace App\Services;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class SchoolAuthorizationService
{
    private const FEATURE_PLAN_KEYS = [
        'results.manual_entry' => 'manual_result_entry',
        'results.upload' => 'csv_result_upload',
        'results.publish' => 'result_publishing',
        'public.result_checker' => 'public_result_checker',
        'scratch_cards.manage' => 'scratch_cards',
        'report_cards.basic' => 'report_card_basic',
        'report_cards.customize' => 'report_card_customization',
        'report_cards.signatures' => 'report_card_signature',
        'report_cards.comments' => 'report_card_auto_comments',
        'student.bulk_upload' => 'student_bulk_upload',
        'support.manage' => 'support.access',
        'attendance.view' => 'attendance',
        'attendance.manage' => 'attendance',
        'cbt.manage' => 'cbt_exams',
        'cbt.question_bank' => 'cbt_question_bank',
        'cbt.mark_theory' => 'cbt_theory_marking',
        'cbt.publish_results' => 'cbt_results',
        'cbt.public_competition' => 'cbt_competition_mode',
        'cbt.certificates' => 'cbt_certificates',
        'pdf.snapshots' => 'pdf_snapshots',
    ];

    /** @var array<string, bool> */
    private array $decisionCache = [];

    /** @var array<string, bool|null> */
    private array $planDecisionCache = [];

    public function __construct(
        private CurrentSchoolService $currentSchool,
        private SchoolRoleFeatureService $roleFeatures,
        private SchoolFeatureAccessService $planFeatures,
        private TeacherAssignmentAccessService $teacherAssignments
    ) {}

    public function can(?User $user, ?School $school, string $featureKey): bool
    {
        if (! $user || ! $school) {
            return false;
        }

        $cacheKey = implode(':', [
            $user->getKey(),
            $school->getKey(),
            $this->roleContext($user) ?: 'none',
            $featureKey,
        ]);

        if (array_key_exists($cacheKey, $this->decisionCache)) {
            return $this->decisionCache[$cacheKey];
        }

        return $this->decisionCache[$cacheKey] = $this->resolve($user, $school, $featureKey);
    }

    public function canAny(?User $user, ?School $school, array|string $featureKeys): bool
    {
        foreach ((array) $featureKeys as $featureKey) {
            if ($this->can($user, $school, $featureKey)) {
                return true;
            }
        }

        return false;
    }

    public function authorize(?User $user, School $school, string $featureKey): void
    {
        if (! $this->can($user, $school, $featureKey)) {
            throw new AuthorizationException('This feature is not enabled for your current school role.');
        }
    }

    public function authorizeAny(?User $user, School $school, array|string $featureKeys): void
    {
        if (! $this->canAny($user, $school, $featureKeys)) {
            throw new AuthorizationException('This feature is not enabled for your current school role.');
        }
    }

    public function featuresForRole(School $school, ?User $user, ?string $roleName = null): array
    {
        $roleName ??= $user ? $this->roleContext($user) : null;

        if (! $roleName) {
            return [];
        }

        $features = $this->roleFeatures->getFeatures($school->id, $roleName);

        foreach ($features as $key => $feature) {
            $features[$key]['enabled'] = $this->can($user, $school, $key);
        }

        return $features;
    }

    public function canViewStudent(User $user, School $school, Student $student): bool
    {
        if ((int) $student->school_id !== (int) $school->id || ! $this->userBelongsToSchool($user, $school)) {
            return false;
        }

        if ($this->hasPlatformOverride($user) || $this->roleContext($user) === 'school_admin') {
            return true;
        }

        $role = $this->roleContext($user);

        if ($role === 'result_officer') {
            return $this->can($user, $school, 'students.view');
        }

        if ($role !== 'teacher' || ! $this->can($user, $school, 'students.view_assigned')) {
            return false;
        }

        return $this->teacherCanAccessStudent($user, $school, $student);
    }

    public function teacherVisibleClassIds(User $teacher, School $school): Collection
    {
        return $this->teacherAssignments->visibleClassIds($school, $teacher);
    }

    public function roleContext(?User $user = null): ?string
    {
        return $this->currentSchool->roleContext($user);
    }

    private function resolve(User $user, School $school, string $featureKey): bool
    {
        if ($featureKey === 'communication.logs.view') {
            return false;
        }

        if (! $this->userBelongsToSchool($user, $school)) {
            return false;
        }

        if ($this->schoolFeatureMustBeExplicitlyEnabled($featureKey)
            && ! $this->schoolFeatureIsExplicitlyEnabled($school, $featureKey)) {
            return false;
        }

        if ($this->schoolFeatureIsExplicitlyDisabled($school, $featureKey) && ! $this->hasPlatformOverride($user)) {
            return false;
        }

        if ($this->hasPlatformOverride($user)) {
            return true;
        }

        $role = $this->roleContext($user);

        if ($role === 'school_admin') {
            return true;
        }

        if (! $role || ! $this->roleFeatures->roleSupports($role, $featureKey)) {
            return false;
        }

        return $this->roleFeatures->enabled($school->id, $role, $featureKey);
    }

    private function schoolFeatureMustBeExplicitlyEnabled(string $featureKey): bool
    {
        return false;
    }

    private function schoolFeatureIsExplicitlyEnabled(School $school, string $featureKey): bool
    {
        foreach ($this->schoolFeatureKeys($featureKey) as $candidateKey) {
            $cacheKey = $school->getKey().':'.$candidateKey;

            if (! array_key_exists($cacheKey, $this->planDecisionCache)) {
                $this->planDecisionCache[$cacheKey] = $this->planFeatures->explicitAccess($school, $candidateKey);
            }

            if ($this->planDecisionCache[$cacheKey] === true) {
                return true;
            }
        }

        return false;
    }

    private function userBelongsToSchool(User $user, School $school): bool
    {
        if ($this->hasPlatformOverride($user)) {
            return true;
        }

        return (int) $user->school_id === (int) $school->id
            || $user->activeSchoolRoles()->where('school_id', $school->id)->exists();
    }

    private function hasPlatformOverride(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    private function schoolFeatureIsExplicitlyDisabled(School $school, string $featureKey): bool
    {
        foreach ($this->schoolFeatureKeys($featureKey) as $candidateKey) {
            $cacheKey = $school->getKey().':'.$candidateKey;

            if (! array_key_exists($cacheKey, $this->planDecisionCache)) {
                $this->planDecisionCache[$cacheKey] = $this->planFeatures->explicitAccess($school, $candidateKey);
            }

            if ($this->planDecisionCache[$cacheKey] === false) {
                return true;
            }

            if ($this->planDecisionCache[$cacheKey] === true) {
                return false;
            }
        }

        return false;
    }

    private function schoolFeatureKeys(string $featureKey): array
    {
        return array_values(array_unique(array_filter([
            $featureKey,
            self::FEATURE_PLAN_KEYS[$featureKey] ?? null,
        ])));
    }

    private function teacherCanAccessStudent(User $teacher, School $school, Student $student): bool
    {
        $classIds = $this->teacherVisibleClassIds($teacher, $school);

        if ($classIds->isEmpty()) {
            return false;
        }

        if ($student->school_class_id && $classIds->contains((int) $student->school_class_id)) {
            return true;
        }

        return $student->classEnrollments()
            ->where('school_id', $school->id)
            ->whereIn('school_class_id', $classIds)
            ->exists();
    }
}
