<?php

namespace App\Services;

use App\Models\MainDailySessionStudentAssignment;
use App\Models\MainDailySessionStudentAssignmentHistory;
use App\Models\MainDailySessionVersion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Maintains the Versioned Routine assignment invariant: for one routine
 * template, a student may have only one active/effective version assignment at
 * a time. Moving to another version closes future generation for the previous
 * version while preserving historical generated snapshots.
 *
 * Future-starting assignment rows can be hard-deleted because generated
 * session_tasks reference source_version_task_id_snapshot rather than
 * assignment ids, the snapshot writer does not foreign-key back to assignment
 * rows, and assignment history is stored separately.
 */
class AutomatedTaskAssignmentService
{
    /**
     * Returns the assignment interval that covers the given date for a student
     * and template, or null if the student is unassigned on that date.
     */
    public function resolveEffectiveAssignment(
        int $studentId,
        int $templateId,
        Carbon $date,
        bool $lockForUpdate = false
    ): ?MainDailySessionStudentAssignment {
        $query = MainDailySessionStudentAssignment::query()
            ->forStudent($studentId)
            ->forTemplate($templateId)
            ->effectiveOn($date)
            ->with(['template', 'version'])
            ->orderByDesc('effective_from_date')
            ->orderByDesc('id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    /**
     * Creates an initial assignment interval starting on $effectiveFrom.
     *
     * Locks ALL intervals for the student+template pair and checks for any
     * existing interval whose date range covers $effectiveFrom before inserting.
     * This prevents both open-ended and closed-interval overlaps under concurrent
     * writes.
     */
    public function createAssignment(
        int $studentId,
        int $templateId,
        int $versionId,
        Carbon $effectiveFrom,
        int $actorUserId
    ): MainDailySessionStudentAssignment {
        return DB::transaction(function () use ($studentId, $templateId, $versionId, $effectiveFrom, $actorUserId) {
            $version = $this->lockVersionForAssignment($versionId, $templateId);
            $today = Carbon::today();
            $todayString = $today->toDateString();
            $closeDateString = $today->copy()->subDay()->toDateString();
            $effectiveFromString = $effectiveFrom->toDateString();

            $intervals = DB::table('main_daily_session_student_assignments')
                ->where('student_id', $studentId)
                ->where('main_daily_session_template_id', $templateId)
                ->where(function ($query) use ($todayString): void {
                    $query->whereNull('effective_to_date')
                        ->orWhere('effective_to_date', '>=', $todayString);
                })
                ->lockForUpdate()
                ->get();

            $versionNames = MainDailySessionVersion::query()
                ->whereIn('id', $intervals->pluck('version_id')->push($versionId)->unique()->values()->all())
                ->pluck('display_name', 'id');

            $sameVersionEffective = $intervals->first(function ($row) use ($versionId, $effectiveFromString): bool {
                return (int) $row->version_id === $versionId
                    && $row->effective_from_date <= $effectiveFromString
                    && ($row->effective_to_date === null || $row->effective_to_date >= $effectiveFromString);
            });

            foreach ($intervals as $interval) {
                if ((int) $interval->version_id === $versionId) {
                    continue;
                }

                if ($interval->effective_from_date >= $todayString) {
                    DB::table('main_daily_session_student_assignments')
                        ->where('id', $interval->id)
                        ->delete();
                } else {
                    DB::table('main_daily_session_student_assignments')
                        ->where('id', $interval->id)
                        ->update(['effective_to_date' => $closeDateString, 'updated_at' => now()]);
                }

                MainDailySessionStudentAssignmentHistory::create([
                    'student_id' => $studentId,
                    'main_daily_session_template_id' => $templateId,
                    'event_type' => 'unassign',
                    'from_version_id' => $interval->version_id,
                    'from_version_display_name' => (string) ($versionNames[$interval->version_id] ?? ''),
                    'to_version_id' => null,
                    'to_version_display_name' => null,
                    'actor_user_id' => $actorUserId,
                ]);
            }

            if ($sameVersionEffective) {
                return MainDailySessionStudentAssignment::query()->findOrFail($sameVersionEffective->id);
            }

            $assignment = MainDailySessionStudentAssignment::create([
                'student_id' => $studentId,
                'main_daily_session_template_id' => $templateId,
                'version_id' => $versionId,
                'effective_from_date' => $effectiveFromString,
                'effective_to_date' => null,
                'assigned_by_user_id' => $actorUserId,
            ]);

            MainDailySessionStudentAssignmentHistory::create([
                'student_id' => $studentId,
                'main_daily_session_template_id' => $templateId,
                'event_type' => 'assign',
                'from_version_id' => null,
                'from_version_display_name' => null,
                'to_version_id' => $versionId,
                'to_version_display_name' => (string) $version->display_name,
                'actor_user_id' => $actorUserId,
            ]);

            return $assignment;
        });
    }

    public function unassignVersion(
        int $studentId,
        int $templateId,
        int $versionId,
        Carbon $closeDate,
        int $actorUserId
    ): void {
        DB::transaction(function () use ($studentId, $templateId, $versionId, $closeDate, $actorUserId): void {
            $closeDateString = $closeDate->toDateString();
            $assignments = DB::table('main_daily_session_student_assignments')
                ->where('student_id', $studentId)
                ->where('main_daily_session_template_id', $templateId)
                ->where('version_id', $versionId)
                ->where(function ($query) use ($closeDateString): void {
                    $query->whereNull('effective_to_date')
                        ->orWhere('effective_to_date', '>=', $closeDateString);
                })
                ->lockForUpdate()
                ->get();

            if ($assignments->isEmpty()) {
                return;
            }

            $oldVersionName = MainDailySessionVersion::find($versionId)?->display_name ?? '';

            foreach ($assignments as $assignment) {
                if ($assignment->effective_from_date > $closeDateString) {
                    DB::table('main_daily_session_student_assignments')
                        ->where('id', $assignment->id)
                        ->delete();
                } else {
                    DB::table('main_daily_session_student_assignments')
                        ->where('id', $assignment->id)
                        ->update(['effective_to_date' => $closeDateString, 'updated_at' => now()]);
                }

                MainDailySessionStudentAssignmentHistory::create([
                    'student_id' => $studentId,
                    'main_daily_session_template_id' => $templateId,
                    'event_type' => 'unassign',
                    'from_version_id' => $assignment->version_id,
                    'from_version_display_name' => $oldVersionName,
                    'to_version_id' => null,
                    'to_version_display_name' => null,
                    'actor_user_id' => $actorUserId,
                ]);
            }
        });
    }

    public function reassign(
        int $studentId,
        int $templateId,
        int $newVersionId,
        Carbon $effectiveFrom,
        int $actorUserId
    ): MainDailySessionStudentAssignment {
        return $this->createAssignment($studentId, $templateId, $newVersionId, $effectiveFrom, $actorUserId);
    }

    /**
     * Closes all current open version intervals for a student. Student remains subscribed.
     */
    public function unassign(
        int $studentId,
        int $templateId,
        Carbon $closeDate,
        int $actorUserId
    ): void {
        DB::transaction(function () use ($studentId, $templateId, $closeDate, $actorUserId) {
            $currentAssignments = DB::table('main_daily_session_student_assignments')
                ->where('student_id', $studentId)
                ->where('main_daily_session_template_id', $templateId)
                ->whereNull('effective_to_date')
                ->lockForUpdate()
                ->get();

            if ($currentAssignments->isEmpty()) {
                return;
            }

            $closeDateString = $closeDate->toDateString();
            $versionNames = MainDailySessionVersion::query()
                ->whereIn('id', $currentAssignments->pluck('version_id')->unique()->values()->all())
                ->pluck('display_name', 'id');

            foreach ($currentAssignments as $current) {
                if ($current->effective_from_date > $closeDateString) {
                    DB::table('main_daily_session_student_assignments')
                        ->where('id', $current->id)
                        ->delete();
                } else {
                    DB::table('main_daily_session_student_assignments')
                        ->where('id', $current->id)
                        ->update(['effective_to_date' => $closeDateString, 'updated_at' => now()]);
                }

                $oldVersionName = (string) ($versionNames[$current->version_id] ?? '');

                MainDailySessionStudentAssignmentHistory::create([
                    'student_id' => $studentId,
                    'main_daily_session_template_id' => $templateId,
                    'event_type' => 'unassign',
                    'from_version_id' => $current->version_id,
                    'from_version_display_name' => $oldVersionName,
                    'to_version_id' => null,
                    'to_version_display_name' => null,
                    'actor_user_id' => $actorUserId,
                ]);
            }
        });
    }

    /**
     * Deletes a version and unassigns students affected today or in the future.
     *
     * Current intervals are closed at yesterday so historical generated rows keep
     * their original version reference. Future-starting intervals are removed.
     */
    public function deleteVersion(
        int $versionId,
        Carbon $effectiveDate,
        int $actorUserId
    ): void {
        DB::transaction(function () use ($versionId, $effectiveDate, $actorUserId): void {
            $version = MainDailySessionVersion::query()
                ->lockForUpdate()
                ->findOrFail($versionId);

            $templateId = (int) $version->main_daily_session_template_id;
            $versionName = (string) $version->display_name;
            $effectiveDateString = $effectiveDate->toDateString();
            $closeDateString = $effectiveDate->copy()->subDay()->toDateString();

            $assignments = DB::table('main_daily_session_student_assignments')
                ->where('main_daily_session_template_id', $templateId)
                ->where('version_id', $versionId)
                ->where(function ($query) use ($effectiveDateString): void {
                    $query->whereNull('effective_to_date')
                        ->orWhere('effective_to_date', '>=', $effectiveDateString);
                })
                ->lockForUpdate()
                ->get();

            foreach ($assignments->groupBy('student_id') as $studentId => $studentAssignments) {
                foreach ($studentAssignments as $assignment) {
                    if ($assignment->effective_from_date >= $effectiveDateString) {
                        DB::table('main_daily_session_student_assignments')
                            ->where('id', $assignment->id)
                            ->delete();

                        continue;
                    }

                    DB::table('main_daily_session_student_assignments')
                        ->where('id', $assignment->id)
                        ->update(['effective_to_date' => $closeDateString, 'updated_at' => now()]);
                }

                MainDailySessionStudentAssignmentHistory::create([
                    'student_id' => (int) $studentId,
                    'main_daily_session_template_id' => $templateId,
                    'event_type' => 'unassign',
                    'from_version_id' => $versionId,
                    'from_version_display_name' => $versionName,
                    'to_version_id' => null,
                    'to_version_display_name' => null,
                    'actor_user_id' => $actorUserId,
                ]);
            }

            DB::table('main_daily_session_version_tasks')
                ->where('version_id', $versionId)
                ->delete();

            $version->delete();
        });
    }

    /**
     * Returns true if the given version has at least one included task and every
     * included task passes the meaningful-content rule. Used to gate assignment.
     */
    public function versionIsAssignable(int $versionId): bool
    {
        $version = MainDailySessionVersion::with([
            'versionTasks.mainTask.attachments',
        ])->find($versionId);

        if (! $version || $version->versionTasks->isEmpty()) {
            return false;
        }

        foreach ($version->versionTasks as $vt) {
            if (! $vt->passesMeaningfulContentRule()) {
                return false;
            }
        }

        return true;
    }

    private function lockVersionForAssignment(int $versionId, int $templateId): MainDailySessionVersion
    {
        return MainDailySessionVersion::query()
            ->whereKey($versionId)
            ->where('main_daily_session_template_id', $templateId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
