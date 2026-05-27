<?php

namespace App\Services;

use App\Http\Controllers\VocabularyAssignmentController;
use App\Models\AcademicYear;
use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\SeriesTask;
use App\Models\SeriesTaskStudentAssignment;
use App\Models\SeriesTaskVersion;
use App\Models\SeriesTaskVersionItem;
use App\Models\SessionMaterial;
use App\Models\SessionTask;
use App\Models\SessionTaskStudent;
use App\Models\TeacherSubjectClass;
use App\Models\Unit;
use App\Models\User;
use App\Models\VocabularyGameAssignment;
use App\Models\VocabularySet;
use App\Support\SeriesTasks\SeriesLibraryItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeriesTaskSnapshotWriter
{
    private const UNIT_TYPE_DAILY_SESSION = 1;

    public function __construct(
        private readonly TeacherStudentSubjectVisibilityService $studentVisibility,
        private readonly SeriesLibrarySourceResolver $sourceResolver,
    ) {}

    public function generateForStudent(
        int $studentId,
        int $taskId,
        int $versionId,
        int $versionItemId,
        int $assignmentId,
        Carbon $generatedForDate
    ): bool {
        $academicYearId = AcademicYear::currentId();
        $task = SeriesTask::query()->findOrFail($taskId);
        $version = SeriesTaskVersion::query()
            ->whereKey($versionId)
            ->where('series_task_id', $taskId)
            ->firstOrFail();
        $versionItem = SeriesTaskVersionItem::query()
            ->whereKey($versionItemId)
            ->where('version_id', $versionId)
            ->firstOrFail();
        $assignment = SeriesTaskStudentAssignment::query()
            ->whereKey($assignmentId)
            ->where('student_id', $studentId)
            ->where('series_task_id', $taskId)
            ->where('version_id', $versionId)
            ->firstOrFail();
        $libraryItem = $this->sourceResolver->resolveItem(
            (string) $versionItem->library_source_type,
            (int) $versionItem->library_source_id
        );

        if (! $libraryItem || (! $this->isVocabularyLibraryItem($libraryItem) && ! $libraryItem->hasSafeDeliveryTarget())) {
            Log::warning('Series Task snapshot skipped because the selected Library item no longer resolves safely.', [
                'series_task_id' => $taskId,
                'version_id' => $versionId,
                'version_item_id' => $versionItemId,
                'student_id' => $studentId,
            ]);

            return false;
        }

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
            Log::info('Series Task generation skipped because no active teacher/student subject context exists.', [
                'student_id' => $studentId,
                'series_task_id' => $taskId,
                'subject_id' => $task->subject_id,
                'class_id' => $classId,
                'date' => $generatedForDate->toDateString(),
            ]);

            return false;
        }

        $unit = $this->resolveUnit($teacherSubjectClass, $academicYearId, $classId);

        $classSession = ClassSession::firstOrCreate(
            [
                'series_task_id' => $taskId,
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
                'differentiated_task_id' => null,
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
                'source_series_task_id_snapshot' => $taskId,
            ],
            [
                'title' => $this->snapshotTitle($task, $libraryItem),
                'taskable_id' => null,
                'task_type_id' => $task->task_type_id,
                'due_date' => null,
                'assign_to_all' => 'custom',
                'description' => $libraryItem->summary ?: $versionItem->library_summary_snapshot ?: $task->description,
                'default_points' => $task->default_points,
                'max_points' => $task->max_points,
                'marks' => null,
                'session_material_id' => $sessionMaterial->id,
                'created_by_teacher_id' => $teacherSubjectClass->user_teacher_coteacher_id,
                'status' => 'published',
                'sort' => $versionItem->sequence_position,
                'version_display_name_snapshot' => $version->display_name,
                'source_series_task_version_id_snapshot' => $version->id,
                'source_series_task_version_item_id_snapshot' => $versionItem->id,
                'source_series_task_assignment_id_snapshot' => $assignment->id,
                'source_series_library_type_snapshot' => $versionItem->library_source_type,
                'source_series_library_id_snapshot' => $versionItem->library_source_id,
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
            $didWrite = $this->writeLibraryAttachment(
                $libraryItem,
                $sessionTask->id,
                $teacherSubjectClass,
                $classId,
                $task,
                $studentId
            ) || $didWrite;
        }

        return $didWrite;
    }

    private function writeLibraryAttachment(
        SeriesLibraryItem $libraryItem,
        int $sessionTaskId,
        TeacherSubjectClass $teacherSubjectClass,
        int $classId,
        SeriesTask $task,
        int $studentId
    ): bool {
        if ($this->isVocabularyLibraryItem($libraryItem)) {
            return $this->writeVocabularyGameAttachment($libraryItem, $sessionTaskId, $teacherSubjectClass, $classId, $task, $studentId);
        }

        $path = $libraryItem->mediaPath ?: $libraryItem->url;

        if (! $path) {
            return false;
        }

        AttachmentFile::create([
            'title' => $libraryItem->title,
            'description' => $libraryItem->summary,
            'type' => $libraryItem->mediaType ?: 'link',
            'path' => $path,
            'file_size' => $libraryItem->fileSize,
            'subject_id' => $teacherSubjectClass->subject_id,
            'class_id' => $classId,
            'teacher_subject_class_id' => $teacherSubjectClass->id,
            'session_task_id' => $sessionTaskId,
        ]);

        return true;
    }

    private function writeVocabularyGameAttachment(
        SeriesLibraryItem $libraryItem,
        int $sessionTaskId,
        TeacherSubjectClass $teacherSubjectClass,
        int $classId,
        SeriesTask $task,
        int $studentId
    ): bool {
        $setQuery = VocabularySet::query()
            ->playable()
            ->whereKey((int) $libraryItem->sourceId)
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED);
        $assignedBy = User::query()->find((int) $task->created_by_user_id);

        if (! $assignedBy?->hasAnyRole(['admin', 'super_admin', 'owner'])) {
            $setQuery->visibleToTeachers((int) $task->created_by_user_id);
        }

        $set = $setQuery->first();

        if (! $set instanceof VocabularySet || ! $set->canBeLaunched()) {
            return false;
        }

        $allowedGames = $this->seriesVocabularyAllowedGames($task);
        $difficultyPolicy = $this->seriesVocabularyDifficultyPolicy($task);
        $assignment = VocabularyGameAssignment::query()->create([
            'vocabulary_set_id' => $set->id,
            'assigned_by_user_id' => $task->created_by_user_id,
            'audience_type' => VocabularyGameAssignment::AUDIENCE_STUDENT,
            'audience_id' => $studentId,
            'allowed_games' => $allowedGames,
            'difficulty_policy' => $difficultyPolicy,
            'status' => VocabularyGameAssignment::STATUS_ACTIVE,
        ]);

        AttachmentFile::create([
            'title' => 'Vocab Game: '.$libraryItem->title,
            'description' => $libraryItem->summary,
            'type' => 'link',
            'path' => VocabularyAssignmentController::assignmentUrl($assignment),
            'file_size' => null,
            'subject_id' => $teacherSubjectClass->subject_id,
            'class_id' => $classId,
            'teacher_subject_class_id' => $teacherSubjectClass->id,
            'session_task_id' => $sessionTaskId,
        ]);

        return true;
    }

    private function isVocabularyLibraryItem(SeriesLibraryItem $libraryItem): bool
    {
        return $libraryItem->sourceType === SeriesLibrarySourceResolver::SOURCE_VOCABULARY_LIST;
    }

    private function seriesVocabularyAllowedGames(SeriesTask $task): array
    {
        return ['hangman', 'missing_letter', 'spelling_choice'];
    }

    private function seriesVocabularyDifficultyPolicy(SeriesTask $task): string
    {
        return VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE;
    }

    private function snapshotTitle(SeriesTask $task, SeriesLibraryItem $libraryItem): string
    {
        $taskTitle = trim((string) $task->title);
        $itemTitle = trim($libraryItem->title);

        if ($taskTitle === '' || strcasecmp($taskTitle, $itemTitle) === 0) {
            return $itemTitle;
        }

        return $taskTitle.' - '.$itemTitle;
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
