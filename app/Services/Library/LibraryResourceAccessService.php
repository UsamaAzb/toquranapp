<?php

namespace App\Services\Library;

use App\Models\AttachmentFile;
use App\Models\LibraryResource;
use App\Models\LibrarySection;
use App\Models\SessionTask;
use App\Models\Student;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class LibraryResourceAccessService
{
    public function canUseSubject(User $teacher, int $subjectId): bool
    {
        if (! $teacher->hasRole('teacher')) {
            return false;
        }

        return TeacherSubjectClass::query()
            ->availableForTeacher()
            ->where('user_teacher_coteacher_id', $teacher->id)
            ->where('subject_id', $subjectId)
            ->exists();
    }

    public function canManageSection(User $teacher, LibrarySection $section): bool
    {
        return (int) $section->owner_user_id === (int) $teacher->id
            && $this->canUseSubject($teacher, (int) $section->subject_id);
    }

    public function canManageResource(User $teacher, LibraryResource $resource): bool
    {
        return (int) $resource->owner_user_id === (int) $teacher->id
            && $this->canUseSubject($teacher, (int) $resource->subject_id);
    }

    public function authorizeSubject(User $teacher, int $subjectId): void
    {
        if (! $this->canUseSubject($teacher, $subjectId)) {
            throw new AuthorizationException('You cannot manage Library resources for this subject.');
        }
    }

    public function authorizeSection(User $teacher, LibrarySection $section): void
    {
        if (! $this->canManageSection($teacher, $section)) {
            throw new AuthorizationException('You cannot manage this Library section.');
        }
    }

    public function authorizeResource(User $teacher, LibraryResource $resource): void
    {
        if (! $this->canManageResource($teacher, $resource)) {
            throw new AuthorizationException('You cannot manage this Library resource.');
        }
    }

    public function attachmentBelongsToSession(AttachmentFile $attachment, int $sessionId): bool
    {
        $attachment->loadMissing('task.classSession');

        return $attachment->task
            && $attachment->task->classSession
            && (int) $attachment->task->class_session_id === $sessionId;
    }

    public function canLearnerAccessAttachment(
        User $user,
        int $studentId,
        int $sessionId,
        AttachmentFile $attachment
    ): bool {
        if (! $this->attachmentBelongsToSession($attachment, $sessionId)) {
            return false;
        }

        if (! $this->userCanActForStudent($user, $studentId)) {
            return false;
        }

        $task = $attachment->task;

        if ($this->taskIsAssignedToAll($task->assign_to_all) || $this->taskIsLegacySharedNormalSession($task)) {
            return true;
        }

        return $task->taskStudents()
            ->where('student_id', $studentId)
            ->exists();
    }

    public function canTeacherAccessAttachment(
        User $user,
        int $studentId,
        int $sessionId,
        AttachmentFile $attachment
    ): bool {
        if (! $user->hasRole('teacher')) {
            return false;
        }

        if (! $this->attachmentBelongsToSession($attachment, $sessionId)) {
            return false;
        }

        $session = $attachment->task?->classSession;

        if (! $session) {
            return false;
        }

        $teacherCanSeeStudent = TeacherSubjectClass::query()
            ->whereKey($session->teacher_subject_classes_id)
            ->where('user_teacher_coteacher_id', $user->id)
            ->availableForTeacher()
            ->whereHas('classSubject.studentsSubjects', fn ($query) => $query
                ->where('student_id', $studentId)
                ->where('status', 'active')
                ->whereHas('student', fn ($studentQuery) => $studentQuery->visibleToTeacher()))
            ->exists();

        if (! $teacherCanSeeStudent) {
            return false;
        }

        if ((int) $session->class_subject_id > 0) {
            $hasEnrollment = StudentsSubject::query()
                ->where('student_id', $studentId)
                ->where('class_subject_id', $session->class_subject_id)
                ->where('status', 'active')
                ->exists();

            if (! $hasEnrollment) {
                return false;
            }
        }

        $task = $attachment->task;

        if ($this->taskIsAssignedToAll($task->assign_to_all) || $this->taskIsLegacySharedNormalSession($task)) {
            return true;
        }

        return $task->taskStudents()
            ->where('student_id', $studentId)
            ->exists();
    }

    private function taskIsAssignedToAll(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'all', 'yes'], true);
    }

    private function taskIsLegacySharedNormalSession(SessionTask $task): bool
    {
        if (! blank($task->assign_to_all)) {
            return false;
        }

        $session = $task->classSession;

        if (! $session) {
            return false;
        }

        return blank($session->student_id)
            && blank($session->main_daily_session_template_id)
            && blank($session->differentiated_task_id)
            && blank($session->series_task_id);
    }

    public function userCanActForStudent(User $user, int $studentId): bool
    {
        $student = Student::query()
            ->with('parent:id,user_id')
            ->select(['id', 'user_id', 'parent_id'])
            ->find($studentId);

        if (! $student) {
            return false;
        }

        if ($user->hasRole('student')) {
            return (int) $student->user_id === (int) $user->id;
        }

        if ($user->hasRole('parent')) {
            return (int) $student->parent?->user_id === (int) $user->id;
        }

        return false;
    }
}
