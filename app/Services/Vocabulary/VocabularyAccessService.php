<?php

namespace App\Services\Vocabulary;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Models\VocabularySet;
use App\Models\VocabularySourceAccess;
use Illuminate\Support\Collection;

class VocabularyAccessService
{
    public function currentClassForStudent(Student $student): ?ClassModel
    {
        $student->loadMissing('currentClass');

        return $student->currentClass;
    }

    public function teacherCanUseClassContext(User $teacher, int $classId, ?int $teacherSubjectClassId = null): bool
    {
        if (! $teacher->hasRole('teacher')) {
            return $teacher->hasAnyRole(['admin', 'super_admin', 'owner']);
        }

        $query = TeacherSubjectClass::query()
            ->availableForTeacher()
            ->where('user_teacher_coteacher_id', $teacher->id)
            ->where('class_id', $classId);

        if ($teacherSubjectClassId !== null) {
            $query->where('id', $teacherSubjectClassId);
        }

        return $query->exists();
    }

    public function hasEnabledAccess(Student $student, VocabularySet $set): bool
    {
        if (! $set->canBeLaunched()) {
            return false;
        }

        $class = $this->currentClassForStudent($student);
        $audiences = [[VocabularySourceAccess::AUDIENCE_STUDENT, (int) $student->id]];

        if ($class) {
            $audiences[] = [VocabularySourceAccess::AUDIENCE_CLASS, (int) $class->id];
        }

        return $this->accessDecisionForSetAndAncestors($set, $audiences) === VocabularySourceAccess::STATUS_ENABLED;
    }

    /**
     * @return Collection<int, VocabularySet>
     */
    public function visiblePlayableSetsForStudent(Student $student): Collection
    {
        return VocabularySet::query()
            ->playable()
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->with('parent.parent.parent')
            ->orderBy('title')
            ->get()
            ->filter(fn (VocabularySet $set): bool => $this->hasEnabledAccess($student, $set))
            ->values();
    }

    public function canUserActForStudent(User $user, Student $student): bool
    {
        if ($user->hasRole('student')) {
            return (int) $student->user_id === (int) $user->id;
        }

        if ($user->hasRole('parent')) {
            $student->loadMissing('parent');

            return (int) $student->parent?->user_id === (int) $user->id;
        }

        return $user->hasAnyRole(['admin', 'super_admin', 'owner']);
    }

    /**
     * @param  array<int, array{0:string, 1:int}>  $audiences
     */
    private function accessDecisionForSetAndAncestors(VocabularySet $set, array $audiences): ?string
    {
        $current = $set;

        while ($current instanceof VocabularySet) {
            $decision = $this->accessDecisionForSet($audiences, (int) $current->id);

            if ($decision !== null) {
                return $decision;
            }

            $current = $current->parent;
        }

        return null;
    }

    /**
     * @param  array<int, array{0:string, 1:int}>  $audiences
     */
    private function accessDecisionForSet(array $audiences, int $setId): ?string
    {
        foreach ($audiences as [$audienceType, $audienceId]) {
            $status = VocabularySourceAccess::query()
                ->where('vocabulary_set_id', $setId)
                ->forAudience($audienceType, $audienceId)
                ->value('status');

            if ($status !== null) {
                return (string) $status;
            }
        }

        return null;
    }
}
