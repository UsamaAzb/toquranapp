<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentsSubject;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ParentBehaviorSubjectResolver
{
    public const ERROR_MESSAGE = 'The Well-Being behavior subject is not ready yet.';

    public function resolveForStudent(int $studentId, ?int $teacherSubjectClassId = null): ?TeacherSubjectClass
    {
        $student = Student::query()
            ->whereKey($studentId)
            ->firstOrFail(['id', 'current_class_id', 'grade_level_id']);

        $subjectIds = $this->wellBeingSubjectIdsForStudent($studentId);

        if ($subjectIds === []) {
            return null;
        }

        // Prefer the child's exact class/subject setup when it exists.
        $strictQuery = TeacherSubjectClass::query()
            ->whereIn('subject_id', $subjectIds)
            ->where('class_id', $student->current_class_id)
            ->where('grade_id', $student->grade_level_id)
            ->availableForTeacher()
            ->withActiveStudentSubject($studentId);

        if ($teacherSubjectClassId) {
            $specific = (clone $strictQuery)
                ->whereKey($teacherSubjectClassId)
                ->first();

            if ($specific) {
                return $specific;
            }
        }

        $strictMatch = $this->orderBySubjectPriority(clone $strictQuery, $subjectIds)
            ->first();

        if ($strictMatch) {
            return $strictMatch;
        }

        // Some older children are not enrolled in Well-Being, but class/grade
        // still gives analytics a better subject context than a global fallback.
        if ($student->current_class_id && $student->grade_level_id) {
            $classGradeQuery = TeacherSubjectClass::query()
                ->whereIn('subject_id', $subjectIds)
                ->where('class_id', $student->current_class_id)
                ->where('grade_id', $student->grade_level_id)
                ->availableForTeacher();

            if ($teacherSubjectClassId) {
                $specific = (clone $classGradeQuery)
                    ->whereKey($teacherSubjectClassId)
                    ->first();

                if ($specific) {
                    return $specific;
                }
            }

            $classGradeMatch = $this->orderBySubjectPriority(clone $classGradeQuery, $subjectIds)
                ->orderBy('id')
                ->first();

            if ($classGradeMatch) {
                return $classGradeMatch;
            }
        }

        // Well-Being is the universal parent-write surface; keep legacy data
        // usable, but log the analytics ambiguity for later roster cleanup.
        Log::warning('Parent Well-Being fallback used without class/grade match.', [
            'student_id' => $studentId,
            'current_class_id' => $student->current_class_id,
            'grade_level_id' => $student->grade_level_id,
            'configured_teacher_subject_class_id' => $teacherSubjectClassId,
            'subject_ids' => $subjectIds,
        ]);

        $fallbackQuery = TeacherSubjectClass::query()
            ->whereIn('subject_id', $subjectIds)
            ->availableForTeacher();

        return $this->orderBySubjectPriority($fallbackQuery, $subjectIds)
            ->orderBy('id')
            ->first();
    }

    /** @return array<int, int> */
    private function wellBeingSubjectIdsForStudent(int $studentId): array
    {
        $configuredSubjectId = (int) Config::get('toquran.parent_behavior_subject_id', 16);
        $subjectIds = [];

        if ($configuredSubjectId > 0 && $this->subjectLooksLikeWellBeing($configuredSubjectId)) {
            $subjectIds[] = $configuredSubjectId;
        }

        $query = StudentsSubject::query()
            ->from('students_subjects as ss')
            ->join('grade_level_subjects as gls', 'gls.id', '=', 'ss.grade_level_subject_id')
            ->join('subjects as subjects', 'subjects.id', '=', 'gls.subject_id')
            ->where('ss.student_id', $studentId)
            ->where('ss.status', 'active');

        if ($this->hasColumn('students_subjects', 'academic_year_id')) {
            $query->where('ss.academic_year_id', AcademicYear::currentId());
        }

        if ($this->hasColumn('subjects', 'active')) {
            $query->where(function ($query): void {
                $query->whereNull('subjects.active')
                    ->orWhere('subjects.active', true);
            });
        }

        if ($this->hasColumn('subjects', 'row_status')) {
            $query->where(function ($query): void {
                $query->whereNull('subjects.row_status')
                    ->orWhereIn('subjects.row_status', ['current', 'active']);
            });
        }

        $linkedWellBeingSubjectIds = $query
            ->get(['subjects.id', 'subjects.title'])
            ->filter(fn ($subject): bool => $this->titleLooksLikeWellBeing((string) $subject->title))
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($subjectIds, $linkedWellBeingSubjectIds)));
    }

    private function subjectLooksLikeWellBeing(int $subjectId): bool
    {
        $query = Subject::query()->whereKey($subjectId);

        if ($this->hasColumn('subjects', 'active')) {
            $query->where(function ($query): void {
                $query->whereNull('active')
                    ->orWhere('active', true);
            });
        }

        $columns = ['id', 'title'];
        if ($this->hasColumn('subjects', 'row_status')) {
            $columns[] = 'row_status';
        }

        $subject = $query->first($columns);

        if (! $subject) {
            return false;
        }

        if (isset($subject->row_status) && $subject->row_status && ! in_array($subject->row_status, ['current', 'active'], true)) {
            return false;
        }

        return $this->titleLooksLikeWellBeing((string) $subject->title);
    }

    private function titleLooksLikeWellBeing(string $title): bool
    {
        $normalizedTitle = preg_replace('/[^a-z]+/', '', strtolower($title)) ?? '';

        return str_contains($normalizedTitle, 'wellbeing');
    }

    private function orderBySubjectPriority($query, array $subjectIds)
    {
        $caseClauses = collect(array_values($subjectIds))
            ->map(fn (int $subjectId, int $index): string => 'WHEN '.(int) $subjectId.' THEN '.$index)
            ->implode(' ');

        return $query->orderByRaw('CASE subject_id '.$caseClauses.' ELSE 999 END');
    }

    private function hasColumn(string $table, string $column): bool
    {
        static $cache = [];

        $key = $table.'.'.$column;
        $cache[$key] ??= Schema::hasColumn($table, $column);

        return $cache[$key];
    }
}
