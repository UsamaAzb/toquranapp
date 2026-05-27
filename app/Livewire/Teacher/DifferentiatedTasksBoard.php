<?php

namespace App\Livewire\Teacher;

use App\Models\ClassSession;
use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskAttachment;
use App\Models\DifferentiatedTaskVersion;
use App\Models\DifferentiatedTaskVersionAttachment;
use App\Models\Subject;
use App\Models\TaskType;
use App\Models\TeacherSubjectClass;
use App\Services\DifferentiatedTaskAssignmentService;
use App\Services\DifferentiatedTaskPublishValidator;
use App\Services\Library\LibraryFileRetentionService;
use App\Services\Library\LibraryToDifferentiatedAttachmentWriter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class DifferentiatedTasksBoard extends Component
{
    use WithFileUploads;

    private const MAX_ATTACHMENT_FILE_KB = 51200;

    public int $subjectId;

    public string $subjectName = 'Subject';

    public string $taskScope = 'working';

    public string $boardUrl = '';

    /** @var array{message: string, tone: string}|null */
    public ?array $boardFeedback = null;

    public bool $createTaskOpen = false;

    /** @var array<string, mixed> */
    public array $draftTask = [
        'title' => '',
        'description' => null,
        'task_type_id' => null,
        'default_points' => 5,
        'max_points' => 10,
        'recurrence_kind' => 'daily',
        'recurrence_interval' => 1,
        'recurrence_weekdays' => [],
        'recurrence_day_of_month' => null,
    ];

    /** @var array<int, array<string, mixed>> */
    public array $taskForms = [];

    /** @var array<int, array<string, mixed>> */
    public array $versionForms = [];

    /** @var array<int, array<string, mixed>> */
    public array $attachmentForms = [];

    /** @var array<int, array<int, string>> */
    public array $publishErrors = [];

    /** @var array<int, true> */
    public array $expandedTasks = [];

    /** @var array<int, true> */
    public array $settingsOpen = [];

    /** @var array<int, true> */
    public array $versionEditorsOpen = [];

    /** @var array<int, array<string, mixed>> */
    public array $taskTypes = [];

    public array $taskFilesByTask = [];

    public ?int $libraryPickerTaskId = null;

    public function mount(int $subjectId): void
    {
        $this->subjectId = $this->ensureOwnedSubjectOrFail($subjectId);
        $this->subjectName = Subject::query()->whereKey($this->subjectId)->value('title') ?: 'Subject';
        $this->boardUrl = request()->url();
        $this->taskScope = $this->normalizeTaskScope((string) request()->query('dt_scope', $this->taskScope));
        $this->boardFeedback = Session::pull('differentiated_task_board_feedback');
        $this->taskTypes = TaskType::query()
            ->orderBy('title')
            ->get(['id', 'title', 'default_points', 'max_points'])
            ->map(fn (TaskType $type): array => [
                'id' => (int) $type->id,
                'title' => (string) $type->title,
                'default_points' => $type->default_points,
                'max_points' => $type->max_points,
            ])
            ->all();
        $this->draftTask['task_type_id'] = $this->taskTypes[0]['id'] ?? null;
        $this->syncBoardState();
    }

    public function setTaskScope(string $scope): void
    {
        $this->taskScope = $this->normalizeTaskScope($scope);
        $this->boardFeedback = null;
        $this->syncBoardState();
    }

    #[On('differentiated-task-assignment-saved')]
    public function refreshBoard(): void
    {
        $this->boardFeedback = null;
        $this->syncBoardState();
    }

    #[On('library-resources-selected')]
    public function useLibraryResources(array $resourceIds): void
    {
        if ($this->libraryPickerTaskId === null) {
            return;
        }

        $task = $this->resolveOwnedTaskOrFail($this->libraryPickerTaskId);
        $this->keepTaskExpanded((int) $task->id);

        if ($task->isArchived()) {
            $this->libraryPickerTaskId = null;

            return;
        }

        $nextSortOrder = ((int) $task->attachments()->max('sort_order')) + 1;
        $writer = app(LibraryToDifferentiatedAttachmentWriter::class);

        foreach (array_values($resourceIds) as $index => $resourceId) {
            $writer->writeOneForTaskAtSortOrder(
                $task,
                (string) $resourceId,
                (int) Auth::id(),
                $this->subjectId,
                $nextSortOrder + $index
            );
        }

        $this->libraryPickerTaskId = null;
        $this->publishErrors[(int) $task->id] = [];
        $this->syncBoardState();
    }

    public function toggleCreateTaskForm(): void
    {
        $this->createTaskOpen = ! $this->createTaskOpen;
    }

    public function createTask(): void
    {
        $payload = $this->validatedTaskPayload($this->draftTask, true);

        DB::transaction(function () use ($payload): void {
            $task = DifferentiatedTask::create($payload + [
                'subject_id' => $this->subjectId,
                'created_by_user_id' => Auth::id(),
                'status' => 'draft',
                'published_at' => null,
            ]);

            DifferentiatedTaskVersion::create([
                'differentiated_task_id' => $task->id,
                'display_name' => 'Version 1',
                'sort_order' => 1,
            ]);

            DifferentiatedTaskVersion::create([
                'differentiated_task_id' => $task->id,
                'display_name' => 'Version 2',
                'sort_order' => 2,
            ]);

            $this->expandedTasks[$task->id] = true;
        });

        $this->draftTask = [
            'title' => '',
            'description' => null,
            'task_type_id' => $this->taskTypes[0]['id'] ?? null,
            'default_points' => 5,
            'max_points' => 10,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'recurrence_weekdays' => [],
            'recurrence_day_of_month' => null,
        ];
        $this->createTaskOpen = false;
        $this->syncBoardState();
    }

    public function saveTask(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $this->keepTaskExpanded($taskId);

        if ($task->isArchived()) {
            $this->taskForms[$taskId] = $this->taskFormFromModel($task);

            return;
        }

        $payload = $this->validatedTaskPayload($this->taskForms[$taskId] ?? [], false, $taskId);
        $task->update($payload);
        unset($this->settingsOpen[$taskId]);
        $this->publishErrors[$taskId] = [];
        $this->syncBoardState();
    }

    public function addVersion(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $this->keepTaskExpanded((int) $task->id);
        $nextSortOrder = ((int) $task->versions()->max('sort_order')) + 1;

        $version = DifferentiatedTaskVersion::create([
            'differentiated_task_id' => $task->id,
            'display_name' => 'Version '.$nextSortOrder,
            'sort_order' => $nextSortOrder,
        ]);

        $this->versionEditorsOpen[$version->id] = true;
        $this->expandedTasks[$task->id] = true;
        $this->syncBoardState();
    }

    public function openVersionEditor(int $versionId): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId);
        $this->keepTaskExpanded((int) $version->differentiated_task_id);
        $this->versionEditorsOpen[$version->id] = true;
        $this->versionForms[$version->id] = $this->versionFormFromModel($version);
    }

    public function openAssignmentModal(int $taskId, int $versionId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId)->load(['versions.selectedAttachments']);
        $this->keepTaskExpanded($taskId);
        $version = $task->versions->firstWhere('id', $versionId);

        abort_unless($version !== null, 404);

        if ($task->isArchived()) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('assignment', 'Restore this Differentiated Task before editing assignments.');
            })->validate();
        }

        if ($task->validVersionsCount() < 2 || ! $version->hasMeaningfulContent()) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('assignment', 'At least two ready versions are required before assigning students.');
            })->validate();
        }

        $this->dispatch('open-differentiated-task-assignment-modal', taskId: $task->id, versionId: $version->id)
            ->to(DifferentiatedTaskAssignmentModal::class);
    }

    public function closeVersionEditor(int $versionId): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId);
        $this->keepTaskExpanded((int) $version->differentiated_task_id);
        unset($this->versionEditorsOpen[$versionId]);
    }

    public function saveVersion(int $versionId): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId)->loadMissing(['task', 'selectedAttachments']);
        $this->keepTaskExpanded((int) $version->differentiated_task_id);
        $form = $this->versionForms[$versionId] ?? [];
        $payload = $this->validatedVersionPayload($version, $form);
        $selectedAttachmentIds = array_values(array_unique(array_map('intval', $payload['selected_attachment_ids'])));

        $wouldBeMeaningful = filled($payload['description']) || ! empty($selectedAttachmentIds);
        $hasOpenAssignments = $version->studentAssignments()->openEnded()->exists();

        if (($version->task?->isActive() || $hasOpenAssignments) && ! $wouldBeMeaningful) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('version', 'Assigned or active versions need a description, an attachment, or both.');
            })->validate();
        }

        DB::transaction(function () use ($version, $payload, $selectedAttachmentIds): void {
            $version->update([
                'display_name' => $payload['display_name'],
                'description' => $payload['description'],
            ]);

            DifferentiatedTaskVersionAttachment::query()
                ->where('version_id', $version->id)
                ->delete();

            foreach ($selectedAttachmentIds as $index => $attachmentId) {
                DifferentiatedTaskVersionAttachment::create([
                    'version_id' => $version->id,
                    'attachment_id' => $attachmentId,
                    'sort_order' => $index + 1,
                ]);
            }
        });

        unset($this->versionEditorsOpen[$versionId]);
        $this->publishErrors[(int) $version->differentiated_task_id] = [];
        $this->syncBoardState();
    }

    public function deleteVersion(int $versionId, DifferentiatedTaskAssignmentService $assignmentService): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId);
        $taskId = (int) $version->differentiated_task_id;
        $this->keepTaskExpanded($taskId);
        $snapshotCount = $this->generatedSnapshotCountForVersion($version->id);

        $assignmentService->deleteVersion($version->id, (int) Auth::id(), Carbon::today('Africa/Cairo'));
        $version->attachmentSelections()->delete();
        $version->delete();

        $this->publishErrors[$taskId] = [];
        unset($this->versionEditorsOpen[$versionId]);

        if ($snapshotCount > 0) {
            $this->boardFeedback = [
                'message' => 'Version deleted for future work. Already delivered student tasks were left unchanged.',
                'tone' => 'warning',
            ];
        }

        $this->syncBoardState();
    }

    public function publishTask(int $taskId, DifferentiatedTaskPublishValidator $validator): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId)->fresh(['versions.selectedAttachments']);
        $this->keepTaskExpanded($taskId);
        $result = $validator->validate($task);
        $this->publishErrors[$taskId] = $result->errors;

        if ($result->fails()) {
            $this->syncBoardState();

            return;
        }

        $task->update([
            'status' => 'active',
            'published_at' => now('Africa/Cairo'),
        ]);

        $assignmentCount = $task->studentAssignments()->openEnded()->count();
        $this->boardFeedback = $assignmentCount === 0
            ? [
                'message' => 'Differentiated Task activated. No student work will generate until students are assigned.',
                'tone' => 'info',
            ]
            : [
                'message' => 'Differentiated Task activated for scheduled generation.',
                'tone' => 'success',
            ];

        $this->publishErrors[$taskId] = [];
        $this->syncBoardState();
    }

    public function unpublishTask(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $this->keepTaskExpanded($taskId);
        $task->update(['status' => 'draft']);
        $this->publishErrors[$taskId] = [];
        $this->syncBoardState();
    }

    public function archiveTask(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $task->update(['status' => 'archived']);
        $this->taskScope = 'archived';
        Session::flash('differentiated_task_board_feedback', [
            'message' => 'Differentiated Task archived. Generated student work was left unchanged.',
            'tone' => 'warning',
        ]);

        $this->redirect($this->scopeUrl('archived'), navigate: false);
    }

    public function restoreTask(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $task->update(['status' => 'draft']);
        $this->publishErrors[$taskId] = [];
        Session::flash('differentiated_task_board_feedback', [
            'message' => 'Differentiated Task restored to draft.',
            'tone' => 'success',
        ]);

        $this->redirect($this->scopeUrl('working'), navigate: false);
    }

    public function deleteTask(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);

        if (
            ! $task->isDraft()
            || $this->generatedSnapshotCountForTask($task->id) > 0
            || $task->studentAssignments()->exists()
            || $task->generationStates()->exists()
            || $task->assignmentHistory()->exists()
        ) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('task', 'Only draft tasks with no assignments, generation state, history, or generated snapshots can be deleted.');
            })->validate();
        }

        DB::transaction(function () use ($task): void {
            $paths = $task->attachments()
                ->where('type', 'file')
                ->pluck('path')
                ->filter()
                ->all();

            $task->delete();

            foreach ($paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        });

        $this->syncBoardState();
    }

    public function uploadTaskFiles(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $this->keepTaskExpanded($taskId);
        $validUploads = collect($this->taskFilesByTask[$taskId] ?? [])
            ->filter(fn ($upload): bool => $upload instanceof TemporaryUploadedFile && is_file($upload->getRealPath()))
            ->values()
            ->all();

        Validator::make([
            'uploads' => $validUploads,
        ], [
            'uploads' => ['required', 'array', 'min:1'],
            'uploads.*' => ['file', 'max:'.self::MAX_ATTACHMENT_FILE_KB],
        ])->validate();

        $nextSortOrder = ((int) $task->attachments()->max('sort_order')) + 1;

        foreach ($validUploads as $upload) {
            $storedPath = Storage::disk('public')->putFile('differentiated-task-attachments', $upload);

            if ($storedPath === false) {
                throw new \RuntimeException('Failed to store Differentiated Task attachment file.');
            }

            DifferentiatedTaskAttachment::create([
                'differentiated_task_id' => $task->id,
                'type' => 'file',
                'title' => $upload->getClientOriginalName(),
                'description' => null,
                'path' => $storedPath,
                'url' => null,
                'file_size' => $upload->getSize() ?: null,
                'sort_order' => $nextSortOrder++,
            ]);
        }

        unset($this->taskFilesByTask[$taskId]);
        $this->publishErrors[$taskId] = [];
        $this->syncBoardState();
    }

    public function openLibraryPicker(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $this->keepTaskExpanded((int) $task->id);

        if ($task->isArchived()) {
            return;
        }

        $this->libraryPickerTaskId = (int) $task->id;
        $this->dispatch(
            'open-library-picker',
            subjectId: $this->subjectId,
            selectedResourceIds: []
        );
    }

    public function updatedTaskFilesByTask(mixed $value, int|string $key): void
    {
        $taskId = (int) $key;

        if ($taskId <= 0) {
            return;
        }

        $this->uploadTaskFiles($taskId);
    }

    public function addLinkAttachment(int $taskId): void
    {
        $this->addExternalAttachment($taskId, 'link');
    }

    public function addYoutubeAttachment(int $taskId): void
    {
        $this->addExternalAttachment($taskId, 'youtube');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = $this->resolveOwnedAttachmentOrFail($attachmentId);
        $task = $attachment->task()->firstOrFail();
        $this->keepTaskExpanded((int) $task->id);

        if ($this->attachmentRemovalWouldBreakActiveContent($attachment)) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('attachment', 'This attachment is keeping an active or assigned version valid. Remove it from the version first or add other content.');
            })->validate();
        }

        $pathToDelete = $attachment->isFile() ? $attachment->path : null;
        $attachment->delete();

        if ($pathToDelete) {
            app(LibraryFileRetentionService::class)->deleteIfUnreferenced($pathToDelete);
        }

        $this->publishErrors[(int) $task->id] = [];
        $this->syncBoardState();
    }

    public function openSettings(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $this->keepTaskExpanded((int) $task->id);
        $this->settingsOpen[$task->id] = true;
        $this->taskForms[$task->id] = $this->taskFormFromModel($task);
    }

    public function closeSettings(int $taskId): void
    {
        $this->keepTaskExpanded($taskId);
        unset($this->settingsOpen[$taskId]);
    }

    public function dismissBoardFeedback(): void
    {
        $this->boardFeedback = null;
    }

    public function render(): View
    {
        $tasks = $this->renderTasks();
        $this->ensureForms($tasks);

        return view('livewire.teacher.differentiated-tasks-board', [
            'tasks' => $tasks,
            'scopeCounts' => $this->scopeCounts(),
            'taskAssignmentCounts' => $this->aggregateTaskAssignmentCounts($tasks),
            'versionAssignmentCounts' => $this->aggregateVersionAssignmentCounts($tasks),
            'snapshotCountsByTask' => $this->aggregateGeneratedSnapshotCounts($tasks),
            'versionDiagnostics' => $this->buildVersionDiagnostics($tasks),
        ]);
    }

    protected function ensureOwnedSubjectOrFail(int $subjectId): int
    {
        TeacherSubjectClass::query()
            ->where('subject_id', $subjectId)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->firstOrFail();

        return $subjectId;
    }

    private function syncBoardState(): void
    {
        $tasks = $this->ownedTaskQuery()
            ->with([
                'versions.selectedAttachments',
                'attachments',
                'taskType',
            ])
            ->get();

        $this->ensureForms($tasks);
    }

    private function keepTaskExpanded(int $taskId): void
    {
        $this->expandedTasks[$taskId] = true;
    }

    private function renderTasks(): EloquentCollection
    {
        return $this->taskQuery()
            ->with([
                'versions' => fn ($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->with('selectedAttachments'),
                'attachments' => fn ($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'taskType',
            ])
            ->orderByDesc('id')
            ->get();
    }

    private function ensureForms(EloquentCollection $tasks): void
    {
        foreach ($tasks as $task) {
            $this->taskForms[$task->id] = $this->taskForms[$task->id] ?? $this->taskFormFromModel($task);
            $this->attachmentForms[$task->id] = $this->attachmentForms[$task->id] ?? [
                'link_title' => '',
                'link_url' => '',
                'youtube_title' => '',
                'youtube_url' => '',
            ];
            $this->publishErrors[$task->id] = $this->publishErrors[$task->id] ?? [];

            foreach ($task->versions as $version) {
                $this->versionForms[$version->id] = $this->versionForms[$version->id]
                    ?? $this->versionFormFromModel($version);
            }
        }
    }

    private function taskFormFromModel(DifferentiatedTask $task): array
    {
        return [
            'title' => (string) $task->title,
            'description' => $task->description,
            'task_type_id' => $task->task_type_id,
            'default_points' => $task->default_points,
            'max_points' => $task->max_points,
            'recurrence_kind' => (string) $task->recurrence_kind,
            'recurrence_interval' => (int) ($task->recurrence_interval ?? 1),
            'recurrence_weekdays' => $task->recurrence_weekdays
                ? $this->numericWeekdaysToTextKeys(array_values(array_filter(explode(',', (string) $task->recurrence_weekdays))))
                : [],
            'recurrence_day_of_month' => $task->recurrence_day_of_month,
        ];
    }

    private function versionFormFromModel(DifferentiatedTaskVersion $version): array
    {
        $version->loadMissing('selectedAttachments');

        return [
            'display_name' => (string) $version->display_name,
            'description' => $version->description,
            'selected_attachment_ids' => $version->selectedAttachments
                ->pluck('id')
                ->map(fn ($value): int => (int) $value)
                ->all(),
        ];
    }

    private function taskQuery(): Builder
    {
        $query = $this->ownedTaskQuery();

        if ($this->taskScope === 'archived') {
            return $query->where('status', 'archived');
        }

        return $query->whereIn('status', ['draft', 'active']);
    }

    private function ownedTaskQuery(): Builder
    {
        return DifferentiatedTask::query()
            ->forSubject($this->subjectId)
            ->where('created_by_user_id', Auth::id());
    }

    private function scopeCounts(): array
    {
        return [
            'working' => $this->ownedTaskQuery()
                ->whereIn('status', ['draft', 'active'])
                ->count(),
            'archived' => $this->ownedTaskQuery()
                ->where('status', 'archived')
                ->count(),
        ];
    }

    private function aggregateTaskAssignmentCounts(EloquentCollection $tasks): array
    {
        $taskIds = $tasks->pluck('id')->map(fn ($value): int => (int) $value)->all();

        if (empty($taskIds)) {
            return [];
        }

        return DB::table('differentiated_task_student_assignments')
            ->select('differentiated_task_id', DB::raw('COUNT(DISTINCT student_id) as aggregate_count'))
            ->whereIn('differentiated_task_id', $taskIds)
            ->whereNull('effective_to_date')
            ->groupBy('differentiated_task_id')
            ->pluck('aggregate_count', 'differentiated_task_id')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    private function aggregateVersionAssignmentCounts(EloquentCollection $tasks): array
    {
        $versionIds = $tasks
            ->flatMap(fn (DifferentiatedTask $task) => $task->versions->pluck('id'))
            ->map(fn ($value): int => (int) $value)
            ->all();

        if (empty($versionIds)) {
            return [];
        }

        return DB::table('differentiated_task_student_assignments')
            ->select('version_id', DB::raw('COUNT(DISTINCT student_id) as aggregate_count'))
            ->whereIn('version_id', $versionIds)
            ->whereNull('effective_to_date')
            ->groupBy('version_id')
            ->pluck('aggregate_count', 'version_id')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    private function aggregateGeneratedSnapshotCounts(EloquentCollection $tasks): array
    {
        $taskIds = $tasks->pluck('id')->map(fn ($value): int => (int) $value)->all();

        if (empty($taskIds)) {
            return [];
        }

        return ClassSession::query()
            ->select('differentiated_task_id', DB::raw('COUNT(*) as aggregate_count'))
            ->whereIn('differentiated_task_id', $taskIds)
            ->groupBy('differentiated_task_id')
            ->pluck('aggregate_count', 'differentiated_task_id')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    private function buildVersionDiagnostics(EloquentCollection $tasks): array
    {
        $output = [];

        foreach ($tasks as $task) {
            foreach ($task->versions as $version) {
                $passes = $version->hasMeaningfulContent();
                $output[$task->id][$version->id] = [
                    'passes' => $passes,
                    'errors' => $passes
                        ? []
                        : ['This version needs a description, an attachment, or both before it can be assigned.'],
                ];
            }
        }

        return $output;
    }

    private function validatedTaskPayload(array $input, bool $creating, ?int $taskId = null): array
    {
        $uniqueTitleRule = Rule::unique('differentiated_tasks', 'title')
            ->where('subject_id', $this->subjectId)
            ->where('created_by_user_id', Auth::id());

        if (! $creating && $taskId !== null) {
            $uniqueTitleRule = $uniqueTitleRule->ignore($taskId);
        }

        $weekdayRules = ($input['recurrence_kind'] ?? null) === 'weekly'
            ? ['required', 'array', 'min:1']
            : ['nullable', 'array'];

        $validated = Validator::make($input, [
            'title' => ['required', 'string', 'max:191', $uniqueTitleRule],
            'description' => ['nullable', 'string'],
            'task_type_id' => ['required', 'integer', 'exists:task_types,id'],
            'default_points' => ['nullable', 'integer', 'min:0'],
            'max_points' => ['nullable', 'integer', 'min:0', 'gte:default_points'],
            'recurrence_kind' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'recurrence_interval' => ['required', 'integer', 'min:1', 'max:12'],
            'recurrence_weekdays' => $weekdayRules,
            'recurrence_weekdays.*' => [Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'recurrence_day_of_month' => ['nullable', 'integer', 'between:1,31'],
        ])->after(function ($validator) use ($input): void {
            if (($input['recurrence_kind'] ?? null) === 'monthly' && ! filled($input['recurrence_day_of_month'])) {
                $validator->errors()->add('recurrence_day_of_month', 'Choose a day of month for monthly recurrence.');
            }
        })->validate();

        return [
            'title' => trim((string) $validated['title']),
            'description' => $this->cleanNullableText($validated['description'] ?? null),
            'task_type_id' => (int) $validated['task_type_id'],
            'default_points' => $validated['default_points'] ?? 0,
            'max_points' => $validated['max_points'] ?? 0,
            'recurrence_kind' => $validated['recurrence_kind'],
            'recurrence_interval' => $validated['recurrence_kind'] === 'daily'
                ? (int) $validated['recurrence_interval']
                : 1,
            'recurrence_weekdays' => $validated['recurrence_kind'] === 'weekly'
                ? implode(',', $this->normalizeWeekdaysToNumeric($validated['recurrence_weekdays'] ?? []))
                : null,
            'recurrence_day_of_month' => $validated['recurrence_kind'] === 'monthly'
                ? (int) $validated['recurrence_day_of_month']
                : null,
        ];
    }

    private function validatedVersionPayload(DifferentiatedTaskVersion $version, array $input): array
    {
        $selectedAttachmentIds = array_values(array_unique(array_map('intval', $input['selected_attachment_ids'] ?? [])));

        Validator::make(['attachment_ids' => $selectedAttachmentIds], [
            'attachment_ids' => ['array'],
            'attachment_ids.*' => ['integer'],
        ])->validate();

        if (! empty($selectedAttachmentIds)) {
            $validCount = DifferentiatedTaskAttachment::query()
                ->where('differentiated_task_id', $version->differentiated_task_id)
                ->whereIn('id', $selectedAttachmentIds)
                ->count();

            abort_unless($validCount === count($selectedAttachmentIds), 404);
        }

        $validated = Validator::make($input, [
            'display_name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('differentiated_task_versions', 'display_name')
                    ->where('differentiated_task_id', $version->differentiated_task_id)
                    ->ignore($version->id),
            ],
            'description' => ['nullable', 'string'],
        ])->validate();

        return [
            'display_name' => trim((string) $validated['display_name']),
            'description' => $this->cleanNullableText($validated['description'] ?? null),
            'selected_attachment_ids' => $selectedAttachmentIds,
        ];
    }

    private function addExternalAttachment(int $taskId, string $type): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $this->keepTaskExpanded($taskId);
        $titleKey = $type === 'youtube' ? 'youtube_title' : 'link_title';
        $urlKey = $type === 'youtube' ? 'youtube_url' : 'link_url';
        $form = $this->attachmentForms[$taskId] ?? [];

        $validated = Validator::make($form, [
            $titleKey => ['nullable', 'string', 'max:191'],
            $urlKey => ['required', 'url', 'max:2048'],
        ])->validate();

        DifferentiatedTaskAttachment::create([
            'differentiated_task_id' => $task->id,
            'type' => $type,
            'title' => trim((string) ($validated[$titleKey] ?: ucfirst($type))),
            'description' => null,
            'path' => null,
            'url' => trim((string) $validated[$urlKey]),
            'file_size' => null,
            'sort_order' => ((int) $task->attachments()->max('sort_order')) + 1,
        ]);

        $this->attachmentForms[$taskId][$titleKey] = '';
        $this->attachmentForms[$taskId][$urlKey] = '';
        $this->publishErrors[$taskId] = [];
        $this->syncBoardState();
    }

    private function attachmentRemovalWouldBreakActiveContent(DifferentiatedTaskAttachment $attachment): bool
    {
        $attachment->loadMissing('versionSelections.version.task');

        foreach ($attachment->versionSelections as $selection) {
            $version = $selection->version;

            if (! $version) {
                continue;
            }

            $version->loadMissing(['task', 'selectedAttachments']);
            $isGuarded = $version->task?->isActive() || $version->studentAssignments()->openEnded()->exists();
            $selectedCount = $version->selectedAttachments->count();

            if ($isGuarded && blank($version->description) && $selectedCount <= 1) {
                return true;
            }
        }

        return false;
    }

    private function resolveOwnedTaskOrFail(int $taskId): DifferentiatedTask
    {
        return $this->ownedTaskQuery()
            ->whereKey($taskId)
            ->firstOrFail();
    }

    private function resolveOwnedVersionOrFail(int $versionId): DifferentiatedTaskVersion
    {
        return DifferentiatedTaskVersion::query()
            ->whereKey($versionId)
            ->whereHas('task', fn ($query) => $query
                ->where('subject_id', $this->subjectId)
                ->where('created_by_user_id', Auth::id()))
            ->firstOrFail();
    }

    private function resolveOwnedAttachmentOrFail(int $attachmentId): DifferentiatedTaskAttachment
    {
        return DifferentiatedTaskAttachment::query()
            ->whereKey($attachmentId)
            ->whereHas('task', fn ($query) => $query
                ->where('subject_id', $this->subjectId)
                ->where('created_by_user_id', Auth::id()))
            ->firstOrFail();
    }

    private function generatedSnapshotCountForTask(int $taskId): int
    {
        return ClassSession::query()
            ->where('differentiated_task_id', $taskId)
            ->count();
    }

    private function generatedSnapshotCountForVersion(int $versionId): int
    {
        return DB::table('session_tasks')
            ->where('source_differentiated_task_version_id_snapshot', $versionId)
            ->count();
    }

    private function normalizeWeekdaysToNumeric(array $weekdays): array
    {
        $map = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];

        return collect($weekdays)
            ->map(fn (string $day): int => $map[strtolower($day)] ?? (int) $day)
            ->unique()
            ->values()
            ->all();
    }

    private function numericWeekdaysToTextKeys(array $days): array
    {
        $numericToText = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
        $knownTextKeys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

        return collect($days)
            ->filter(fn (string $day): bool => $day !== '')
            ->map(function (string $day) use ($numericToText, $knownTextKeys): string {
                $lower = strtolower($day);

                if (in_array($lower, $knownTextKeys, true)) {
                    return $lower;
                }

                return $numericToText[(int) $day] ?? $day;
            })
            ->values()
            ->all();
    }

    private function cleanNullableText(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function normalizeTaskScope(string $scope): string
    {
        return in_array($scope, ['working', 'archived'], true) ? $scope : 'working';
    }

    public function scopeUrl(string $scope): string
    {
        return $this->boardUrl.'?dt_scope='.$this->normalizeTaskScope($scope);
    }
}
