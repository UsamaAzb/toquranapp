<?php

namespace App\Services;

use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\MainDailySessionStudentAssignment;
use App\Models\MainDailySessionVersionTask;
use App\Models\SessionMaterial;
use App\Models\SessionTask;
use App\Models\SessionTaskStudent;
use App\Models\TeacherSubjectClass;
use App\Models\Unit;
use App\Services\Vocabulary\VocabularyGameAttachmentBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Writes the immutable per-student snapshot rows for one Automated Task
 * generation event (one student, one assignment interval, one date).
 */
class AutomatedTaskSnapshotWriter
{
    private const UNIT_TYPE_DAILY_SESSION = 1;

    private ?bool $attachmentHasSortOrder = null;

    /**
     * Returns true if at least one generated row was inserted, or false when
     * every relevant row already existed.
     */
    public function writeSnapshot(
        int $studentId,
        MainDailySessionStudentAssignment $assignment,
        Carbon $date,
        int $academicYearId,
        int $classId
    ): bool {
        $template = $assignment->template ?? $assignment->load('template')->template;
        $version = $assignment->version ?? $assignment->load('version')->version;

        // Eager loading main-task attachments prevents N+1 copies during generation.
        $versionTasks = $version->versionTasks()
            ->with('mainTask.attachments')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($versionTasks->isEmpty()) {
            return false;
        }

        $teacherSubjectClass = $this->resolveTeacherSubjectClass($classId, (int) $template->subject_id, $studentId);

        if (! $teacherSubjectClass) {
            Log::info('Automated Task generation skipped because the student has no active subject link.', [
                'student_id' => $studentId,
                'template_id' => $template->id,
                'subject_id' => $template->subject_id,
                'class_id' => $classId,
                'date' => $date->toDateString(),
            ]);

            return false;
        }

        $unit = $this->resolveUnit($teacherSubjectClass, $academicYearId, $classId);

        $classSession = ClassSession::firstOrCreate(
            [
                'main_daily_session_template_id' => $template->id,
                'student_id' => $studentId,
                'generated_for_date' => $date->toDateString(),
            ],
            [
                'teacher_subject_classes_id' => $teacherSubjectClass->id,
                'class_id' => $classId,
                'subject_id' => $teacherSubjectClass->subject_id,
                'grade_id' => $teacherSubjectClass->grade_id,
                'teacher_id' => $teacherSubjectClass->user_teacher_coteacher_id,
                'unit_id' => $unit->id,
                'class_subject_id' => $teacherSubjectClass->class_subject_id,
                'date' => $date->toDateString(),
                'session_start_time' => '00:00',
                'session_end_time' => '00:00',
                'title' => (string) $template->title,
                'daily_session_id' => null,
            ]
        );

        $didWrite = $classSession->wasRecentlyCreated;

        $sessionMaterial = SessionMaterial::firstOrCreate(
            ['session_id' => $classSession->id],
            [
                'teacher_subject_classes_id' => $teacherSubjectClass->id,
                'subject_id' => $teacherSubjectClass->subject_id,
                'grade_id' => $teacherSubjectClass->grade_id,
                'teacher_id' => $teacherSubjectClass->user_teacher_coteacher_id,
                'unit_id' => $unit->id,
                'status' => 'published',
                'assign_to_all' => 'custom',
                'class_id' => $classId,
            ]
        );

        if ($sessionMaterial->wasRecentlyCreated) {
            $didWrite = true;
        }

        foreach ($versionTasks as $versionTask) {
            $didWrite = $this->writeVersionTaskSnapshot(
                $versionTask,
                $classSession->id,
                $sessionMaterial->id,
                $studentId,
                $teacherSubjectClass,
                $classId,
                $version->display_name,
                (int) $template->created_by_user_id
            ) || $didWrite;
        }

        return $didWrite;
    }

