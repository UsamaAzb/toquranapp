<?php

namespace App\Services;

use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskStudentAssignment;
use App\Models\DifferentiatedTaskStudentAssignmentHistory;
use App\Models\DifferentiatedTaskStudentGenerationState;
use App\Models\DifferentiatedTaskVersion;
use App\Models\Student;
use App\Models\TeacherSubjectClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DifferentiatedTaskAssignmentService
{
    public function __construct(
        private readonly TeacherStudentSubjectVisibilityService $studentVisibility,
    ) {}

    public function assign(int $studentId, int $taskId, int $versionId, int $actorUserId, int $subjectId): void
    {
        $this->writeAssignment($studentId, $taskId, $versionId, $actorUserId, $subjectId);
    }

    public function unassign(int $studentId, int $taskId, int $actorUserId): void
    {
        DB::transaction(function () use ($studentId, $taskId, $actorUserId): void {
            $task = DifferentiatedTask::query()
                ->whereKey($taskId)
                ->where('created_by_user_id', $actorUserId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertTeacherCanAccessSubject((int) $task->subject_id, $actorUserId);

            $effectiveFrom = $this->resolveEffectiveChangeDate($studentId, $taskId);
            $closeDate = $effectiveFrom->copy()->subDay();
            $currentAssignments = DifferentiatedTaskStudentAssignment::query()
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

    public function moveToVersion(int $studentId, int $taskId, int $newVersionId, int $actorUserId, int $subjectId): void
    {
        $this->writeAssignment($studentId, $taskId, $newVersionId, $actorUserId, $subjectId);
    }

    /**
     * Applies version-scoped modal semantics:
     * selected students move/stay on this version, currently assigned students
     * to this version but omitted from the selected set become unassigned.
     */
    public function bulkSave(
        int $taskId,
        int $versionId,
        array $selectedStudentIds,
        int $actorUserId,
        int $subjectId,
        ?array $managedStudentIds = null
    ): void {
        $selectedStudentIds = array_values(array_unique(array_map('intval', $selectedStudentIds)));
        $managedStudentIds = $managedStudentIds === null
            ? null
            : array_values(array_unique(array_map('intval', $managedStudentIds)));

        DB::transaction(function () use ($taskId, $versionId, $selectedStudentIds, $actorUserId, $subjectId, $managedStudentIds): void {
            $task = $this->lockTaskForTeacher($taskId, $actorUserId, $subjectId);
            $version = $this->lockVersionForTask($versionId, $taskId);
            $this->assertTaskAssignable($task);
            $this->assertVersionAssignable($version);

            $currentlyAssignedToVersion = DifferentiatedTaskStudentAssignment::query()
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
                $this->writeAssignment($studentId, $taskId, $versionId, $actorUserId, $subjectId);
            }
        });
    }

    public function deleteVersion(int $versionId, int $actorUserId, Carbon $effectiveDate): void
    {
        DB::transaction(function () use ($versionId, $actorUserId, $effectiveDate): void {
            $version = DifferentiatedTaskVersion::query()
                ->whereKey($versionId)
                ->with('task')
                ->lockForUpdate()
                ->firstOrFail();

            $task = $version->task;

            if (! $task || (int) $task->created_by_user_id !== $actorUserId) {
                throw new RuntimeException('You do not have access to this Differentiated Task version.');
            }

            $assignments = DifferentiatedTaskStudentAssignment::query()
                ->forTask((int) $task->id)
                ->where('version_id', $versionId)
                ->openEnded()
                ->with('version')
                ->lockForUpdate()
                ->get();

            $closeDate = $effectiveDate->copy()->startOfDay()->subDay();

            foreach ($assignments as $assignment) {
                if ($assignment->effective_from_date->gt($closeDate)) {
                    $assignment->delete();
                } else {
                    $assignment->update(['effective_to_date' => $closeDate->toDateString()]);
                }

                $this->deactivateGenerationState((int) $assignment->student_id, (int) $task->id, $closeDate);
                $this->recordHistory(
                    (int) $assignment->student_id,
                    (int) $task->id,
                    'version_deleted',
                    $version,
                    null,
                    $actorUserId
                );
            }
        });
    }

    public function resolveEffectiveAssignment(
        int $studentId,
        int $taskId,
        Carbon $date,
        bool $lockForUpdate = false
    ): ?DifferentiatedTaskStudentAssignment {
        $query = DifferentiatedTaskStudentAssignment::query()
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
        int $actorUserId,
        int $subjectId
    ): void {
        DB::transaction(function () use ($studentId, $taskId, $versionId, $actorUserId, $subjectId): void {
            $task = $this->lockTaskForTeacher($taskId, $actorUserId, $subjectId);
            $version = $this->lockVersionForTask($versionId, $taskId);

            $this->assertStudentEligible($studentId, (int) $task->subject_id, $actorUserId);
            $this->assertTaskAssignable($task);
            $this->assertVersionAssignable($version);

            $effectiveFrom = $this->resolveEffectiveChangeDate($studentId, $taskId);
            $closeDate = $effectiveFrom->copy()->subDay();
            $current = $this->lockCurrentAssignment($studentId, $taskId);

            if ($current && (int) $current->version_id === $versionId && $current->isOpenEnded()) {
                $this->createGenerationStateIfMissing($studentId, $taskId, $current->effective_from_date);

                return;
            }

            if ($current) {
                if ($current->effective_from_date->gt($closeDate)) {
                    $current->delete();
                } else {
                    $current->update(['effective_to_date' => $closeDate->toDateString()]);
                }
            }

            $assignment = DifferentiatedTaskStudentAssignment::create([
                'student_id' => $studentId,
                'differentiated_task_id' => $taskId,
                'version_id' => $versionId,
                'effective_from_date' => $effectiveFrom->toDateString(),
                'effective_to_date' => null,
                'assigned_by_user_id' => $actorUserId,
            ]);

            $this->reactivateGenerationState($studentId, $taskId, $effectiveFrom);
            $this->recordHistory(
                $studentId,
                $taskId,
                $current ? 'move' : 'assign',
                $current?->version,
                $assignment->version,
                $actorUserId
            );
        });
    }

    private function lockTaskForTeacher(int $taskId, int $actorUserId, int $subjectId): DifferentiatedTask
    {
        $task = DifferentiatedTask::query()
            ->whereKey($taskId)
            ->where('created_by_user_id', $actorUserId)
            ->where('subject_id', $subjectId)
            ->with(['versions.selectedAttachments'])
            ->lockForUpdate()
            ->firstOrFail();

        $this->assertTeacherCanAccessSubject($subjectId, $actorUserId);

        return $task;
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

    private function lockVersionForTask(int $versionId, int $taskId): DifferentiatedTaskVersion
    {
        return DifferentiatedTaskVersion::query()
            ->whereKey($versionId)
            ->where('differentiated_task_id', $taskId)
            ->with('selectedAttachments')
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function assertTaskAssignable(DifferentiatedTask $task): void
    {
        if ($task->isArchived()) {
            throw new RuntimeException('Archived Differentiated Tasks cannot be assigned.');
        }

        if ($task->validVersionsCount() < 2) {
            throw new RuntimeException('At least two valid versions are required before assigning students.');
        }
    }

    private function assertVersionAssignable(DifferentiatedTaskVersion $version): void
    {
        if (! $version->hasMeaningfulContent()) {
            throw new RuntimeException('This version needs a description, an attachment, or both before it can be assigned.');
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

    private function lockCurrentAssignment(int $studentId, int $taskId): ?DifferentiatedTaskStudentAssignment
    {
        return DifferentiatedTaskStudentAssignment::query()
            ->forStudent($studentId)
            ->forTask($taskId)
            ->openEnded()
            ->with('version')
            ->lockForUpdate()
            ->first();
    }

    private function resolveEffectiveChangeDate(int $studentId, int $taskId): Carbon
    {
        $today = now('Africa/Cairo')->startOfDay();

        $deliveredToday = DB::table('class_sessions')
            ->where('student_id', $studentId)
            ->where('differentiated_task_id', $taskId)
            ->whereDate('generated_for_date', $today->toDateString())
            ->exists();

        return $deliveredToday ? $today->copy()->addDay() : $today;
    }

    private function reactivateGenerationState(int $studentId, int $taskId, Carbon $effectiveFrom): void
    {
        $state = DifferentiatedTaskStudentGenerationState::query()
            ->forStudent($studentId)
            ->forTask($taskId)
            ->lockForUpdate()
            ->first();

        if ($state) {
            $state->update([
                'is_active' => 1,
                'start_date' => $effectiveFrom->toDateString(),
                'end_date' => null,
            ]);

            return;
        }

        DifferentiatedTaskStudentGenerationState::create([
            'student_id' => $studentId,
            'differentiated_task_id' => $taskId,
            'is_active' => 1,
            'start_date' => $effectiveFrom->toDateString(),
            'end_date' => null,
            'last_generated_date' => null,
            'paused_through_date' => null,
        ]);
    }

    private function createGenerationStateIfMissing(int $studentId, int $taskId, Carbon $effectiveFrom): void
    {
        $exists = DifferentiatedTaskStudentGenerationState::query()
            ->forStudent($studentId)
            ->forTask($taskId)
            ->lockForUpdate()
            ->exists();

        if ($exists) {
            return;
        }

        DifferentiatedTaskStudentGenerationState::create([
            'student_id' => $studentId,
            'differentiated_task_id' => $taskId,
            'is_active' => 1,
            'start_date' => $effectiveFrom->toDateString(),
            'end_date' => null,
            'last_generated_date' => null,
            'paused_through_date' => null,
        ]);
    }

    private function deactivateGenerationState(int $studentId, int $taskId, Carbon $endDate): void
    {
        $state = DifferentiatedTaskStudentGenerationState::query()
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
        ?DifferentiatedTaskVersion $fromVersion,
        ?DifferentiatedTaskVersion $toVersion,
        int $actorUserId
    ): void {
        DifferentiatedTaskStudentAssignmentHistory::create([
            'student_id' => $studentId,
            'differentiated_task_id' => $taskId,
            'event_type' => $eventType,
            'from_version_id' => $fromVersion?->id,
            'from_version_display_name' => $fromVersion?->display_name,
            'to_version_id' => $toVersion?->id,
            'to_version_display_name' => $toVersion?->display_name,
            'actor_user_id' => $actorUserId,
        ]);
    }
}
