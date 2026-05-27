<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\TeacherSubjectClass;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TeacherStudentSubjectVisibilityService
{
    public function activeVisibleStudentIdsSubquery(int $teacherId, int $subjectId): Builder
    {
        $ownedClassSubjectIds = $this->ownedClassSubjectIds($teacherId, $subjectId);

        $query = DB::table('students')
            ->select('students.id as student_id')
            ->where(function ($query): void {
                $query->whereNull('students.account_status')
                    ->orWhere('students.account_status', '')
                    ->orWhere('students.account_status', 'active');
            })
            ->whereExists(function ($query) use ($subjectId, $ownedClassSubjectIds): void {
                $query->selectRaw('1')
                    ->from('students_subjects')
                    ->join('grade_level_subjects', 'grade_level_subjects.id', '=', 'students_subjects.grade_level_subject_id')
                    ->whereColumn('students_subjects.student_id', 'students.id')
                    ->where('students_subjects.academic_year_id', AcademicYear::currentId())
                    ->where('grade_level_subjects.subject_id', $subjectId)
                    ->where('students_subjects.status', 'active')
                    ->whereIn('students_subjects.class_subject_id', $ownedClassSubjectIds);
            });

        if (empty($ownedClassSubjectIds)) {
            $query->whereRaw('1 = 0');
        }

        return $query->distinct();
    }

    public function studentIsVisible(int $teacherId, int $subjectId, int $studentId): bool
    {
        return DB::query()
            ->fromSub($this->activeVisibleStudentIdsSubquery($teacherId, $subjectId), 'visible_students')
            ->where('student_id', $studentId)
            ->exists();
    }

    public function resolveTeacherSubjectClassForStudent(
        int $teacherId,
        int $subjectId,
        int $studentId,
        int $classId
    ): ?TeacherSubjectClass {
        if (! $this->studentIsVisible($teacherId, $subjectId, $studentId)) {
            return null;
        }

        return TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->availableForTeacher()
            ->whereExists(function ($query) use ($studentId, $subjectId): void {
                $query->selectRaw('1')
                    ->from('students_subjects')
                    ->join('grade_level_subjects', 'grade_level_subjects.id', '=', 'students_subjects.grade_level_subject_id')
                    ->where('students_subjects.student_id', $studentId)
                    ->where('students_subjects.academic_year_id', AcademicYear::currentId())
                    ->where('students_subjects.status', 'active')
                    ->where('grade_level_subjects.subject_id', $subjectId)
                    ->whereColumn('students_subjects.class_subject_id', 'teacher_subject_classes.class_subject_id');
            })
            ->first();
    }

    /**
     * @return array<int, int>
     */
    public function ownedClassSubjectIdsForApproval(int $teacherId, int $subjectId): array
    {
        return $this->ownedClassSubjectIds($teacherId, $subjectId);
    }

    public function taskStudentIsVisibleForApproval(
        int $teacherId,
        int $subjectId,
        int $studentId,
        int $classSubjectId
    ): bool {
        if (! in_array($classSubjectId, $this->ownedClassSubjectIds($teacherId, $subjectId), true)) {
            return false;
        }

        return DB::table('students_subjects')
            ->join('grade_level_subjects', 'grade_level_subjects.id', '=', 'students_subjects.grade_level_subject_id')
            ->where('students_subjects.student_id', $studentId)
            ->where('students_subjects.academic_year_id', AcademicYear::currentId())
            ->where('students_subjects.status', 'active')
            ->where('students_subjects.class_subject_id', $classSubjectId)
            ->where('grade_level_subjects.subject_id', $subjectId)
            ->exists();
    }

    /**
     * @return array<int, int>
     */
    private function ownedClassSubjectIds(int $teacherId, int $subjectId): array
    {
        return TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->availableForTeacher()
            ->pluck('class_subject_id')
            ->filter()
            ->map(fn ($value): int => (int) $value)
            ->unique()
            ->values()
            ->all();
    }
}