    private function writeVersionTaskSnapshot(
        MainDailySessionVersionTask $versionTask,
        int $classSessionId,
        int $sessionMaterialId,
        int $studentId,
        TeacherSubjectClass $teacherSubjectClass,
        int $classId,
        string $versionDisplayName,
        int $assignedByUserId
    ): bool {
        $resolvedDescription = $versionTask->resolveDescription();
        $mainTask = $versionTask->mainTask;
        $didWrite = false;

        $sessionTask = SessionTask::firstOrCreate(
            [
                'class_session_id' => $classSessionId,
                'source_version_task_id_snapshot' => $versionTask->id,
            ],
            [
                'title' => (string) $mainTask->title,
                'taskable_id' => null,
                'task_type_id' => (int) $mainTask->task_type_id,
                'due_date' => null,
                'assign_to_all' => 'custom',
                'description' => $resolvedDescription,
                'default_points' => $mainTask->default_points,
                'max_points' => $mainTask->max_points,
                'marks' => null,
                'session_material_id' => $sessionMaterialId,
                'created_by_teacher_id' => $teacherSubjectClass->user_teacher_coteacher_id,
                'status' => 'published',
                'sort' => $versionTask->sort_order,
                'version_display_name_snapshot' => $versionDisplayName,
            ]
        );

        if ($sessionTask->wasRecentlyCreated) {
            $didWrite = true;
        }

        $sessionTaskStudent = SessionTaskStudent::firstOrCreate(
            [
                'session_task_id' => $sessionTask->id,
                'student_id' => $studentId,
            ],
            [
                'student_points' => null,
                'submitted_at' => null,
                'assign_to_all' => 'custom',
                'status' => 'assigned',
                'flag' => null,
            ]
        );

        if ($sessionTaskStudent->wasRecentlyCreated) {
            $didWrite = true;
        }

        if ($sessionTask->wasRecentlyCreated) {
            $attachmentSortOrder = 1;

            foreach ($mainTask->attachments as $attachment) {
                $vocabularySetId = VocabularyGameAttachmentBuilder::setIdFromPath(
                    (string) ($attachment->path ?: $attachment->url)
                );

                if ($vocabularySetId !== null) {
                    $attributes = app(VocabularyGameAttachmentBuilder::class)->studentAttachmentAttributes(
                        $vocabularySetId,
                        $assignedByUserId,
                        $studentId,
                        $attachment->title,
                        $attachment->description,
                        $teacherSubjectClass,
                        $classId
                    );

                    if ($attributes !== null) {
                        AttachmentFile::create($this->withAttachmentSortOrder(
                            $attributes + ['session_task_id' => $sessionTask->id],
                            $attachmentSortOrder
                        ));

                        $attachmentSortOrder++;
                        $didWrite = true;
                    }

                    continue;
                }

                AttachmentFile::create($this->withAttachmentSortOrder([
                    'title' => $attachment->title,
                    'description' => $attachment->description,
                    'type' => strtolower((string) $attachment->type),
                    'path' => (string) ($attachment->path ?? $attachment->url ?? ''),
                    'file_size' => $attachment->file_size,
                    'subject_id' => $teacherSubjectClass->subject_id,
                    'class_id' => $classId,
                    'teacher_subject_class_id' => $teacherSubjectClass->id,
                    'session_task_id' => $sessionTask->id,
                ], $attachmentSortOrder));

                $attachmentSortOrder++;
                $didWrite = true;
            }
        }

        return $didWrite;
    }

    private function resolveTeacherSubjectClass(int $classId, int $subjectId, int $studentId): ?TeacherSubjectClass
    {
        return TeacherSubjectClass::query()
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->availableForTeacher()
            ->withActiveStudentSubject($studentId)
            ->first();
    }

    private function withAttachmentSortOrder(array $attributes, int $sortOrder): array
    {
        if ($this->attachmentHasSortOrderColumn()) {
            $attributes['sort_order'] = $sortOrder;
        }

        return $attributes;
    }

    private function attachmentHasSortOrderColumn(): bool
    {
        return $this->attachmentHasSortOrder ??= Schema::hasColumn((new AttachmentFile())->getTable(), 'sort_order');
    }

    private function resolveUnit(TeacherSubjectClass $teacherSubjectClass, int $academicYearId, int $classId): Unit
    {
        return Unit::firstOrCreate(
            [
                'teacher_subject_classes_id' => $teacherSubjectClass->id,
                'unit_type_id' => self::UNIT_TYPE_DAILY_SESSION,
                'academic_year_id' => $academicYearId,
            ],
            [
                'title' => 'term_one_'.($teacherSubjectClass->class_name ?? $classId),
                'subject_id' => $teacherSubjectClass->subject_id,
                'class_id' => $teacherSubjectClass->class_id,
                'teacher_id' => $teacherSubjectClass->user_teacher_coteacher_id,
                'grade_level_id' => $teacherSubjectClass->grade_id,
                'status' => 'published',
                'is_interdisciplinary' => 0,
            ]
        );
    }
}
