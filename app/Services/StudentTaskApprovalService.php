<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\SessionTask;
use App\Models\SessionTaskStudent;
use App\Models\StudentTaskApprovalEvent;
use App\Models\StudentTaskApprovalSetting;
use App\Models\User;
use App\Support\LifecycleGate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentTaskApprovalService
{
    public const TRUSTED_REVIEW_DAYS = 7;

    public function __construct(
        private readonly TeacherStudentSubjectVisibilityService $teacherVisibility,
        private readonly RewardProgressionService $rewardProgression
    ) {}

    public function putToReview(User $actor, int $sessionTaskId, int $studentId): SessionTaskStudent
    {
        return DB::transaction(function () use ($actor, $sessionTaskId, $studentId): SessionTaskStudent {
            $this->assertStudentActor($actor, $studentId);

            $task = $this->resolveAccessibleTask($sessionTaskId, $studentId);
            $pivot = $this->lockOrCreatePivot($task->id, $studentId);

            if ($pivot->isCompleted() || $pivot->isInReviewLike()) {
                return $pivot;
            }

            if (! $this->isAssignedLike($pivot)) {
                throw ValidationException::withMessages([
                    'task' => 'This task cannot be submitted for review.',
                ]);
            }

            $now = now(config('app.timezone'));
            $trustedSetting = StudentTaskApprovalSetting::trustedEnabledFor($studentId);

            $pivot->status = SessionTaskStudent::STATUS_IN_REVIEW;
            $pivot->review_submitted_at = $now;
            $pivot->review_submitted_by_id = $actor->id;
            $pivot->review_submission_source = SessionTaskStudent::SOURCE_STUDENT_REVIEW;
            $pivot->trusted_auto_approval_snapshot = $trustedSetting !== null;
            $pivot->trusted_auto_approval_due_at = $trustedSetting
                ? CarbonImmutable::instance($now)->addDays(self::TRUSTED_REVIEW_DAYS)
                : null;
            $pivot->trusted_auto_approval_granted_by_id = $trustedSetting?->updated_by_user_id;
            $pivot->save();

            $this->writeEvent(
                $pivot,
                StudentTaskApprovalEvent::TYPE_SUBMITTED_FOR_REVIEW,
                $actor,
                SessionTaskStudent::SOURCE_STUDENT_REVIEW
            );

            return $pivot->fresh();
        }, 3);
    }

    public function completeWithStudentPin(
        User $actor,
        int $sessionTaskId,
        int $studentId,
        int $verifierUserId
    ): SessionTaskStudent {
        return DB::transaction(function () use ($actor, $sessionTaskId, $studentId, $verifierUserId): SessionTaskStudent {
            $this->assertStudentActor($actor, $studentId);

            $task = $this->resolveAccessibleTask($sessionTaskId, $studentId);
            $pivot = $this->lockOrCreatePivot($task->id, $studentId);

            return $this->finalize(
                pivot: $pivot,
                task: $task,
                actor: $actor,
                points: $this->defaultPoints($task),
                source: SessionTaskStudent::SOURCE_STUDENT_PIN,
                eventType: StudentTaskApprovalEvent::TYPE_COMPLETED_WITH_PIN,
                rewardGranterUserId: $verifierUserId,
                requireInReview: false
            );
        }, 3);
    }

    public function approveAsParent(User $actor, int $sessionTaskStudentId, int $effortPoints): SessionTaskStudent
    {
        return DB::transaction(function () use ($actor, $sessionTaskStudentId, $effortPoints): SessionTaskStudent {
            $pivot = $this->lockPivotById($sessionTaskStudentId);
            $task = $this->resolveAccessibleTask($pivot->session_task_id, $pivot->student_id);
            $this->assertParentActor($actor, $pivot->student_id);

            return $this->finalize(
                pivot: $pivot,
                task: $task,
                actor: $actor,
                points: $effortPoints,
                source: SessionTaskStudent::SOURCE_PARENT_APPROVAL,
                eventType: StudentTaskApprovalEvent::TYPE_APPROVED,
                rewardGranterUserId: $actor->id,
                requireInReview: true
            );
        }, 3);
    }

    public function completeAsParent(User $actor, int $sessionTaskId, int $studentId, int $effortPoints): SessionTaskStudent
    {
        return DB::transaction(function () use ($actor, $sessionTaskId, $studentId, $effortPoints): SessionTaskStudent {
            $this->assertParentActor($actor, $studentId);

            $task = $this->resolveAccessibleTask($sessionTaskId, $studentId);
            $pivot = $this->lockOrCreatePivot($task->id, $studentId);

            if (! $this->isAssignedLike($pivot) && ! $pivot->isCompleted()) {
                throw ValidationException::withMessages([
                    'task' => 'Only assigned tasks can be completed directly.',
                ]);
            }

            return $this->finalize(
                pivot: $pivot,
                task: $task,
                actor: $actor,
                points: $effortPoints,
                source: SessionTaskStudent::SOURCE_PARENT_DIRECT_COMPLETION,
                eventType: StudentTaskApprovalEvent::TYPE_COMPLETED_BY_PARENT,
                rewardGranterUserId: $actor->id,
                requireInReview: false
            );
        }, 3);
    }

    public function approveAsTeacher(User $actor, int $sessionTaskStudentId, int $effortPoints): SessionTaskStudent
    {
        return DB::transaction(function () use ($actor, $sessionTaskStudentId, $effortPoints): SessionTaskStudent {
            $pivot = $this->lockPivotById($sessionTaskStudentId);
            $task = $this->resolveAccessibleTask($pivot->session_task_id, $pivot->student_id);
            $this->assertTeacherActor($actor, $task, $pivot->student_id);

            return $this->finalize(
                pivot: $pivot,
                task: $task,
                actor: $actor,
                points: $effortPoints,
                source: SessionTaskStudent::SOURCE_TEACHER_APPROVAL,
                eventType: StudentTaskApprovalEvent::TYPE_APPROVED,
                rewardGranterUserId: $actor->id,
                requireInReview: true
            );
        }, 3);
    }

    /**
     * @return array{approved:int, skipped:int}
     */
    public function autoApproveTrustedChildTasks(?CarbonImmutable $dueBefore = null, int $limit = 100): array
    {
        $dueBefore ??= CarbonImmutable::now(config('app.timezone'));
        $approved = 0;
        $skipped = 0;

        $rows = SessionTaskStudent::query()
            ->where('status', SessionTaskStudent::STATUS_IN_REVIEW)
            ->where('trusted_auto_approval_snapshot', true)
            ->whereNotNull('trusted_auto_approval_due_at')
            ->where('trusted_auto_approval_due_at', '<=', $dueBefore)
            ->orderBy('trusted_auto_approval_due_at')
            ->limit($limit)
            ->get(['id']);

        foreach ($rows as $row) {
            DB::transaction(function () use ($row, &$approved, &$skipped): void {
                $pivot = $this->lockPivotById((int) $row->id);

                if (! $pivot->isInReviewLike() || ! $pivot->trusted_auto_approval_granted_by_id) {
                    $this->writeEvent(
                        $pivot,
                        StudentTaskApprovalEvent::TYPE_SKIPPED_STALE,
                        null,
                        SessionTaskStudent::SOURCE_TRUSTED_CHILD_AUTO,
                        null,
                        ['reason' => 'missing_granter_or_stale']
                    );
                    $skipped++;

                    return;
                }

                $task = $this->resolveAccessibleTask($pivot->session_task_id, $pivot->student_id);

                $this->finalize(
                    pivot: $pivot,
                    task: $task,
                    actor: null,
                    points: $this->defaultPoints($task),
                    source: SessionTaskStudent::SOURCE_TRUSTED_CHILD_AUTO,
                    eventType: StudentTaskApprovalEvent::TYPE_TRUSTED_AUTO_APPROVED,
                    rewardGranterUserId: (int) $pivot->trusted_auto_approval_granted_by_id,
                    requireInReview: true
                );

                $approved++;
            }, 3);
        }

        return ['approved' => $approved, 'skipped' => $skipped];
    }

    public function parentInReviewRows(User $actor, int $studentId): Collection
    {
        $this->assertParentActor($actor, $studentId);

        return $this->approvalRowsQuery()
            ->where('session_task_student.student_id', $studentId)
            ->get();
    }

    public function teacherInReviewRows(User $actor, int $studentId, int $subjectId): Collection
    {
        abort_unless($actor->hasRole('teacher'), 403);

        return $this->approvalRowsQuery()
            ->where('session_task_student.student_id', $studentId)
            ->where('class_sessions.subject_id', $subjectId)
            ->whereIn(
                'class_sessions.class_subject_id',
                $this->teacherVisibility->ownedClassSubjectIdsForApproval($actor->id, $subjectId)
            )
            ->get();
    }

    private function finalize(
        SessionTaskStudent $pivot,
        SessionTask $task,
        ?User $actor,
        int $points,
        string $source,
        string $eventType,
        int $rewardGranterUserId,
        bool $requireInReview
    ): SessionTaskStudent {
        if ($pivot->isCompleted()) {
            return $pivot;
        }

        if ($requireInReview && ! $pivot->isInReviewLike()) {
            throw ValidationException::withMessages([
                'task' => 'Only tasks in review can be approved.',
            ]);
        }

        $this->assertPointBounds($points, $task);

        $now = now(config('app.timezone'));
        $pivot->status = SessionTaskStudent::STATUS_COMPLETED;
        $pivot->student_points = $points;
        $pivot->submitted_at = $pivot->submitted_at ?: $now;
        $pivot->approved_at = $now;
        $pivot->approved_by_id = $actor?->id;
        $pivot->approval_source = $source;
        $pivot->flag = null;
        $pivot->save();

        if ($points > 0) {
            $this->applyRewardEffects($task, $pivot->student_id, $points, $rewardGranterUserId);
        }

        $this->writeEvent($pivot, $eventType, $actor, $source, $points);
        $this->advanceUpNextFlag($task, $pivot->student_id);

        return $pivot->fresh();
    }

    private function applyRewardEffects(SessionTask $task, int $studentId, int $points, int $granterUserId): void
    {
        $academicYearId = AcademicYear::currentId();
        $subjectId = (int) $task->classSession?->subject_id;

        if ($subjectId <= 0) {
            throw ValidationException::withMessages([
                'task' => 'This task is missing a subject context, so reward points cannot be applied.',
            ]);
        }

        $this->rewardProgression->applyPointDelta(
            studentId: $studentId,
            pointsDelta: $points,
            sourceType: 'task',
            sourceId: $task->id,
            grantedBy: $granterUserId,
            academicYearId: $academicYearId,
            subjectId: $subjectId
        );
    }

    private function advanceUpNextFlag(SessionTask $task, int $studentId): void
    {
        $taskIds = SessionTask::query()
            ->where('class_session_id', $task->class_session_id)
            ->orderBy('sort')
            ->pluck('id');

        SessionTaskStudent::query()
            ->whereIn('session_task_id', $taskIds)
            ->where('student_id', $studentId)
            ->where('flag', 'up-next')
            ->update(['flag' => null]);

        $nextTaskId = SessionTask::query()
            ->where('class_session_id', $task->class_session_id)
            ->where('sort', '>', $task->sort)
            ->orderBy('sort')
            ->pluck('id')
            ->first(function ($id) use ($studentId): bool {
                return ! SessionTaskStudent::query()
                    ->where('session_task_id', $id)
                    ->where('student_id', $studentId)
                    ->where('status', SessionTaskStudent::STATUS_COMPLETED)
                    ->exists();
            });

        if ($nextTaskId) {
            $nextPivot = SessionTaskStudent::query()->firstOrNew([
                'session_task_id' => $nextTaskId,
                'student_id' => $studentId,
            ]);

            $nextPivot->flag = 'up-next';

            if (! $nextPivot->exists || $this->isAssignedLike($nextPivot)) {
                $nextPivot->status = SessionTaskStudent::STATUS_ASSIGNED;
            }

            $nextPivot->save();
        }
    }

    private function writeEvent(
        SessionTaskStudent $pivot,
        string $eventType,
        ?User $actor,
        string $source,
        ?int $points = null,
        ?array $metadata = null
    ): void {
        StudentTaskApprovalEvent::create([
            'session_task_student_id' => $pivot->id,
            'session_task_id' => $pivot->session_task_id,
            'student_id' => $pivot->student_id,
            'event_type' => $eventType,
            'actor_user_id' => $actor?->id,
            'actor_role' => $actor ? $this->primaryRole($actor) : 'system',
            'source' => $source,
            'points' => $points,
            'metadata' => $metadata,
            'created_at' => now(config('app.timezone')),
        ]);
    }

    private function approvalRowsQuery()
    {
        return SessionTaskStudent::query()
            ->select('session_task_student.*')
            ->with(['task.classSession.subject'])
            ->join('session_tasks', 'session_tasks.id', '=', 'session_task_student.session_task_id')
            ->join('class_sessions', 'class_sessions.id', '=', 'session_tasks.class_session_id')
            ->whereIn('session_task_student.status', [
                SessionTaskStudent::STATUS_IN_REVIEW,
                SessionTaskStudent::STATUS_LEGACY_PENDING,
            ])
            ->orderBy('class_sessions.subject_id')
            ->orderBy('session_tasks.sort')
            ->orderBy('session_tasks.id');
    }

    private function resolveAccessibleTask(int $sessionTaskId, int $studentId): SessionTask
    {
        $task = SessionTask::query()
            ->with('classSession')
            ->whereKey($sessionTaskId)
            ->firstOrFail();

        $session = $task->classSession;
        abort_unless($session, 404);

        abort_if(LifecycleGate::inspect($studentId)->denied(), 403);

        abort_unless($session->student_id === null || (int) $session->student_id === $studentId, 403);

        $hasSubject = DB::table('students_subjects')
            ->where('student_id', $studentId)
            ->where('class_subject_id', $session->class_subject_id)
            ->where('status', 'active')
            ->exists();
        abort_unless($hasSubject, 403);

        return $task;
    }

    private function lockOrCreatePivot(int $sessionTaskId, int $studentId): SessionTaskStudent
    {
        $pivot = SessionTaskStudent::query()
            ->where('session_task_id', $sessionTaskId)
            ->where('student_id', $studentId)
            ->lockForUpdate()
            ->first();

        if ($pivot) {
            return $pivot;
        }

        return SessionTaskStudent::create([
            'session_task_id' => $sessionTaskId,
            'student_id' => $studentId,
            'status' => SessionTaskStudent::STATUS_ASSIGNED,
        ]);
    }

    private function lockPivotById(int $sessionTaskStudentId): SessionTaskStudent
    {
        return SessionTaskStudent::query()
            ->whereKey($sessionTaskStudentId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function assertPointBounds(int $points, SessionTask $task): void
    {
        $max = $this->maxPoints($task);

        if ($points < 0 || $points > $max) {
            throw ValidationException::withMessages([
                'points' => "Effort points must be between 0 and {$max}.",
            ]);
        }
    }

    private function defaultPoints(SessionTask $task): int
    {
        return max(0, min((int) ($task->default_points ?? 0), $this->maxPoints($task)));
    }

    private function maxPoints(SessionTask $task): int
    {
        return max(0, (int) ($task->max_points ?? $task->default_points ?? 0));
    }

    private function isAssignedLike(SessionTaskStudent $pivot): bool
    {
        return $pivot->status === null
            || $pivot->status === ''
            || $pivot->status === SessionTaskStudent::STATUS_ASSIGNED;
    }

    private function assertStudentActor(User $actor, int $studentId): void
    {
        abort_unless($actor->hasRole('student') && (int) $actor->student?->id === $studentId, 403);
    }

    private function assertParentActor(User $actor, int $studentId): void
    {
        abort_unless($actor->hasRole('parent'), 403);

        $parent = $actor->parent_user;
        abort_unless(
            $parent && $parent->students()->where('students.id', $studentId)->exists(),
            403
        );
    }

    private function assertTeacherActor(User $actor, SessionTask $task, int $studentId): void
    {
        abort_unless($actor->hasRole('teacher'), 403);

        $session = $task->classSession;
        abort_unless($session, 404);

        $allowed = $this->teacherVisibility->taskStudentIsVisibleForApproval(
            $actor->id,
            (int) $session->subject_id,
            $studentId,
            (int) $session->class_subject_id
        );

        abort_unless($allowed, 403);
    }

    private function primaryRole(User $actor): string
    {
        foreach (['student', 'parent', 'teacher', 'admin'] as $role) {
            if ($actor->hasRole($role)) {
                return $role;
            }
        }

        return 'user';
    }
}
