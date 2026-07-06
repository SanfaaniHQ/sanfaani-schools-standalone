<?php

namespace App\Services\Portals;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class StudentPortalLinkService
{
    public function childrenForParent(User $parent, School $school): Collection
    {
        return $parent->children()
            ->wherePivot('school_id', $school->id)
            ->with([
                'schoolClass',
                'currentEnrollment.schoolClass',
            ])
            ->withCount([
                'results',
                'attendanceRecords',
                'feeInvoices',
                'reportCardSnapshots',
                'cbtAttempts',
            ])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function studentForUser(User $user, School $school): ?Student
    {
        $linked = $user->studentProfile()
            ->with([
                'schoolClass',
                'currentEnrollment.schoolClass',
            ])
            ->withCount([
                'results',
                'attendanceRecords',
                'feeInvoices',
                'reportCardSnapshots',
                'cbtAttempts',
            ])
            ->where('school_id', $school->id)
            ->first();

        if ($linked) {
            return $linked;
        }

        return $this->legacyStudentMatch($user, $school);
    }

    public function legacyChildrenForParent(User $parent, School $school): Collection
    {
        $email = strtolower(trim((string) $parent->email));

        if ($email === '') {
            return new Collection;
        }

        return Student::query()
            ->with([
                'schoolClass',
                'currentEnrollment.schoolClass',
            ])
            ->withCount([
                'results',
                'attendanceRecords',
                'feeInvoices',
                'reportCardSnapshots',
                'cbtAttempts',
            ])
            ->where('school_id', $school->id)
            ->whereNotNull('guardian_email')
            ->whereRaw('LOWER(TRIM(guardian_email)) = ?', [$email])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function attachParentToStudent(
        User $parent,
        Student $student,
        string $relationship = 'guardian',
        bool $isPrimary = false
    ): void {
        $parent->children()->syncWithoutDetaching([
            $student->id => [
                'school_id' => $student->school_id,
                'relationship' => $relationship,
                'is_primary' => $isPrimary,
                'can_view_results' => true,
                'can_view_attendance' => true,
                'can_view_finance' => true,
                'receives_notifications' => true,
            ],
        ]);
    }

    public function linkStudentUser(User $user, Student $student): void
    {
        $student->forceFill([
            'student_user_id' => $user->id,
        ])->save();
    }

    private function legacyStudentMatch(User $user, School $school): ?Student
    {
        $email = strtolower(trim((string) $user->email));
        $emailLocalPart = strtolower(Str::before($email, '@'));
        $name = strtolower(trim((string) $user->name));

        return Student::query()
            ->with([
                'schoolClass',
                'currentEnrollment.schoolClass',
            ])
            ->withCount([
                'results',
                'attendanceRecords',
                'feeInvoices',
                'reportCardSnapshots',
                'cbtAttempts',
            ])
            ->where('school_id', $school->id)
            ->where(function ($query) use ($email, $emailLocalPart, $name) {
                if ($email !== '') {
                    $query->whereRaw('LOWER(TRIM(admission_number)) = ?', [$email])
                        ->orWhereRaw('LOWER(TRIM(admission_number)) = ?', [$emailLocalPart]);
                }

                if ($name !== '') {
                    $query->orWhereRaw('LOWER(TRIM(first_name)) = ?', [$name])
                        ->orWhereRaw('LOWER(TRIM(last_name)) = ?', [$name]);
                }
            })
            ->orderByDesc('id')
            ->first();
    }
}
