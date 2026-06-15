<?php

namespace App\Livewire\Teacher;

use App\Models\ClassSession;
use App\Models\GeneralLibraryFolder;
use App\Models\GeneralLibraryResource;
use App\Models\SeriesTask;
use App\Models\SeriesTaskVersion;
use App\Models\SeriesTaskVersionItem;
use App\Models\Subject;
use App\Models\TaskType;
use App\Models\TeacherSubjectClass;
use App\Models\VocabularyGameAssignment;
use App\Services\Library\GeneralLibraryAccessService;
use App\Services\SeriesLibrarySourceResolver;
use App\Services\SeriesTaskPublishValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class SeriesTasksBoard extends Component
{
    private const LEGACY_COLLECTION_PICKER_TYPE = '__legacy';

    public int $subjectId;

    public string $subjectName = 'Subject';

    public string $taskScope = 'working';

    public string $boardUrl = '';

    public bool $seriesReleasePolicyEnabled = false;

    private ?bool $releasePolicyColumnExists = null;

    /** @var array{message: string, tone: string}|null */
    public ?array $boardFeedback = null;

    public bool $createTaskOpen = false;

    public ?string $collectionPickerTarget = null;

    public ?string $collectionPickerType = null;

    public ?int $libraryPickerSectionId = null;

    /** @var array<string, mixed>|null */
    public ?array $libraryPickerCurrentSection = null;

    public ?int $vocabularyPickerParentId = null;

    /** @var array<string, mixed>|null */
    public ?array $vocabularyPickerCurrentFolder = null;

    public string $collectionSearch = '';

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
        'sequence_behavior' => 'stop_at_end',
        'release_policy' => 'continuous',
        'collection_key' => '',
        'vocabulary_allowed_games' => ['hangman', 'missing_letter', 'spelling_choice'],
        'vocabulary_difficulty_policy' => 'student_choice',
    ];

    /** @var array<int, array<string, mixed>> */
    public array $taskForms = [];

    /** @var array<int, array<string, mixed>> */
    public array $versionForms = [];

    /** @var array<int, mixed> */
    public array $itemSelections = [];

    /** @var array<int, string> */
    public array $itemSearches = [];

    /** @var array<int, array<int, string>> */
    public array $publishErrors = [];

    /** @var array<int, true> */
    public array $expandedTasks = [];

    /** @var array<int, true> */
    public array $expandedVersions = [];

    /** @var array<int, true> */
    public array $settingsOpen = [];

    /** @var array<int, true> */
    public array $versionEditorsOpen = [];

    /** @var array<int, array<string, mixed>> */
    public array $taskTypes = [];

    /** @var array<int, array<string, mixed>> */
    public array $collections = [];

    public function mount(int $subjectId): void
    {
        $sourceResolver = app(SeriesLibrarySourceResolver::class);
        $this->subjectId = $this->ensureOwnedSubjectOrFail($subjectId);
        $this->seriesReleasePolicyEnabled = $this->releasePolicyColumnExists();
        $this->subjectName = Subject::query()->whereKey($this->subjectId)->value('title') ?: 'Subject';
        $this->boardUrl = request()->url();
        $this->taskScope = $this->normalizeTaskScope((string) request()->query('series_scope', $this->taskScope));
        $this->boardFeedback = Session::pull('series_task_board_feedback');
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
        $this->collections = $this->collectionOptions($sourceResolver);
        $this->syncBoardState();
    }

    public function setTaskScope(string $scope): void
    {
        $this->taskScope = $this->normalizeTaskScope($scope);
        $this->boardFeedback = null;
        $this->syncBoardState();
    }

    #[On('series-task-assignment-saved')]
    public function refreshBoard(): void
    {
        $this->boardFeedback = null;
        $this->syncBoardState();
    }

    public function toggleCreateTaskForm(): void
    {
        $this->createTaskOpen = ! $this->createTaskOpen;
    }

    public function openCollectionPicker(string $target): void
    {
        $this->collectionPickerTarget = $target;
        $this->collectionPickerType = null;
        $this->libraryPickerSectionId = null;
        $this->vocabularyPickerParentId = null;
        $this->vocabularyPickerCurrentFolder = null;
        $this->collectionSearch = '';
        $this->refreshCollectionOptions();
    }

    public function closeCollectionPicker(): void
    {
        $this->collectionPickerTarget = null;
        $this->collectionPickerType = null;
        $this->libraryPickerSectionId = null;
        $this->libraryPickerCurrentSection = null;
        $this->vocabularyPickerParentId = null;
        $this->vocabularyPickerCurrentFolder = null;
        $this->collectionSearch = '';
    }

    public function enterCollectionType(string $type): void
    {
        if ($type === SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER) {
            return;
        }

        $hasType = collect($this->collections)->contains(fn (array $collection): bool => $collection['type'] === $type);

        if (! $hasType) {
            return;
        }

        $this->collectionPickerType = $type;
        $this->vocabularyPickerParentId = null;
        $this->vocabularyPickerCurrentFolder = null;
        $this->collectionSearch = '';
    }

    public function enterLegacyCollectionTypes(): void
    {
        $this->collectionPickerType = self::LEGACY_COLLECTION_PICKER_TYPE;
        $this->libraryPickerSectionId = null;
        $this->libraryPickerCurrentSection = null;
        $this->vocabularyPickerParentId = null;
        $this->vocabularyPickerCurrentFolder = null;
        $this->collectionSearch = '';
        $this->refreshCollectionOptions();
    }

    public function enterLibrarySection(int $sectionId): void
    {
        $section = collect($this->collections)->firstWhere(
            'key',
            $this->collectionKey(SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER, $sectionId)
        );

        if (! is_array($section)) {
            return;
        }

        $this->collectionPickerType = SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER;
        $this->libraryPickerSectionId = $sectionId;
        $this->collectionSearch = '';
        $this->refreshCollectionOptions();
    }

    public function enterVocabularyFolder(int $folderId): void
    {
        $folder = collect($this->collections)->firstWhere(
            'key',
            $this->collectionKey(SeriesLibrarySourceResolver::TYPE_VOCABULARY, $folderId)
        );

        if (! is_array($folder) || (int) ($folder['child_folder_count'] ?? 0) < 1) {
            return;
        }

        $this->collectionPickerType = SeriesLibrarySourceResolver::TYPE_VOCABULARY;
        $this->vocabularyPickerParentId = $folderId;
        $this->vocabularyPickerCurrentFolder = $folder;
        $this->collectionSearch = '';
    }

    public function goToCollectionTypes(): void
    {
        if ($this->collectionPickerType === SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER) {
            $parentId = $this->libraryPickerCurrentSection['parent_id'] ?? null;
            $this->libraryPickerSectionId = $parentId === null ? null : (int) $parentId;
            $this->collectionPickerType = $this->libraryPickerSectionId === null
                ? null
                : SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER;
            $this->collectionSearch = '';
            $this->refreshCollectionOptions();

            return;
        }

        if ($this->collectionPickerType === SeriesLibrarySourceResolver::TYPE_VOCABULARY) {
            $parentId = $this->vocabularyPickerCurrentFolder['parent_id'] ?? null;

            if ($this->vocabularyPickerParentId !== null) {
                $this->vocabularyPickerParentId = $parentId === null ? null : (int) $parentId;
                $this->vocabularyPickerCurrentFolder = $this->vocabularyPickerParentId === null
                    ? null
                    : $this->collectionSummary(
                        SeriesLibrarySourceResolver::TYPE_VOCABULARY,
                        $this->vocabularyPickerParentId
                    );
                $this->collectionSearch = '';

                return;
            }

            $this->collectionPickerType = self::LEGACY_COLLECTION_PICKER_TYPE;
            $this->vocabularyPickerParentId = null;
            $this->vocabularyPickerCurrentFolder = null;
            $this->collectionSearch = '';

            return;
        }

        $this->collectionPickerType = $this->collectionPickerType === self::LEGACY_COLLECTION_PICKER_TYPE
            ? null
            : self::LEGACY_COLLECTION_PICKER_TYPE;
        $this->libraryPickerSectionId = null;
        $this->libraryPickerCurrentSection = null;
        $this->vocabularyPickerParentId = null;
        $this->vocabularyPickerCurrentFolder = null;
        $this->collectionSearch = '';
        $this->refreshCollectionOptions();
    }

    public function clearCollectionSelection(): void
    {
        if ($this->collectionPickerTarget === 'draft') {
            $this->draftTask['collection_key'] = '';
            $this->closeCollectionPicker();

            return;
        }

        if (str_starts_with((string) $this->collectionPickerTarget, 'task:')) {
            $taskId = (int) str_replace('task:', '', (string) $this->collectionPickerTarget);
            $task = $this->resolveOwnedTaskOrFail($taskId);

            if (! $task->isActive() && ! $task->isArchived()) {
                $this->taskForms[$taskId]['collection_key'] = '';
                $this->keepTaskExpanded($taskId);
            }
        }

        $this->closeCollectionPicker();
    }

    public function chooseCollection(string $collectionKey): void
    {
        [$collectionType, $collectionId] = $this->parseCollectionKey($collectionKey);

        if (! app(SeriesLibrarySourceResolver::class)->sourceIsSelectableForSeriesLaunch(
            $collectionType,
            $collectionId,
            (int) Auth::id(),
            $this->subjectId
        )) {
            return;
        }

        if ($this->collectionPickerTarget === 'draft') {
            $this->draftTask['collection_key'] = $collectionKey;
            $this->closeCollectionPicker();

            return;
        }

        if (str_starts_with((string) $this->collectionPickerTarget, 'task:')) {
            $taskId = (int) str_replace('task:', '', (string) $this->collectionPickerTarget);
            $task = $this->resolveOwnedTaskOrFail($taskId);

            if (! $task->isActive() && ! $task->isArchived()) {
                $this->taskForms[$taskId]['collection_key'] = $collectionKey;
                $this->keepTaskExpanded($taskId);
            }
        }

        $this->closeCollectionPicker();
    }

    public function toggleTask(int $taskId): void
    {
        if (isset($this->expandedTasks[$taskId])) {
            unset($this->expandedTasks[$taskId]);

            return;
        }

        $this->expandedTasks[$taskId] = true;
    }

    public function toggleVersion(int $versionId): void
    {
        if (isset($this->expandedVersions[$versionId])) {
            unset($this->expandedVersions[$versionId]);

            return;
        }

        $this->expandedVersions[$versionId] = true;
    }

    public function createTask(): void
    {
        $this->seriesReleasePolicyEnabled = $this->releasePolicyColumnExists();
        $payload = $this->validatedTaskPayload($this->draftTask);

        DB::transaction(function () use ($payload): void {
            $task = SeriesTask::create($payload + [
                'subject_id' => $this->subjectId,
                'created_by_user_id' => Auth::id(),
                'status' => 'draft',
                'published_at' => null,
            ]);

            SeriesTaskVersion::create([
                'series_task_id' => $task->id,
                'display_name' => 'Pathway 1',
                'sort_order' => 1,
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
            'sequence_behavior' => 'stop_at_end',
            'release_policy' => 'continuous',
            'collection_key' => '',
            'vocabulary_allowed_games' => ['hangman', 'missing_letter', 'spelling_choice'],
            'vocabulary_difficulty_policy' => 'student_choice',
        ];
        $this->createTaskOpen = false;
        $this->syncBoardState();
    }

    public function saveTask(int $taskId): void
    {
        $this->seriesReleasePolicyEnabled = $this->releasePolicyColumnExists();
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $this->keepTaskExpanded($taskId);

        if ($task->isArchived()) {
            $this->taskForms[$taskId] = $this->taskFormFromModel($task);

            return;
        }

        $input = $this->taskForms[$taskId] ?? [];

        if ($task->isActive()) {
            $input['collection_key'] = $this->collectionKey(
                $task->library_collection_type,
                $task->library_collection_id
            );
        }

        $previousCollectionKey = $this->collectionKey($task->library_collection_type, $task->library_collection_id);
        $payload = $this->validatedTaskPayload($input);
        $task->update($payload);

        if ($previousCollectionKey !== $this->collectionKey($payload['library_collection_type'], $payload['library_collection_id'])) {
            foreach ($task->versions()->pluck('id') as $versionId) {
                unset($this->itemSelections[(int) $versionId], $this->itemSearches[(int) $versionId]);
            }
        }

        unset($this->settingsOpen[$taskId]);
        $this->publishErrors[$taskId] = [];
        $this->syncBoardState();
    }

    public function addVersion(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $this->keepTaskExpanded((int) $task->id);
        $nextDisplayNumber = ((int) $task->versions()->count()) + 1;

        $version = DB::transaction(function () use ($task, $nextDisplayNumber): SeriesTaskVersion {
            SeriesTaskVersion::query()
                ->where('series_task_id', $task->id)
                ->increment('sort_order');

            return SeriesTaskVersion::create([
                'series_task_id' => $task->id,
                'display_name' => 'Pathway '.$nextDisplayNumber,
                'sort_order' => 1,
            ]);
        });

        $this->versionEditorsOpen[$version->id] = true;
        $this->expandedTasks[$task->id] = true;
        $this->expandedVersions[$version->id] = true;
        $this->syncBoardState();
    }

    public function saveVersion(int $versionId): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId)->loadMissing('task');
        $this->keepTaskExpanded((int) $version->series_task_id);
        $payload = $this->validatedVersionPayload($version, $this->versionForms[$versionId] ?? []);
        $version->update($payload);
        unset($this->versionEditorsOpen[$versionId]);
        $this->publishErrors[(int) $version->series_task_id] = [];
        $this->syncBoardState();
    }

    public function deleteVersion(int $versionId): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId)->loadMissing('task');
        $taskId = (int) $version->series_task_id;
        $sortOrder = (int) $version->sort_order;

        $this->keepTaskExpanded($taskId);

        if ($version->task?->isArchived()) {
            $this->boardFeedback = [
                'message' => 'Restore this Series Task before deleting a pathway.',
                'tone' => 'warning',
            ];

            return;
        }

        $result = DB::transaction(function () use ($versionId, $taskId, $sortOrder): array {
            $lockedVersion = SeriesTaskVersion::query()
                ->whereKey($versionId)
                ->where('series_task_id', $taskId)
                ->lockForUpdate()
                ->firstOrFail();

            $now = now(config('app.timezone', 'Africa/Cairo'));
            $today = $now->toDateString();

            $assignments = DB::table('series_task_student_assignments')
                ->where('version_id', $versionId)
                ->lockForUpdate()
                ->get();
            $stateIds = DB::table('series_task_student_generation_states')
                ->where('series_task_id', $taskId)
                ->where('current_version_id', $versionId)
                ->lockForUpdate()
                ->pluck('id');

            if ($assignments->isNotEmpty()) {
                DB::table('series_task_student_assignment_history')->insert($assignments->map(function ($assignment) use ($lockedVersion, $now): array {
                    return [
                        'student_id' => (int) $assignment->student_id,
                        'series_task_id' => (int) $assignment->series_task_id,
                        'event_type' => 'version_deleted',
                        'from_version_id' => (int) $lockedVersion->id,
                        'from_version_display_name' => (string) $lockedVersion->display_name,
                        'to_version_id' => null,
                        'to_version_display_name' => null,
                        'from_sequence_position' => (int) ($assignment->start_sequence_position ?? 1),
                        'to_sequence_position' => null,
                        'actor_user_id' => (int) Auth::id(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all());

                DB::table('series_task_student_assignments')
                    ->where('version_id', $versionId)
                    ->delete();
            }

            if ($stateIds->isNotEmpty()) {
                DB::table('series_task_student_generation_states')
                    ->whereIn('id', $stateIds)
                    ->update([
                        'current_version_id' => null,
                        'is_active' => 0,
                        'end_date' => $today,
                        'updated_at' => $now,
                    ]);
            }

            $lockedVersion->items()->delete();
            $lockedVersion->delete();

            SeriesTaskVersion::query()
                ->where('series_task_id', $taskId)
                ->where('sort_order', '>', $sortOrder)
                ->decrement('sort_order');

            return [
                'assignment_count' => $assignments->count(),
                'state_count' => $stateIds->count(),
            ];
        });

        unset($this->versionEditorsOpen[$versionId], $this->expandedVersions[$versionId], $this->versionForms[$versionId], $this->itemSelections[$versionId], $this->itemSearches[$versionId]);
        $this->publishErrors[$taskId] = [];
        $this->boardFeedback = [
            'message' => ($result['assignment_count'] > 0 || $result['state_count'] > 0)
                ? 'Pathway deleted. Assigned students will stop receiving future tasks from it.'
                : 'Pathway deleted.',
            'tone' => 'success',
        ];
        $this->syncBoardState();
    }

    public function addItemToVersion(int $versionId): void
    {
        $selection = $this->itemSelections[$versionId] ?? '';

        if (is_array($selection)) {
            $this->addItemsToVersion($versionId);

            return;
        }

        $this->addSingleItemToVersion($versionId, (string) $selection);
    }

    public function addItemsToVersion(int $versionId): void
    {
        $this->syncVersionItems($versionId);
    }

    public function syncVersionItems(int $versionId): void
    {
        $selectedItemKeys = collect($this->itemSelections[$versionId] ?? [])
            ->filter(fn ($selected): bool => $this->truthy($selected))
            ->keys()
            ->map(fn ($key): string => (string) $key)
            ->values()
            ->all();

        $sourceResolver = app(SeriesLibrarySourceResolver::class);
        $version = $this->resolveOwnedVersionOrFail($versionId)->loadMissing('task', 'items');
        $task = $version->task;
        abort_if(! $task || $task->isArchived(), 404);
        $this->keepTaskExpanded((int) $task->id);
        $this->expandedVersions[$versionId] = true;

        $availableItems = collect($sourceResolver->orderedItems(
            (string) $task->library_collection_type,
            $task->library_collection_id === null ? null : (int) $task->library_collection_id,
            (int) Auth::id(),
            $this->subjectId
        ))->keyBy(fn ($item): string => $this->itemKey($item->sourceType, $item->sourceId));

        $selectedItemKeys = collect($selectedItemKeys)
            ->filter(fn (string $key): bool => $availableItems->has($key))
            ->values()
            ->all();

        DB::transaction(function () use ($version, $availableItems, $selectedItemKeys): void {
            $currentItems = $version->items()
                ->get()
                ->keyBy(fn (SeriesTaskVersionItem $item): string => $this->itemKey(
                    (string) $item->library_source_type,
                    (int) $item->library_source_id
                ));

            foreach ($currentItems as $itemKey => $currentItem) {
                if (! in_array($itemKey, $selectedItemKeys, true)) {
                    $currentItem->delete();
                }
            }

            foreach ($selectedItemKeys as $index => $itemKey) {
                $currentItem = $currentItems->get($itemKey);

                if ($currentItem) {
                    $currentItem->update([
                        'sequence_position' => 60000 + $index,
                        'is_active' => 1,
                    ]);
                }
            }

            foreach ($selectedItemKeys as $index => $itemKey) {
                $item = $availableItems->get($itemKey);

                if (! $item || ! $this->seriesItemHasSafeDeliveryTarget($item)) {
                    continue;
                }

                [$sourceType, $sourceId] = $this->parseItemKey($itemKey);

                $version->items()->updateOrCreate(
                    [
                        'library_source_type' => $sourceType,
                        'library_source_id' => $sourceId,
                    ],
                    [
                        'library_title_snapshot' => $item->title,
                        'library_url_snapshot' => $item->url,
                        'library_summary_snapshot' => $item->summary,
                        'sequence_position' => $index + 1,
                        'is_active' => 1,
                    ]
                );
            }
        });

        $this->itemSelections[$versionId] = $selectedItemKeys === []
            ? $availableItems->mapWithKeys(fn ($item): array => [$this->itemKey($item->sourceType, $item->sourceId) => false])->all()
            : $this->defaultItemSelectionForVersion($task, $version->fresh('items'));
        $this->publishErrors[(int) $task->id] = [];
        $this->syncBoardState();
    }

    public function selectAllVersionItems(int $versionId): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId)->loadMissing('task');
        $task = $version->task;
        abort_if(! $task || $task->isArchived(), 404);
        $this->keepTaskExpanded((int) $task->id);
        $this->expandedVersions[$versionId] = true;

        $this->itemSelections[$versionId] = collect(app(SeriesLibrarySourceResolver::class)->orderedItems(
            (string) $task->library_collection_type,
            $task->library_collection_id === null ? null : (int) $task->library_collection_id,
            (int) Auth::id(),
            $this->subjectId
        ))
            ->mapWithKeys(fn ($item): array => [$this->itemKey($item->sourceType, $item->sourceId) => true])
            ->all();
    }

    public function clearVersionItems(int $versionId): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId)->loadMissing('task');
        $task = $version->task;
        abort_if(! $task || $task->isArchived(), 404);
        $this->keepTaskExpanded((int) $task->id);
        $this->expandedVersions[$versionId] = true;

        $this->itemSelections[$versionId] = collect(app(SeriesLibrarySourceResolver::class)->orderedItems(
            (string) $task->library_collection_type,
            $task->library_collection_id === null ? null : (int) $task->library_collection_id,
            (int) Auth::id(),
            $this->subjectId
        ))
            ->mapWithKeys(fn ($item): array => [$this->itemKey($item->sourceType, $item->sourceId) => false])
            ->all();
    }

    private function addSingleItemToVersion(int $versionId, string $itemKey, bool $syncAfterCreate = true): void
    {
        $sourceResolver = app(SeriesLibrarySourceResolver::class);
        $version = $this->resolveOwnedVersionOrFail($versionId)->loadMissing('task');
        $task = $version->task;
        abort_if(! $task || $task->isArchived(), 404);
        $this->keepTaskExpanded((int) $task->id);
        $this->expandedVersions[$versionId] = true;

        [$sourceType, $sourceId] = $this->parseItemKey($itemKey);
        $item = $sourceId ? $sourceResolver->resolveItem($sourceType, $sourceId, (int) Auth::id()) : null;

        if (! $item || ! $this->seriesItemHasSafeDeliveryTarget($item)) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('item', 'Choose a resolvable Library item.');
            })->validate();
        }

        if (! $sourceResolver->itemBelongsToCollection(
            (string) $task->library_collection_type,
            $task->library_collection_id === null ? null : (int) $task->library_collection_id,
            $sourceType,
            $sourceId
        )) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('item', 'Choose an item from this Series Task Library source.');
            })->validate();
        }

        $exists = $version->items()
            ->where('library_source_type', $item->sourceType)
            ->where('library_source_id', $item->sourceId)
            ->exists();

        if ($exists) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('item', 'This Library item is already selected for this pathway.');
            })->validate();
        }

        SeriesTaskVersionItem::create([
            'version_id' => $version->id,
            'library_source_type' => $item->sourceType,
            'library_source_id' => $item->sourceId,
            'library_title_snapshot' => $item->title,
            'library_url_snapshot' => $item->url,
            'library_summary_snapshot' => $item->summary,
            'sequence_position' => ((int) $version->items()->max('sequence_position')) + 1,
            'is_active' => 1,
        ]);

        $this->itemSelections[$versionId] = is_array($this->itemSelections[$versionId] ?? null) ? [] : '';
        $this->publishErrors[(int) $task->id] = [];

        if ($syncAfterCreate) {
            $this->syncBoardState();
        }
    }

    public function openAssignmentModal(int $taskId, int $versionId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId)->load(['versions.items']);
        $this->keepTaskExpanded($taskId);
        $version = $task->versions->firstWhere('id', $versionId);

        abort_unless($version !== null, 404);

        if ($task->isArchived()) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('assignment', 'Restore this Series Task before editing assignments.');
            })->validate();
        }

        if ($version->activeItemsCount() < 1) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('assignment', 'This pathway needs at least one active Library item before assigning students.');
            })->validate();
        }

        $this->dispatch('open-series-task-assignment-modal', taskId: $task->id, versionId: $version->id)
            ->to(SeriesTaskAssignmentModal::class);
    }

    public function publishTask(int $taskId): void
    {
        $validator = app(SeriesTaskPublishValidator::class);
        $task = $this->resolveOwnedTaskOrFail($taskId)->fresh(['versions.items']);
        $this->keepTaskExpanded($taskId);
        $result = $validator->validate($task);
        $this->publishErrors[$taskId] = $result->errors;

        if ($result->fails()) {
            $this->syncBoardState();

            return;
        }

        $task->update([
            'status' => 'active',
            'published_at' => now(config('app.timezone', 'Africa/Cairo')),
        ]);

        $assignmentCount = $task->studentAssignments()->openEnded()->count();
        $this->boardFeedback = $assignmentCount === 0
            ? [
                'message' => 'Series Task activated. No student work will generate until students are assigned.',
                'tone' => 'info',
            ]
            : [
                'message' => 'Series Task activated for scheduled generation.',
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
        Session::flash('series_task_board_feedback', [
            'message' => 'Series Task archived. Generated student work was left unchanged.',
            'tone' => 'warning',
        ]);

        $this->redirect($this->scopeUrl('archived'), navigate: false);
    }

    public function restoreTask(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $task->update(['status' => 'draft']);
        $this->publishErrors[$taskId] = [];
        Session::flash('series_task_board_feedback', [
            'message' => 'Series Task restored to draft.',
            'tone' => 'success',
        ]);

        $this->redirect($this->scopeUrl('working'), navigate: false);
    }

    public function render(): View
    {
        $sourceResolver = app(SeriesLibrarySourceResolver::class);
        $tasks = $this->ownedTaskQuery()
            ->with([
                'versions.items',
                'versions.studentAssignments',
                'studentAssignments',
            ])
            ->withCount('studentAssignments')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $this->primeForms($tasks);

        return view('livewire.teacher.series-tasks-board', [
            'tasks' => $tasks,
            'libraryItemsByTask' => $this->libraryItemsByTask($tasks, $sourceResolver),
            'sourceLabelsByTask' => $this->sourceLabelsByTask($tasks),
            'legacySourceWarningsByTask' => $this->legacySourceWarningsByTask($tasks),
            'collectionPickerState' => $this->collectionPickerState(),
        ]);
    }

    private function collectionPickerState(): array
    {
        $sourceSearch = mb_strtolower(trim($this->collectionSearch));
        $filteredSources = collect($this->collections)
            ->filter(function (array $collection) use ($sourceSearch): bool {
                if ($sourceSearch === '') {
                    return true;
                }

                $typeLabel = (string) ($collection['type_label'] ?? str_replace('_', ' ', (string) $collection['type']));

                return str_contains(mb_strtolower((string) $collection['title']), $sourceSearch)
                    || str_contains(mb_strtolower($typeLabel), $sourceSearch);
            })
            ->values();

        $localSourceCollections = $filteredSources
            ->filter(fn (array $collection): bool => $collection['type'] === SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER)
            ->values();

        $vocabularySourceGroup = $filteredSources
            ->filter(fn (array $collection): bool => $collection['type'] === SeriesLibrarySourceResolver::TYPE_VOCABULARY)
            ->values();

        $legacySourceGroups = $filteredSources
            ->filter(fn (array $collection): bool => ! in_array($collection['type'], [
                SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER,
                SeriesLibrarySourceResolver::TYPE_VOCABULARY,
            ], true))
            ->groupBy('type');

        $sourceGroups = $legacySourceGroups;

        if ($vocabularySourceGroup->isNotEmpty()) {
            $sourceGroups = $sourceGroups->put(SeriesLibrarySourceResolver::TYPE_VOCABULARY, $vocabularySourceGroup);
        }

        $selectedSourceGroup = ($this->collectionPickerType && $this->collectionPickerType !== self::LEGACY_COLLECTION_PICKER_TYPE)
            ? ($sourceGroups[$this->collectionPickerType] ?? collect())
            : collect();

        if ($this->collectionPickerType === SeriesLibrarySourceResolver::TYPE_VOCABULARY) {
            $selectedSourceGroup = $sourceSearch === ''
                ? $selectedSourceGroup
                    ->filter(fn (array $collection): bool => (int) ($collection['parent_id'] ?? 0) === (int) ($this->vocabularyPickerParentId ?? 0))
                    ->values()
                : $selectedSourceGroup->values();
        }

        return [
            'legacy_picker_type' => self::LEGACY_COLLECTION_PICKER_TYPE,
            'source_search' => $sourceSearch,
            'local_source_collections' => $localSourceCollections,
            'vocabulary_source_group' => $vocabularySourceGroup,
            'legacy_source_groups' => $legacySourceGroups,
            'selected_source_group' => $selectedSourceGroup,
            'selected_source_group_label' => $selectedSourceGroup->first()['type_label']
                ?? ($this->vocabularyPickerCurrentFolder['title'] ?? null)
                ?? ucwords(str_replace('_', ' ', (string) $this->collectionPickerType)),
        ];
    }

    private function syncBoardState(): void
    {
        $tasks = $this->ownedTaskQuery()->with(['versions.items'])->get();
        $this->primeForms($tasks);
    }

    private function ownedTaskQuery()
    {
        return SeriesTask::query()
            ->where('subject_id', $this->subjectId)
            ->where('created_by_user_id', Auth::id())
            ->when(
                $this->taskScope === 'archived',
                fn ($query) => $query->archived(),
                fn ($query) => $query->whereIn('status', ['draft', 'active'])
            );
    }

    private function primeForms($tasks): void
    {
        foreach ($tasks as $task) {
            $this->taskForms[$task->id] ??= $this->taskFormFromModel($task);

            foreach ($task->versions as $version) {
                $this->versionForms[$version->id] ??= $this->versionFormFromModel($version);
                $this->itemSelections[$version->id] ??= $this->defaultItemSelectionForVersion($task, $version);
                $this->itemSearches[$version->id] ??= '';
            }
        }
    }

    private function defaultItemSelectionForVersion(SeriesTask $task, SeriesTaskVersion $version): array
    {
        $version->loadMissing('items');

        $availableItems = collect(app(SeriesLibrarySourceResolver::class)->orderedItems(
            (string) $task->library_collection_type,
            $task->library_collection_id === null ? null : (int) $task->library_collection_id,
            (int) Auth::id(),
            $this->subjectId
        ));
        $selectedKeys = $version->items
            ->map(fn (SeriesTaskVersionItem $item): string => $this->itemKey(
                (string) $item->library_source_type,
                (int) $item->library_source_id
            ))
            ->flip();
        $selectAllByDefault = $version->items->isEmpty();

        return $availableItems
            ->mapWithKeys(fn ($item): array => [
                $this->itemKey($item->sourceType, $item->sourceId) => $selectAllByDefault
                    || $selectedKeys->has($this->itemKey($item->sourceType, $item->sourceId)),
            ])
            ->all();
    }

    private function taskFormFromModel(SeriesTask $task): array
    {
        return [
            'title' => $task->title,
            'description' => $task->description,
            'task_type_id' => $task->task_type_id,
            'default_points' => $task->default_points,
            'max_points' => $task->max_points,
            'recurrence_kind' => $task->recurrence_kind,
            'recurrence_interval' => $task->recurrence_interval,
            'recurrence_weekdays' => $this->numericWeekdaysToTextKeys(explode(',', (string) $task->recurrence_weekdays)),
            'recurrence_day_of_month' => $task->recurrence_day_of_month,
            'sequence_behavior' => $task->sequence_behavior,
            'release_policy' => $this->releasePolicyColumnExists()
                ? (string) ($task->release_policy ?: 'continuous')
                : 'continuous',
            'collection_key' => $this->collectionKey($task->library_collection_type, $task->library_collection_id),
            'vocabulary_allowed_games' => $this->normalizeVocabularyAllowedGames($task->vocabulary_allowed_games ?? []),
            'vocabulary_difficulty_policy' => $this->normalizeVocabularyDifficultyPolicy($task->vocabulary_difficulty_policy),
        ];
    }

    private function versionFormFromModel(SeriesTaskVersion $version): array
    {
        return [
            'display_name' => $version->display_name,
            'description' => $version->description,
        ];
    }

    private function validatedTaskPayload(array $input): array
    {
        [$collectionType, $collectionId] = $this->parseCollectionKey((string) ($input['collection_key'] ?? ''));
        $releasePolicyEnabled = $this->releasePolicyColumnExists();
        $rules = [
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'task_type_id' => ['required', 'integer', Rule::exists('task_types', 'id')],
            'default_points' => ['required', 'integer', 'min:0', 'max:255'],
            'max_points' => ['required', 'integer', 'min:0', 'max:255'],
            'recurrence_kind' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:31'],
            'recurrence_weekdays' => ['array'],
            'recurrence_day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'sequence_behavior' => ['required', Rule::in(['stop_at_end', 'loop'])],
            'vocabulary_allowed_games' => ['array'],
            'vocabulary_allowed_games.*' => [Rule::in(['hangman', 'missing_letter', 'spelling_choice'])],
            'vocabulary_difficulty_policy' => [Rule::in([
                VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE,
                'sprout',
                'climber',
                'champion',
            ])],
        ];

        if ($releasePolicyEnabled) {
            $rules['release_policy'] = ['required', Rule::in(['continuous', 'wait_for_completion'])];
        }

        $validated = Validator::make($input, $rules)->validate();

        if ((int) $validated['default_points'] > (int) $validated['max_points']) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('default_points', 'Default points cannot be greater than max points.');
            })->validate();
        }

        if ($collectionType === '') {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('collection', 'Choose a Library source.');
            })->validate();
        }

        if (! app(SeriesLibrarySourceResolver::class)->sourceIsSelectableForSeriesLaunch(
            $collectionType,
            $collectionId,
            (int) Auth::id(),
            $this->subjectId
        )) {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add('collection', 'Choose a ready Library source.');
            })->validate();
        }

        $payload = [
            'title' => trim((string) $validated['title']),
            'description' => $this->cleanNullableText($validated['description'] ?? null),
            'task_type_id' => (int) $validated['task_type_id'],
            'default_points' => (int) $validated['default_points'],
            'max_points' => (int) $validated['max_points'],
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
            'sequence_behavior' => $validated['sequence_behavior'],
            'library_collection_type' => $collectionType,
            'library_collection_id' => $collectionId,
        ];

        if ($this->vocabularyPolicyColumnsExist()) {
            $isVocabularySeries = $collectionType === SeriesLibrarySourceResolver::TYPE_VOCABULARY;
            $payload['vocabulary_allowed_games'] = $isVocabularySeries
                ? $this->normalizeVocabularyAllowedGames($validated['vocabulary_allowed_games'] ?? [])
                : null;
            $payload['vocabulary_difficulty_policy'] = $isVocabularySeries
                ? $this->normalizeVocabularyDifficultyPolicy($validated['vocabulary_difficulty_policy'] ?? null)
                : null;
        }

        if ($releasePolicyEnabled) {
            $payload['release_policy'] = $validated['release_policy'] ?? 'continuous';
        }

        return $payload;
    }

    private function releasePolicyColumnExists(): bool
    {
        return $this->releasePolicyColumnExists ??= Schema::hasColumn('series_tasks', 'release_policy');
    }

    public function vocabularyPolicyColumnsExist(): bool
    {
        return Schema::hasColumn('series_tasks', 'vocabulary_allowed_games')
            && Schema::hasColumn('series_tasks', 'vocabulary_difficulty_policy');
    }

    public function vocabularyGameOptions(): array
    {
        return [
            'hangman' => 'Floatie',
            'missing_letter' => 'Missing Letter',
            'spelling_choice' => 'Correct Spelling',
        ];
    }

    public function vocabularyDifficultyPolicyOptions(): array
    {
        return [
            VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE => 'Student chooses',
            'sprout' => 'Sprout',
            'climber' => 'Climber',
            'champion' => 'Champion',
        ];
    }

    private function validatedVersionPayload(SeriesTaskVersion $version, array $input): array
    {
        $validated = Validator::make($input, [
            'display_name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('series_task_versions', 'display_name')
                    ->where('series_task_id', $version->series_task_id)
                    ->ignore($version->id),
            ],
            'description' => ['nullable', 'string'],
        ])->validate();

        return [
            'display_name' => trim((string) $validated['display_name']),
            'description' => $this->cleanNullableText($validated['description'] ?? null),
        ];
    }

    private function resolveOwnedTaskOrFail(int $taskId): SeriesTask
    {
        return $this->ownedTaskQuery()
            ->whereKey($taskId)
            ->firstOrFail();
    }

    private function resolveOwnedVersionOrFail(int $versionId): SeriesTaskVersion
    {
        return SeriesTaskVersion::query()
            ->whereKey($versionId)
            ->whereHas('task', fn ($query) => $query
                ->where('subject_id', $this->subjectId)
                ->where('created_by_user_id', Auth::id()))
            ->firstOrFail();
    }

    private function resolveOwnedItemOrFail(int $itemId): SeriesTaskVersionItem
    {
        return SeriesTaskVersionItem::query()
            ->whereKey($itemId)
            ->whereHas('version.task', fn ($query) => $query
                ->where('subject_id', $this->subjectId)
                ->where('created_by_user_id', Auth::id()))
            ->firstOrFail();
    }

    private function ensureOwnedSubjectOrFail(int $subjectId): int
    {
        TeacherSubjectClass::query()
            ->where('subject_id', $subjectId)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->firstOrFail();

        return $subjectId;
    }

    private function libraryItemsByTask($tasks, SeriesLibrarySourceResolver $sourceResolver): array
    {
        $items = [];

        foreach ($tasks as $task) {
            $items[$task->id] = collect($sourceResolver->orderedItems(
                (string) $task->library_collection_type,
                $task->library_collection_id === null ? null : (int) $task->library_collection_id,
                (int) Auth::id(),
                $this->subjectId
            ))
                ->map(fn ($item): array => [
                    'key' => $this->itemKey($item->sourceType, $item->sourceId),
                    'title' => $item->title,
                    'source_type' => $item->sourceType,
                    'url' => $item->url,
                ])
                ->all();
        }

        return $items;
    }

    private function sourceLabelsByTask($tasks): array
    {
        $librarySectionIds = $tasks
            ->filter(fn (SeriesTask $task): bool => (string) $task->library_collection_type === SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER)
            ->pluck('library_collection_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();
        $librarySectionTitles = $librarySectionIds->isEmpty()
            ? collect()
            : GeneralLibraryFolder::query()
                ->whereIn('id', $librarySectionIds->all())
                ->where('status', GeneralLibraryFolder::STATUS_ACTIVE)
                ->pluck('title', 'id');
        $labels = [];

        foreach ($tasks as $task) {
            $type = (string) $task->library_collection_type;

            $labels[(int) $task->id] = $type === SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER
                ? (string) ($librarySectionTitles[(int) $task->library_collection_id] ?? 'Shared Library folder')
                : $this->collectionTypeLabel($type);
        }

        return $labels;
    }

    private function legacySourceWarningsByTask($tasks): array
    {
        $warnings = [];

        foreach ($tasks as $task) {
            if ((string) $task->library_collection_type !== SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER) {
                $warnings[(int) $task->id] = $this->legacySourceWarningText($this->collectionTypeLabel((string) $task->library_collection_type));

                continue;
            }

            $legacyItemTypes = $task->versions
                ->flatMap(fn (SeriesTaskVersion $version) => $version->items)
                ->filter(fn (SeriesTaskVersionItem $item): bool => (string) $item->library_source_type !== SeriesLibrarySourceResolver::SOURCE_GENERAL_LIBRARY_RESOURCE)
                ->map(fn (SeriesTaskVersionItem $item): string => $this->collectionTypeLabel((string) $item->library_source_type))
                ->unique()
                ->values();

            if ($legacyItemTypes->isNotEmpty()) {
                $warnings[(int) $task->id] = $this->legacySourceWarningText($legacyItemTypes->implode(', '));
            }
        }

        return $warnings;
    }

    private function legacySourceWarningText(string $legacySourceLabel): string
    {
        return sprintf(
            '%s is an old Week14 source. New student work will stay paused until this Series Task is recreated with Shared Library sources.',
            $legacySourceLabel
        );
    }

    private function collectionOptions(SeriesLibrarySourceResolver $sourceResolver): array
    {
        return collect($sourceResolver->allCollections((int) Auth::id(), $this->subjectId, $this->libraryPickerSectionId))
            ->map(fn ($collection): array => [
                'key' => $this->collectionKey($collection->type, $collection->id),
                'title' => $collection->title,
                'type' => $collection->type,
                'type_label' => $this->collectionTypeLabel($collection->type),
                'description' => $collection->description,
                'selectable' => $collection->selectable,
                'blocked_reason' => $collection->blockedReason,
                'parent_id' => $collection->parentId,
                'direct_resource_count' => $collection->directResourceCount,
                'tree_resource_count' => $collection->treeResourceCount,
                'child_folder_count' => $collection->childFolderCount,
            ])
            ->all();
    }

    private function refreshCollectionOptions(): void
    {
        $this->collections = $this->collectionOptions(app(SeriesLibrarySourceResolver::class));
        $this->libraryPickerCurrentSection = $this->libraryPickerSectionId === null
            ? null
            : $this->librarySectionSummary($this->libraryPickerSectionId);
        $this->vocabularyPickerCurrentFolder = $this->vocabularyPickerParentId === null
            ? null
            : $this->collectionSummary(SeriesLibrarySourceResolver::TYPE_VOCABULARY, $this->vocabularyPickerParentId);
    }

    private function librarySectionSummary(int $sectionId): ?array
    {
        $section = $this->usableGeneralLibraryFolderQuery()
            ->whereKey($sectionId)
            ->first(['id', 'parent_id', 'title']);

        if (! $section instanceof GeneralLibraryFolder) {
            return null;
        }

        if (! app(GeneralLibraryAccessService::class)->canUseFolder(Auth::user(), $section)) {
            return null;
        }

        $directResourceCount = GeneralLibraryResource::query()
            ->where('general_library_folder_id', $section->id)
            ->where('status', GeneralLibraryResource::STATUS_ACTIVE)
            ->count();

        return [
            'id' => (int) $section->id,
            'parent_id' => $section->parent_id === null ? null : (int) $section->parent_id,
            'title' => (string) $section->title,
            'key' => $this->collectionKey(SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER, (int) $section->id),
            'selectable' => $directResourceCount > 0,
            'direct_resource_count' => $directResourceCount,
        ];
    }

    private function usableGeneralLibraryFolderQuery()
    {
        return GeneralLibraryFolder::query()
            ->where('status', GeneralLibraryFolder::STATUS_ACTIVE);
    }

    private function collectionTypeLabel(string $type): string
    {
        return match ($type) {
            SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER => 'Shared Library',
            SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION => 'My Library folders',
            SeriesLibrarySourceResolver::TYPE_VOCABULARY => 'Vocabulary',
            SeriesLibrarySourceResolver::TYPE_SAT => 'SAT',
            SeriesLibrarySourceResolver::TYPE_STORY => 'Literature',
            SeriesLibrarySourceResolver::TYPE_TV_SERIES => 'TV Series',
            SeriesLibrarySourceResolver::TYPE_LEVEL_UP => 'Level Up',
            SeriesLibrarySourceResolver::TYPE_AUDIO_LEVEL => 'Audio Level',
            SeriesLibrarySourceResolver::TYPE_PEER_COACH => 'Peer Coach',
            SeriesLibrarySourceResolver::TYPE_NOTICE_NOTE => 'Notice & Note',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }

    private function collectionSummary(string $type, int $id): ?array
    {
        $summary = collect($this->collections)->firstWhere('key', $this->collectionKey($type, $id));

        return is_array($summary) ? $summary : null;
    }

    private function parseCollectionKey(string $key): array
    {
        if (! str_contains($key, ':')) {
            return ['', null];
        }

        [$type, $id] = explode(':', $key, 2);

        return [$type, $id === '' ? null : (int) $id];
    }

    private function parseItemKey(string $key): array
    {
        if (! str_contains($key, ':')) {
            return ['', 0];
        }

        [$type, $id] = explode(':', $key, 2);

        return [$type, (int) $id];
    }

    private function collectionKey(?string $type, ?int $id): string
    {
        return (string) $type.':'.($id ?? '');
    }

    private function itemKey(string $type, int $id): string
    {
        return $type.':'.$id;
    }

    private function keepTaskExpanded(int $taskId): void
    {
        $this->expandedTasks[$taskId] = true;
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ?? false;
    }

    private function seriesItemHasSafeDeliveryTarget($item): bool
    {
        return $item->sourceType === SeriesLibrarySourceResolver::SOURCE_VOCABULARY_LIST
            || $item->hasSafeDeliveryTarget();
    }

    private function normalizeVocabularyAllowedGames(mixed $games): array
    {
        $allowed = collect(is_array($games) ? $games : [])
            ->map(fn ($game): string => (string) $game)
            ->filter(fn (string $game): bool => in_array($game, ['hangman', 'missing_letter', 'spelling_choice'], true))
            ->unique()
            ->values()
            ->all();

        return $allowed === [] ? ['hangman', 'missing_letter', 'spelling_choice'] : $allowed;
    }

    private function normalizeVocabularyDifficultyPolicy(mixed $policy): string
    {
        $policy = (string) ($policy ?: VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE);

        return in_array($policy, [
            VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE,
            'sprout',
            'climber',
            'champion',
        ], true)
            ? $policy
            : VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE;
    }

    private function renumberItems(int $versionId): void
    {
        $items = SeriesTaskVersionItem::query()
            ->where('version_id', $versionId)
            ->orderBy('sequence_position')
            ->orderBy('id')
            ->get();

        foreach ($items as $index => $item) {
            $item->update(['sequence_position' => $index + 1]);
        }
    }

    private function generatedSnapshotCountForTask(int $taskId): int
    {
        return ClassSession::query()
            ->where('series_task_id', $taskId)
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
        $knownTextKeys = ['sun', 'tue', 'wed', 'thu', 'fri', 'sat', 'mon'];

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
        return $this->boardUrl.'?series_scope='.$this->normalizeTaskScope($scope);
    }
}
