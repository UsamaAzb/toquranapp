<?php

namespace App\Services;

use App\Models\SeriesTask;
use App\Models\SeriesTaskStudentAssignment;
use App\Models\SeriesTaskStudentAssignmentHistory;
use App\Models\SeriesTaskStudentGenerationState;
use App\Models\SeriesTaskVersion;
use App\Models\Student;
use App\Models\TeacherSubjectClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SeriesTaskAssignmentService
{
    public function __construct(
        private readonly TeacherStudentSubjectVisibilityService $studentVisibility,
    ) {}

    public function assign(
        int $studentId,
        int $taskId,
        int $versionId,
        int $startSequencePosition,
        int $actorUserId,
        int $subjectId
    ): void {
        $this->writeAssignment($studentId, $taskId, $versionId, $startSequencePosition, $actorUserId, $subjectId);
    }

    public function unassign(int $studentId, int $taskId, int $actorUserId): void
    {
        DB::transaction(function () use ($studentId, $taskId, $actorUserId): void {
            $task = SeriesTask::query()
                ->whereKey($taskId)
                ->where('created_by_user_id', $actorUserId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertTeacherCanAccessSubject((int) $task->subject_id, $actorUserId);

            $effectiveFrom = $this->resolveEffectiveChangeDate($studentId, $taskId);
            $closeDate = $effectiveFrom->copy()->subDay();
            $currentAssignments = SeriesTaskStudentAssignment::query()
                ->forStudent($studentId)
                ->forTask($taskId)
                ->openEnded()
                ->with('version')
                ->lockForUpdate()
                ->get();

            if ($currentAssignments->isEmpty()) {
                return;
            }

            foreach ($currentAssignments as $current) {
                if ($current->effective_from_date->gt($closeDate)) {
                    $current->delete();
                } else {
                    $current->update(['effective_to_date' => $closeDate->toDateString()]);
                }

                $this->recordHistory($studentId, $taskId, 'unassign', $current->version, null, $actorUserId);
            }

            $this->deactivateGenerationState($studentId, $taskId, $closeDate);
        });
    }

    public function bulkSave(
        int $taskId,
        int $versionId,
        array $selectedStudentIds,
        array $startPositionsByStudent,
        int $actorUserId,
        int $subjectId,
        ?array $managedStudentIds = null
    ): void {
        $selectedStudentIds = array_values(array_unique(array_map('intval', $selectedStudentIds)));
        $managedStudentIds = $managedStudentIds === null
            ? null
            : array_values(array_unique(array_map('intval', $managedStudentIds)));

        DB::transaction(function () use ($taskId, $versionId, $selectedStudentIds, $startPositionsByStudent, $actorUserId, $subjectId, $managedStudentIds): void {
            $task = $this->lockTaskForTeacher($taskId, $actorUserId, $subjectId);
            $version = $this->lockVersionForTask($versionId, $taskId);
            $this->assertTaskAssignable($task);
            $this->assertVersionAssignable($version);

            $currentlyAssignedToVersion = SeriesTaskStudentAssignment::query()
                ->forTask($taskId)
                ->where('version_id', $versionId)
                ->openEnded()
                ->lockForUpdate()
                ->pluck('student_id')
                ->map(fn ($value): int => (int) $value)
                ->all();
            $studentsManagedForRemoval = $managedStudentIds ?? $currentlyAssignedToVersion;

            foreach (array_intersect(array_diff($currentlyAssignedToVersion, $selectedStudentIds), $studentsManagedForRemoval) as $studentId) {
                $this->unassign($studentId, $taskId, $actorUserId);
            }

            foreach ($selectedStudentIds as $studentId) {
                $this->writeAssignment(
                    $studentId,
                    $taskId,
                    $versionId,
                    max(1, (int) ($startPositionsByStudent[$studentId] ?? 1)),
                    $actorUserId,
                    $subjectId
                );
            }
        });
    }

    public function resolveEffectiveAssignment(
        int $studentId,
        int $taskId,
        Carbon $date,
        bool $lockForUpdate = false
    ): ?SeriesTaskStudentAssignment {
        $query = SeriesTaskStudentAssignment::query()
            ->forStudent($studentId)
            ->forTask($taskId)
            ->effectiveOn($date)
            ->with(['task', 'version'])
            ->orderByDesc('effective_from_date')
            ->orderByDesc('id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function writeAssignment(
        int $studentId,
        int $taskId,
        int $versionId,
        int $startSequencePosition,
        int $actorUserId,
        int $subjectId
    ): void {
        DB::transaction(function () use ($studentId, $taskId, $versionId, $startSequencePosition, $actorUserId, $subjectId): void {
            $task = $this->lockTaskForTeacher($taskId, $actorUserId, $subjectId);
            $version = $this->lockVersionForTask($versionId, $taskId);

            $this->assertStudentEligible($studentId, (int) $task->subject_id, $actorUserId);
            $this->assertTaskAssignable($task);
            $this->assertVersionAssignable($version);
            $this->assertSequencePositionExists($version, $startSequencePosition);

            $effectiveFrom = $this->resolveEffectiveChangeDate($studentId, $taskId);
            $closeDate = $effectiveFrom->copy()->subDay();
            $current = $this->lockCurrentAssignment($studentId, $taskId);

            if (
                $current
                && (int) $current->version_id === $versionId
                && (int) $current->start_sequence_position === $startSequencePosition
                && $current->isOpenEnded()
            ) {
                $this->reactivateGenerationState($studentId, $taskId, $versionId, $startSequencePosition, $current->effective_from_date);

                return;
            }

            if ($current) {
                if ($current->effective_from_date->gt($closeDate)) {
                    $current->delete();
                } else {
                    $current->update(['effective_to_date' => $closeDate->toDateString()]);
                }
            }

            $assignment = SeriesTaskStudentAssignment::create([
                'student_id' => $studentId,
                'series_task_id' => $taskId,
                'version_id' => $versionId,
                'start_sequence_position' => $startSequencePosition,
                'effective_from_date' => $effectiveFrom->toDateString(),
                'effective_to_date' => null,
                'assigned_by_user_id' => $actorUserId,
            ]);

            $this->reactivateGenerationState($studentId, $taskId, $versionId, $startSequencePosition, $effectiveFrom);
            $this->recordHistory(
                $studentId,
                $taskId,
                $current ? 'move' : 'assign',
                $current?->version,
                $assignment->version,
                $actorUserId,
                $current?->start_sequence_position,
                $startSequencePosition
            );
        });
    }

    private function lockTaskForTeacher(int $taskId, int $actorUserId, int $subjectId): SeriesTask
    {
        $task = SeriesTask::query()
            ->whereKey($taskId)
            ->where('created_by_user_id', $actorUserId)
            ->where('subject_id', $subjectId)
            ->with(['versions.items'])
            ->lockForUpdate()
            ->firstOrFail();

        $this->assertTeacherCanAccessSubject($subjectId, $actorUserId);

        return $task;
    }

    private function lockVersionForTask(int $versionId, int $taskId): SeriesTaskVersion
    {
        return SeriesTaskVersion::query()
            ->whereKey($versionId)
            ->where('series_task_id', $taskId)
            ->with('items')
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function assertTeacherCanAccessSubject(int $subjectId, int $teacherId): void
    {
        $hasSubject = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->availableForTeacher()
            ->exists();

        if (! $hasSubject) {
            throw new RuntimeException('You do not have access to this subject.');
        }
    }

    private function assertTaskAssignable(SeriesTask $task): void
    {
        if ($task->isArchived()) {
            throw new RuntimeException('Archived Series Tasks cannot be assigned.');
        }

        if ($task->activeVersionsWithItemsCount() < 1) {
            throw new RuntimeException('At least one version with an active Library item is required before assigning students.');
        }
    }

    private function assertVersionAssignable(SeriesTaskVersion $version): void
    {
        if ($version->activeItemsCount() < 1) {
            throw new RuntimeException('This version needs at least one active Library item before it can be assigned.');
        }
    }

    private function assertSequencePositionExists(SeriesTaskVersion $version, int $position): void
    {
        $exists = $version->items()
            ->where('sequence_position', $position)
            ->active()
            ->exists();

        if (! $exists) {
            throw new RuntimeException('Choose an active starting item in this Series version.');
        }
    }

    private function assertStudentEligible(int $studentId, int $subjectId, int $teacherId): void
    {
        $student = Student::query()
            ->whereKey($studentId)
            ->where(function ($query): void {
                $query->whereNull('account_status')
                    ->orWhere('account_status', '')
                    ->orWhere('account_status', 'active');
            })
            ->first();

        if (! $student) {
            throw new RuntimeException('This student is not available for assignment.');
        }

        if (! $this->studentVisibility->studentIsVisible($teacherId, $subjectId, $studentId)) {
            throw new RuntimeException('This student is not available in the selected subject.');
        }
    }

    private function lockCurrentAssignment(int $studentId, int $taskId): ?SeriesTaskStudentAssignment
    {
        return SeriesTaskStudentAssignment::query()
            ->forStudent($studentId)
            ->forTask($taskId)
            ->openEnded()
            ->with('version')
            ->lockForUpdate()
            ->first();
    }

    private function resolveEffectiveChangeDate(int $studentId, int $taskId): Carbon
    {
        $today = now(config('app.timezone', 'Africa/Cairo'))->startOfDay();

        $deliveredToday = DB::table('class_sessions')
            ->where('student_id', $studentId)
            ->where('series_task_id', $taskId)
            ->whereDate('generated_for_date', $today->toDateString())
            ->exists();

        return $deliveredToday ? $today->copy()->addDay() : $today;
    }

    private function reactivateGenerationState(
        int $studentId,
        int $taskId,
        int $versionId,
        int $nextSequencePosition,
        Carbon $effectiveFrom
    ): void {
        $state = SeriesTaskStudentGenerationState::query()
            ->forStudent($studentId)
            ->forTask($taskId)
            ->lockForUpdate()
            ->first();

        if ($state) {
            $state->update([
                'current_version_id' => $versionId,
                'is_active' => 1,
                'start_date' => $effectiveFrom->toDateString(),
                'end_date' => null,
                'next_sequence_position' => $nextSequencePosition,
                'completed_at' => null,
            ]);

            return;
        }

        SeriesTaskStudentGenerationState::create([
            'student_id' => $studentId,
            'series_task_id' => $taskId,
            'current_version_id' => $versionId,
            'is_active' => 1,
            'start_date' => $effectiveFrom->toDateString(),
            'end_date' => null,
            'next_sequence_position' => $nextSequencePosition,
            'last_delivered_sequence_position' => null,
            'last_generated_date' => null,
            'paused_through_date' => null,
            'completed_at' => null,
        ]);
    }

    private function deactivateGenerationState(int $studentId, int $taskId, Carbon $endDate): void
    {
        $state = SeriesTaskStudentGenerationState::query()
            ->forStudent($studentId)
            ->forTask($taskId)
            ->lockForUpdate()
            ->first();

        if (! $state) {
            return;
        }

        $state->update([
            'is_active' => 0,
            'end_date' => $endDate->toDateString(),
        ]);
    }

    private function recordHistory(
        int $studentId,
        int $taskId,
        string $eventType,
        ?SeriesTaskVersion $fromVersion,
        ?SeriesTaskVersion $toVersion,
        int $actorUserId,
        ?int $fromSequencePosition = null,
        ?int $toSequencePosition = null
    ): void {
        SeriesTaskStudentAssignmentHistory::create([
            'student_id' => $studentId,
            'series_task_id' => $taskId,
            'event_type' => $eventType,
            'from_version_id' => $fromVersion?->id,
            'from_version_display_name' => $fromVersion?->display_name,
            'to_version_id' => $toVersion?->id,
            'to_version_display_name' => $toVersion?->display_name,
            'from_sequence_position' => $fromSequencePosition,
            'to_sequence_position' => $toSequencePosition,
            'actor_user_id' => $actorUserId,
        ]);
    }
}
