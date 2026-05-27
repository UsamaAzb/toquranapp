<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskAttachment;
use App\Models\DifferentiatedTaskStudentAssignment;
use App\Models\DifferentiatedTaskVersion;
use App\Models\SessionMaterial;
use App\Models\SessionTask;
use App\Models\SessionTaskStudent;
use App\Models\TeacherSubjectClass;
use App\Models\Unit;
use App\Services\Vocabulary\VocabularyGameAttachmentBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DifferentiatedTaskSnapshotWriter
{
    private const UNIT_TYPE_DAILY_SESSION = 1;

    private ?bool $attachmentHasSortOrder = null;

    public function __construct(
        private readonly TeacherStudentSubjectVisibilityService $studentVisibility,
    ) {}

    public function generateForStudent(
        int $studentId,
        int $taskId,
        int $versionId,
        int $assignmentId,
        Carbon $generatedForDate
    ): bool {
        $academicYearId = AcademicYear::currentId();

        $task = DifferentiatedTask::query()->findOrFail($taskId);
        $version = DifferentiatedTaskVersion::query()
            ->whereKey($versionId)
            ->where('differentiated_task_id', $taskId)
            ->with('selectedAttachments')
            ->firstOrFail();
        $assignment = DifferentiatedTaskStudentAssignment::query()
            ->whereKey($assignmentId)
            ->where('student_id', $studentId)
            ->where('differentiated_task_id', $taskId)
            ->where('version_id', $versionId)
            ->firstOrFail();

        $classId = (int) DB::table('students')
            ->where('id', $studentId)
            ->value('current_class_id');

        if ($classId <= 0) {
            return false;
        }

        $teacherSubjectClass = $this->resolveTeacherSubjectClass(
            $classId,
            (int) $task->subject_id,
            $studentId,
            (int) $task->created_by_user_id
        );

        if (! $teacherSubjectClass) {
            Log::info('Differentiated Task generation skipped because no active teacher/student subject context exists.', [
                'student_id' => $studentId,
                'task_id' => $taskId,
                'subject_id' => $task->subject_id,
                'class_id' => $classId,
                'date' => $generatedForDate->toDateString(),
            ]);

            return false;
        }

        $unit = $this->resolveUnit($teacherSubjectClass, $academicYearId, $classId);

        $classSession = ClassSession::firstOrCreate(
            [
                'differentiated_task_id' => $taskId,
                'student_id' => $studentId,
                'generated_for_date' => $generatedForDate->toDateString(),
            ],
            [
                'teacher_subject_classes_id' => $teacherSubjectClass->id,
                'class_id' => $classId,
                'subject_id' => $teacherSubjectClass->subject_id,
                'grade_id' => $teacherSubjectClass->grade_id,
                'teacher_id' => $teacherSubjectClass->user_teacher_coteacher_id,
                'unit_id' => $unit->id,
                'class_subject_id' => $teacherSubjectClass->class_subject_id,
                'date' => $generatedForDate->toDateString(),
                'session_start_time' => '00:00',
                'session_end_time' => '00:00',
                'title' => (string) $task->title,
                'daily_session_id' => null,
                'main_daily_session_template_id' => null,
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
            ]
        );

        if ($sessionMaterial->wasRecentlyCreated) {
            $didWrite = true;
        }

        $sessionTask = SessionTask::firstOrCreate(
            [
                'class_session_id' => $classSession->id,
                'source_differentiated_task_id_snapshot' => $taskId,
            ],
            [
                'title' => (string) $task->title,
                'taskable_id' => null,
                'task_type_id' => $task->task_type_id,
                'due_date' => null,
                'assign_to_all' => 'custom',
                'description' => $version->description ?: $task->description,
                'default_points' => $task->default_points,
                'max_points' => $task->max_points,
                'marks' => null,
                'session_material_id' => $sessionMaterial->id,
                'created_by_teacher_id' => $teacherSubjectClass->user_teacher_coteacher_id,
                'status' => 'published',
                'sort' => $task->sort_order,
                'version_display_name_snapshot' => $version->display_name,
                'source_differentiated_task_version_id_snapshot' => $version->id,
                'source_differentiated_task_assignment_id_snapshot' => $assignment->id,
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

            foreach ($version->selectedAttachments as $attachment) {
                $didWrite = $this->copyAttachmentSnapshot(
                    $attachment,
                    $task,
                    $studentId,
                    $generatedForDate,
                    $sessionTask->id,
                    $teacherSubjectClass,
                    $classId,
                    $attachmentSortOrder
                ) || $didWrite;

                $attachmentSortOrder++;
            }
        }

        return $didWrite;
    }

    private function copyAttachmentSnapshot(
        DifferentiatedTaskAttachment $attachment,
        DifferentiatedTask $task,
        int $studentId,
        Carbon $generatedForDate,
        int $sessionTaskId,
        TeacherSubjectClass $teacherSubjectClass,
        int $classId,
        int $sortOrder
    ): bool {
        $type = strtolower((string) $attachment->type);
        $vocabularySetId = VocabularyGameAttachmentBuilder::setIdFromPath(
            (string) ($attachment->path ?: $attachment->url)
        );

        if ($vocabularySetId !== null) {
            $attributes = app(VocabularyGameAttachmentBuilder::class)->studentAttachmentAttributes(
                $vocabularySetId,
                (int) $task->created_by_user_id,
                $studentId,
                $attachment->title,
                $attachment->description,
                $teacherSubjectClass,
                $classId
            );

            if ($attributes === null) {
                return false;
            }

            AttachmentFile::create($this->withAttachmentSortOrder(
                $attributes + ['session_task_id' => $sessionTaskId],
                $sortOrder
            ));

            return true;
        }

        $path = $type === 'file'
            ? $this->copyFileAttachment($attachment, $task, $studentId, $generatedForDate, $sessionTaskId)
            : (string) ($attachment->url ?: $attachment->path);

        if ($path === null) {
            return false;
        }

        AttachmentFile::create($this->withAttachmentSortOrder([
            'title' => $attachment->title,
            'description' => $attachment->description,
            'type' => $type,
            'path' => $path,
            'file_size' => $attachment->file_size,
            'subject_id' => $teacherSubjectClass->subject_id,
            'class_id' => $classId,
            'teacher_subject_class_id' => $teacherSubjectClass->id,
            'session_task_id' => $sessionTaskId,
        ], $sortOrder));

        return true;
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

    private function copyFileAttachment(
        DifferentiatedTaskAttachment $attachment,
        DifferentiatedTask $task,
        int $studentId,
        Carbon $generatedForDate,
        int $sessionTaskId
    ): ?string {
        $source = ltrim((string) $attachment->path, '/');

        if ($source === '' || ! Storage::disk('public')->exists($source)) {
            Log::warning('Differentiated Task source attachment file is missing; generated snapshot will omit this attachment.', [
                'attachment_id' => $attachment->id,
                'task_id' => $task->id,
                'student_id' => $studentId,
                'generated_for_date' => $generatedForDate->toDateString(),
                'source_path' => $source,
            ]);

            return null;
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $fileName = $attachment->id.($extension !== '' ? '.'.$extension : '');
        $target = sprintf(
            'automated-tasks/differentiated/%d/%d/%s/%d/%s',
            $task->id,
            $studentId,
            $generatedForDate->toDateString(),
            $sessionTaskId,
            $fileName
        );

        try {
            $copied = Storage::disk('public')->copy($source, $target);
        } catch (Throwable $exception) {
            Log::warning('Differentiated Task attachment copy failed; generated snapshot will omit this attachment.', [
                'attachment_id' => $attachment->id,
                'task_id' => $task->id,
                'student_id' => $studentId,
                'generated_for_date' => $generatedForDate->toDateString(),
                'source_path' => $source,
                'target_path' => $target,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $copied) {
            Log::warning('Differentiated Task attachment copy failed; generated snapshot will omit this attachment.', [
                'attachment_id' => $attachment->id,
                'task_id' => $task->id,
                'student_id' => $studentId,
                'generated_for_date' => $generatedForDate->toDateString(),
                'source_path' => $source,
                'target_path' => $target,
            ]);

            return null;
        }

        return $target;
    }

    private function resolveTeacherSubjectClass(
        int $classId,
        int $subjectId,
        int $studentId,
        int $teacherId
    ): ?TeacherSubjectClass {
        return $this->studentVisibility->resolveTeacherSubjectClassForStudent(
            $teacherId,
            $subjectId,
            $studentId,
            $classId
        );
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
