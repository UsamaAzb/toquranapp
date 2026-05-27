<?php

namespace App\Livewire\Teacher;

use App\Models\Cambradge_word_api;
use App\Models\Category_group_word;
use App\Models\Group_word;
use App\Models\Student;
use App\Models\TeacherSubjectClass;
use App\Models\VocabularyGameAssignment;
use App\Models\VocabularySet;
use App\Models\VocabularySetWord;
use App\Models\VocabularySourceAccess;
use App\Services\Vocabulary\CambridgeAudioDownloadService;
use App\Services\Vocabulary\LegacyHangmanImportService;
use App\Services\Vocabulary\VocabularyAudioResolver;
use App\Services\Vocabulary\VocabularyDifficultyEstimator;
use App\Services\Vocabulary\VocabularySourceRegistry;
use App\Services\Vocabulary\VocabularyWordProvider;
use App\Services\Vocabulary\WrongOptionGenerator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class VocabularyManager extends Component
{
    use WithFileUploads;

    public string $setTitle = '';

    public string $setDescription = '';

    public string $setVisibility = VocabularySet::VISIBILITY_PRIVATE;

    public string $setNodeType = VocabularySet::NODE_FOLDER;

    public ?int $setParentId = null;

    #[Url(as: 'set')]
    public ?int $selectedSetId = null;

    #[Url(as: 'mode')]
    public string $viewMode = 'landing';

    public bool $setEditorOpen = false;

    public string $editSetTitle = '';

    public string $editSetDescription = '';

    public string $wordSearch = '';

    public string $wordBankDifficultyFilter = '';

    public string $wordBankGroupFilter = '';

    public string $sourceSearch = '';

    public ?int $wrongOptionsWordId = null;

    public string $wrongOptionsText = '';

    public ?int $audioWordId = null;

    public string $audioInputMode = 'cambridge';

    public string $audioPartialPath = '';

    public string $audioCompleteUrl = '';

    public $audioUpload = null;

    public string $newWordText = '';

    public string $newWordAudioMode = 'cambridge';

    public string $newWordAudioPartialPath = '';

    public string $newWordAudioCompleteUrl = '';

    public $newWordAudioUpload = null;

    public bool $newWordWithoutAudio = false;

    public ?string $newWordFeedback = null;

    public ?int $imageWordId = null;

    public ?string $imagePreviewUrl = null;

    public $wordImageUpload = null;

    public bool $imageEditorOpen = false;

    public ?int $groupWordId = null;

    /** @var array<int, string> */
    public array $wordGroupIds = [];

    public bool $groupEditorOpen = false;

    public bool $wordPickerOpen = false;

    public bool $wrongOptionsOpen = false;

    public bool $audioReplacementOpen = false;

    public ?int $accessTeacherSubjectClassId = null;

    public string $accessAudienceType = VocabularySourceAccess::AUDIENCE_CLASS;

    public ?int $accessStudentId = null;

    public bool $legacyReportOpen = false;

    public bool $setCreatorOpen = false;

    public bool $accessManagerOpen = false;

    public ?int $accessSetId = null;

    public string $accessClassSearch = '';

    /** @var array<int, bool> */
    public array $accessClassSelections = [];

    public int $accessClassDisplayLimit = 60;

    public int $accessManagerInstance = 0;

    public ?int $groupEditorCategoryId = null;

    public string $groupEditorName = '';

    public bool $wordGroupManagerOpen = false;

    public bool $listWordEditMode = false;

    public int $wordBankLimit = 50;

    public int $sourceWordLimit = 50;

    public bool $bulkPreviewOpen = false;

    public string $bulkAction = '';

    /** @var array<string, mixed> */
    public array $bulkPreview = [];

    /** @var array<int, bool> */
    public array $bulkSelectedWordIds = [];

    public int $bulkBatchPage = 0;

    public ?int $lastWrongOptionsRefreshWordId = null;

    public string $lastWrongOptionsRefreshMessage = '';

    /** @var array<int, bool> */
    public array $editableListWordIds = [];

    /** @var array<int, bool> */
    public array $pickerSelectedWordIds = [];

    protected array $validationAttributes = [
        'setTitle' => 'folder title',
        'setDescription' => 'description',
        'setVisibility' => 'visibility',
        'setNodeType' => 'item type',
        'setParentId' => 'parent folder',
        'wordSearch' => 'word search',
        'wordBankDifficultyFilter' => 'word difficulty filter',
        'wordBankGroupFilter' => 'word group filter',
        'wrongOptionsText' => 'wrong spelling suggestions',
        'audioInputMode' => 'audio source',
        'audioPartialPath' => 'dictionary media path',
        'audioCompleteUrl' => 'complete audio URL',
        'audioUpload' => 'audio upload',
        'newWordText' => 'new word',
        'newWordAudioMode' => 'audio source',
        'newWordAudioPartialPath' => 'dictionary media path',
        'newWordAudioCompleteUrl' => 'complete audio URL',
        'newWordAudioUpload' => 'audio upload',
        'wordImageUpload' => 'word image',
        'wordGroupIds' => 'word groups',
        'groupEditorName' => 'word group name',
    ];

    public function mount(?int $set = null): void
    {
        $querySet = request()->integer('set') ?: null;
        $queryMode = request()->query('mode');

        if (is_string($queryMode) && in_array($queryMode, ['landing', 'word_bank', 'source'], true)) {
            $this->viewMode = $queryMode;
        }

        if (($set !== null || $querySet !== null) && $this->schemaReady()) {
            $this->selectedSetId = $set ?? $querySet;
            $this->viewMode = 'source';
        }
    }

    public function showLanding(): void
    {
        $this->viewMode = 'landing';
        $this->selectedSetId = null;
        $this->wordPickerOpen = false;
        $this->setEditorOpen = false;
        $this->setCreatorOpen = false;
        $this->sourceSearch = '';
        $this->listWordEditMode = false;
        $this->resetValidation();
    }

    public function showWordBank(): void
    {
        $this->viewMode = 'word_bank';
        $this->selectedSetId = null;
        $this->wordPickerOpen = false;
        $this->setEditorOpen = false;
        $this->setCreatorOpen = false;
        $this->sourceSearch = '';
        $this->listWordEditMode = false;
        $this->resetValidation();
    }

    public function openSourceRoot(int $setId): void
    {
        $this->selectSet($setId);
    }

    public function openSetCreator(?int $parentId = null, string $nodeType = VocabularySet::NODE_FOLDER): void
    {
        $this->setParentId = $parentId;
        $this->setNodeType = in_array($nodeType, [VocabularySet::NODE_FOLDER, VocabularySet::NODE_PLAYABLE], true)
            ? $nodeType
            : VocabularySet::NODE_FOLDER;
        $this->setCreatorOpen = true;
        $this->resetValidation();
    }

    public function createSet(): void
    {
        $this->requireSchema();

        $payload = $this->validate([
            'setTitle' => ['required', 'string', 'max:120'],
            'setDescription' => ['nullable', 'string', 'max:300'],
            'setVisibility' => ['required', 'in:private,shared'],
            'setNodeType' => ['required', 'in:folder,playable'],
            'setParentId' => ['nullable', 'integer'],
        ]);
        $parent = $this->validatedParentForNewSet($payload['setParentId'] ?? null);
        $this->guardChildHomogeneity($parent, $payload['setNodeType']);

        $set = VocabularySet::query()->create([
            'title' => trim($payload['setTitle']),
            'description' => $this->cleanText($payload['setDescription'] ?? null),
            'parent_id' => $parent?->id,
            'node_type' => $payload['setNodeType'],
            'set_type' => VocabularySet::TYPE_TEACHER,
            'source_kind' => VocabularySet::SOURCE_CUSTOM,
            'source_key' => null,
            'owner_user_id' => Auth::id(),
            'visibility' => $payload['setVisibility'],
            'sort_order' => $this->nextSetSortOrder($parent ? (int) $parent->id : null),
            'created_by_user_id' => Auth::id(),
            'updated_by_user_id' => Auth::id(),
        ]);

        $this->selectedSetId = (int) $set->id;
        $this->viewMode = 'source';
        $this->setCreatorOpen = false;
        $this->reset(['setTitle', 'setDescription']);
        $this->dispatch('toast', type: 'success', message: $set->isFolder()
            ? 'Vocabulary folder created.'
            : 'Vocabulary list created. Add words to make it playable.');
    }

    public function selectSet(int $setId): void
    {
        $this->resolveVisibleSet($setId);
        $this->selectedSetId = $setId;
        $this->viewMode = 'source';
        $this->setEditorOpen = false;
        $this->wordPickerOpen = false;
        $this->sourceSearch = '';
        $this->listWordEditMode = false;
        $this->sourceWordLimit = 50;
        $this->resetValidation();
    }

    public function updatedWordSearch(): void
    {
        $this->wordBankLimit = 50;
        $this->resetBulkPreview();
    }

    public function updatedWordBankDifficultyFilter(): void
    {
        $this->wordBankLimit = 50;
        $this->resetBulkPreview();
    }

    public function updatedWordBankGroupFilter(): void
    {
        $this->wordBankLimit = 50;
        $this->resetBulkPreview();
    }

    public function updatedSourceSearch(): void
    {
        $this->sourceWordLimit = 50;
    }

    public function loadMoreWordBank(): void
    {
        $this->wordBankLimit += 50;
    }

    public function loadMoreSourceWords(): void
    {
        $this->sourceWordLimit += 50;
    }

    public function openSetEditor(): void
    {
        $set = $this->requireSelectedEditableSet();
        $this->editSetTitle = (string) $set->title;
        $this->editSetDescription = (string) ($set->description ?? '');
        $this->setEditorOpen = true;
    }

    public function saveSetEditor(): void
    {
        $set = $this->requireSelectedEditableSet();
        $payload = $this->validate([
            'editSetTitle' => ['required', 'string', 'max:120'],
            'editSetDescription' => ['nullable', 'string', 'max:300'],
        ]);

        $set->forceFill([
            'title' => trim($payload['editSetTitle']),
            'description' => trim((string) ($payload['editSetDescription'] ?? '')) ?: null,
            'updated_by_user_id' => Auth::id(),
        ])->save();

        $this->setEditorOpen = false;
        $this->dispatch('toast', type: 'success', message: 'Vocabulary item updated.');
    }

    public function openWordPicker(): void
    {
        $this->pickerSelectedWordIds = [];
        $this->newWordFeedback = null;

        if ($this->selectedSetId !== null) {
            $set = $this->resolveVisibleSet((int) $this->selectedSetId);

            if ($set->source_kind === VocabularySet::SOURCE_CUSTOM && $set->isPlayable() && $this->canEditSet($set)) {
                $this->pickerSelectedWordIds = $set->words()
                    ->pluck('cambradge_words_api.id')
                    ->mapWithKeys(fn ($id): array => [(int) $id => true])
                    ->all();
            }
        }

        $this->wordPickerOpen = true;
    }

    public function closeWordPicker(): void
    {
        $this->wordPickerOpen = false;
        $this->pickerSelectedWordIds = [];
        $this->newWordFeedback = null;
    }

    public function startListWordEdit(): void
    {
        $set = $this->requireSelectedEditableList();
        $this->editableListWordIds = $set->words()
            ->pluck('cambradge_words_api.id')
            ->mapWithKeys(fn ($id): array => [(int) $id => true])
            ->all();
        $this->listWordEditMode = true;
    }

    public function cancelListWordEdit(): void
    {
        $this->listWordEditMode = false;
        $this->editableListWordIds = [];
    }

    public function saveListWordEdit(): void
    {
        $set = $this->requireSelectedEditableList();
        $keepIds = collect($this->editableListWordIds)
            ->filter(fn ($selected): bool => (bool) $selected)
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->values();

        VocabularySetWord::query()
            ->where('vocabulary_set_id', (int) $set->id)
            ->whereNotIn('word_id', $keepIds)
            ->delete();

        $this->reindexWords((int) $set->id);
        $this->cancelListWordEdit();
        $this->dispatch('toast', type: 'success', message: 'Vocabulary list updated.');
    }

    public function addWord(int $wordId): void
    {
        $set = $this->requireSelectedEditableList();
        $word = Cambradge_word_api::query()->findOrFail($wordId);

        VocabularySetWord::query()->firstOrCreate(
            [
                'vocabulary_set_id' => (int) $set->id,
                'word_id' => (int) $word->id,
            ],
            [
                'position' => $this->nextWordPosition((int) $set->id),
                'added_by_user_id' => Auth::id(),
            ]
        );

        $this->dispatch('toast', type: 'success', message: 'Word added to the folder.');
    }

    public function savePickerSelectedWords(): void
    {
        $set = $this->requireSelectedEditableList();
        $selectedIds = collect($this->pickerSelectedWordIds)
            ->filter(fn ($selected): bool => (bool) $selected)
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();
        $existingIds = VocabularySetWord::query()
            ->where('vocabulary_set_id', (int) $set->id)
            ->pluck('word_id')
            ->map(fn ($id): int => (int) $id);

        foreach ($selectedIds->diff($existingIds)->values() as $wordId) {
            VocabularySetWord::query()->create([
                'vocabulary_set_id' => (int) $set->id,
                'word_id' => (int) $wordId,
                'position' => $this->nextWordPosition((int) $set->id),
                'added_by_user_id' => Auth::id(),
            ]);
        }

        $this->wordPickerOpen = false;
        $this->pickerSelectedWordIds = [];
        $this->dispatch('toast', type: 'success', message: 'Selected words added to this list.');
    }

    public function removeWord(int $wordId): void
    {
        $set = $this->requireSelectedEditableList();

        VocabularySetWord::query()
            ->where('vocabulary_set_id', $set->id)
            ->where('word_id', $wordId)
            ->delete();

        $this->reindexWords((int) $set->id);
        $this->dispatch('toast', type: 'success', message: 'Word removed.');
    }

    public function moveWord(int $wordId, string $direction): void
    {
        $set = $this->requireSelectedEditableList();
        $memberships = VocabularySetWord::query()
            ->where('vocabulary_set_id', $set->id)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $currentIndex = $memberships->search(fn (VocabularySetWord $membership): bool => (int) $membership->word_id === $wordId);

        if ($currentIndex === false) {
            return;
        }

        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if (! $memberships->has($targetIndex)) {
            return;
        }

        $current = $memberships[$currentIndex];
        $target = $memberships[$targetIndex];
        $currentPosition = (int) $current->position;

        $current->forceFill(['position' => (int) $target->position])->save();
        $target->forceFill(['position' => $currentPosition])->save();
    }

    public function moveChildSet(int $setId, string $direction): void
    {
        $child = $this->requireEditableSet($setId);
        $siblings = VocabularySet::query()
            ->where('parent_id', $child->parent_id)
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $currentIndex = $siblings->search(fn (VocabularySet $set): bool => (int) $set->id === (int) $child->id);

        if ($currentIndex === false) {
            return;
        }

        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if (! $siblings->has($targetIndex)) {
            return;
        }

        $target = $siblings[$targetIndex];
        $currentOrder = (int) $child->sort_order;

        $child->forceFill([
            'sort_order' => (int) $target->sort_order,
            'updated_by_user_id' => Auth::id(),
        ])->save();
        $target->forceFill([
            'sort_order' => $currentOrder,
            'updated_by_user_id' => Auth::id(),
        ])->save();

        $this->dispatch('toast', type: 'success', message: 'Vocabulary item order updated.');
    }

    public function archiveSelectedSet(): void
    {
        $this->archiveSet((int) $this->selectedSetId);
    }

    public function archiveSet(int $setId): void
    {
        $set = $this->requireEditableSet($setId);
        $nextSelectedId = $set->parent_id ? (int) $set->parent_id : null;
        $set->forceFill([
            'visibility' => VocabularySet::VISIBILITY_ARCHIVED,
            'updated_by_user_id' => Auth::id(),
        ])->save();

        $this->selectedSetId = $nextSelectedId;
        $this->viewMode = $nextSelectedId ? 'source' : 'landing';
        $this->dispatch('toast', type: 'success', message: 'Vocabulary folder archived.');
    }

    public function deleteSet(int $setId): void
    {
        $set = $this->requireEditableSet($setId);
        abort_unless($set->source_kind === VocabularySet::SOURCE_CUSTOM, 403);

        $treeIds = $this->setTreeIds($set);
        $nextSelectedId = $set->parent_id ? (int) $set->parent_id : null;

        DB::transaction(function () use ($treeIds): void {
            VocabularySetWord::query()
                ->whereIn('vocabulary_set_id', $treeIds)
                ->delete();
            VocabularySourceAccess::query()
                ->whereIn('vocabulary_set_id', $treeIds)
                ->delete();
            VocabularyGameAssignment::query()
                ->whereIn('vocabulary_set_id', $treeIds)
                ->update(['status' => VocabularyGameAssignment::STATUS_ARCHIVED]);

            foreach (array_reverse($treeIds) as $treeId) {
                VocabularySet::query()->whereKey($treeId)->delete();
            }
        });

        if ($this->selectedSetId !== null && in_array((int) $this->selectedSetId, $treeIds, true)) {
            $this->selectedSetId = $nextSelectedId;
            $this->viewMode = $nextSelectedId ? 'source' : 'landing';
        }

        $this->dispatch('toast', type: 'success', message: 'Vocabulary copy deleted.');
    }

    public function openSetEditorFor(int $setId): void
    {
        $this->selectSet($setId);
        $this->openSetEditor();
    }

    public function enableSelectedSetForClass(): void
    {
        $this->requireAccessSchema();
        $set = $this->resolveVisibleSet((int) $this->selectedSetId);
        [$audienceType, $audienceId] = $this->resolveAccessAudience();

        VocabularySourceAccess::query()->updateOrCreate(
            [
                'vocabulary_set_id' => (int) $set->id,
                'audience_type' => $audienceType,
                'audience_id' => $audienceId,
            ],
            [
                'status' => VocabularySourceAccess::STATUS_ENABLED,
                'enabled_by_user_id' => Auth::id(),
                'enabled_at' => now(config('app.timezone')),
                'revoked_at' => null,
            ]
        );

        $this->dispatch('toast', type: 'success', message: $set->isFolder()
            ? 'Vocabulary folder enabled for this class. Current and future lessons inside it will be visible unless a child lesson is disabled.'
            : 'Vocabulary lesson enabled for this class.');
    }

    public function openAccessManager(): void
    {
        $this->prepareAccessManager((int) $this->selectedSetId);
    }

    public function openAccessManagerFor(int $setId): void
    {
        $this->prepareAccessManager($setId);
    }

    #[Renderless]
    public function closeAccessManager(): void
    {
        $this->accessManagerOpen = false;
        $this->accessSetId = null;
        $this->accessClassSearch = '';
    }

    public function saveAccessManager(): void
    {
        $this->requireAccessSchema();
        $set = $this->resolveVisibleSet((int) $this->accessSetId);
        $classIds = $this->teacherAccessContexts()
            ->pluck('class_id')
            ->map(fn ($classId): int => (int) $classId)
            ->filter()
            ->unique()
            ->values();

        $inheritedAccess = $this->inheritedClassAccessForSet($set, $classIds);
        $directAccess = VocabularySourceAccess::query()
            ->where('vocabulary_set_id', (int) $set->id)
            ->where('audience_type', VocabularySourceAccess::AUDIENCE_CLASS)
            ->whereIn('audience_id', $classIds)
            ->get()
            ->keyBy('audience_id');
        $defaultRowsByClass = $this->bulkAccessRows(null)->keyBy('class_id');

        DB::transaction(function () use ($set, $classIds, $inheritedAccess, $directAccess, $defaultRowsByClass): void {
            if ($set->isFolder()) {
                $this->saveFolderAccessRows($set, $classIds, $inheritedAccess, $defaultRowsByClass);

                return;
            }

            $this->savePlayableAccessRows($set, $classIds, $inheritedAccess, $directAccess, $defaultRowsByClass);
        });

        $this->accessManagerOpen = false;
        $this->accessSetId = null;
        $this->accessClassSearch = '';
        $this->dispatch('toast', type: 'success', message: 'Vocabulary access saved.');
    }

    public function openLaunchChooserFor(int $setId): void
    {
        $set = $this->resolveVisibleSet($setId);

        if (! $set->canBeLaunched()) {
            $this->dispatch('toast', type: 'warning', message: 'Open a lesson/list to start a game.');

            return;
        }

        $this->redirectRoute('vocabulary.games.source', [
            'source' => (int) $set->id,
        ], navigate: false);
    }

    public function disableSelectedSetForClass(): void
    {
        $this->requireAccessSchema();
        $set = $this->resolveVisibleSet((int) $this->selectedSetId);
        [$audienceType, $audienceId] = $this->resolveAccessAudience();

        VocabularySourceAccess::query()->updateOrCreate(
            [
                'vocabulary_set_id' => (int) $set->id,
                'audience_type' => $audienceType,
                'audience_id' => $audienceId,
            ],
            [
                'status' => VocabularySourceAccess::STATUS_DISABLED,
                'enabled_by_user_id' => Auth::id(),
                'revoked_at' => now(config('app.timezone')),
            ]
        );

        $this->dispatch('toast', type: 'success', message: $set->isFolder()
            ? 'Vocabulary folder disabled for this class.'
            : 'Vocabulary lesson disabled for this class.');
    }

    public function toggleLegacyReport(): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner']), 403);

        $this->legacyReportOpen = ! $this->legacyReportOpen;
    }

    public function cloneSet(int $setId): void
    {
        $source = $this->resolveVisibleSet($setId);
        $clone = $this->copySetTreeToCustom($source);

        $this->selectedSetId = (int) $clone->id;
        $this->viewMode = 'source';
        $this->dispatch('toast', type: 'success', message: 'Editable copy created in My vocabulary folders.');
    }

    public function openWrongOptions(int $wordId): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        $word = Cambradge_word_api::query()->findOrFail($wordId);
        $generated = app(WrongOptionGenerator::class)->spellingOptions((string) $word->word, null, 3);

        $this->wrongOptionsWordId = (int) $word->id;
        $this->wrongOptionsText = trim((string) ($word->wrong_spelling ?? '')) !== ''
            ? (string) $word->wrong_spelling
            : implode("\n", $generated);
        $this->wrongOptionsOpen = true;
    }

    public function saveWrongOptions(): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        $payload = $this->validate([
            'wrongOptionsText' => ['nullable', 'string', 'max:1000'],
        ]);

        $updates = ['wrong_spelling' => $this->cleanText($payload['wrongOptionsText'] ?? null)];

        if ($this->wordQualityMetadataReady()) {
            $updates['wrong_spelling_rules'] = null;
            $updates['wrong_spelling_source'] = 'manual';
        }

        $word = Cambradge_word_api::query()->findOrFail($this->wrongOptionsWordId);
        $word->forceFill($updates)->save();

        $this->reset(['wrongOptionsWordId', 'wrongOptionsText', 'wrongOptionsOpen']);
        $this->dispatch('toast', type: 'success', message: 'Wrong spelling suggestions saved.');
    }

    public function updateWordDifficulty(int $wordId, string $difficulty): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        abort_unless(in_array($difficulty, ['', '1', '2', '3', '4', '5', '6'], true), 422);

        Cambradge_word_api::query()
            ->whereKey($wordId)
            ->update($this->manualDifficultyUpdate($difficulty === '' ? null : $difficulty));

        $this->dispatch('toast', type: 'success', message: 'Word difficulty saved.');
    }

    public function regenerateWrongOptionsForWord(int $wordId): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);

        $word = Cambradge_word_api::query()->findOrFail($wordId);
        $before = trim((string) ($word->wrong_spelling ?? ''));
        $this->applyWrongOptionGeneration($word);
        $word->refresh();
        $after = trim((string) ($word->wrong_spelling ?? ''));

        $message = $before === $after
            ? 'Already matches current generator: '.str_replace("\n", ', ', $after)
            : 'Wrong options regenerated: '.str_replace("\n", ', ', $after);

        $this->lastWrongOptionsRefreshWordId = $wordId;
        $this->lastWrongOptionsRefreshMessage = $message;
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function reestimateWordDifficulty(int $wordId): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);

        $word = Cambradge_word_api::query()->findOrFail($wordId);
        $this->applyDifficultyEstimate($word);

        $this->dispatch('toast', type: 'success', message: 'Difficulty re-estimated. The level may stay the same when the estimate already matches.');
    }

    public function previewBulkVocabularyQuality(string $action): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        abort_unless(in_array($action, ['wrong_options', 'difficulty', 'accept_difficulty'], true), 422);

        $this->bulkAction = $action;
        $this->bulkBatchPage = 0;
        $this->bulkPreview = $this->buildBulkPreview($action);
        $this->bulkSelectedWordIds = collect($this->bulkPreview['update_ids'] ?? [])
            ->mapWithKeys(fn ($id): array => [(int) $id => true])
            ->all();
        $this->bulkPreviewOpen = true;
    }

    public function confirmBulkVocabularyQuality(): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        abort_unless(in_array($this->bulkAction, ['wrong_options', 'difficulty', 'accept_difficulty'], true), 422);

        $allowedIds = collect($this->bulkPreview['update_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->all();
        $selectedIds = collect($this->bulkSelectedWordIds)
            ->filter(fn ($selected): bool => (bool) $selected)
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->all();
        $ids = array_values(array_intersect($allowedIds, $selectedIds));

        foreach (array_chunk($ids, 100) as $chunk) {
            Cambradge_word_api::query()
                ->whereIn('id', $chunk)
                ->orderBy('id')
                ->get()
                ->each(function (Cambradge_word_api $word): void {
                    if ($this->bulkAction === 'wrong_options') {
                        $this->applyWrongOptionGeneration($word);
                    } elseif ($this->bulkAction === 'difficulty') {
                        $this->applyDifficultyEstimate($word);
                    } elseif ($this->bulkAction === 'accept_difficulty') {
                        $word->forceFill($this->manualDifficultyUpdate((string) $word->difficulty_levels))->save();
                    }
                });
        }

        $updated = count($ids);
        $hadMore = ! empty($this->bulkPreview['too_many']);
        $message = $updated.' word bank rows updated.';

        if ($hadMore) {
            $message .= ' Use Next 500 to review the next batch.';
        }

        $this->bulkPreview = $this->buildBulkPreview($this->bulkAction);
        $this->bulkSelectedWordIds = collect($this->bulkPreview['update_ids'] ?? [])
            ->mapWithKeys(fn ($id): array => [(int) $id => true])
            ->all();

        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function nextBulkPreviewBatch(): void
    {
        abort_unless($this->bulkPreviewOpen && in_array($this->bulkAction, ['wrong_options', 'difficulty', 'accept_difficulty'], true), 422);

        $this->bulkBatchPage++;
        $this->bulkPreview = $this->buildBulkPreview($this->bulkAction);
        $this->selectAllBulkPreviewRows();
    }

    public function previousBulkPreviewBatch(): void
    {
        abort_unless($this->bulkPreviewOpen && in_array($this->bulkAction, ['wrong_options', 'difficulty', 'accept_difficulty'], true), 422);

        $this->bulkBatchPage = max(0, $this->bulkBatchPage - 1);
        $this->bulkPreview = $this->buildBulkPreview($this->bulkAction);
        $this->selectAllBulkPreviewRows();
    }

    public function selectAllBulkPreviewRows(): void
    {
        $this->bulkSelectedWordIds = collect($this->bulkPreview['update_ids'] ?? [])
            ->mapWithKeys(fn ($id): array => [(int) $id => true])
            ->all();
    }

    public function clearBulkPreviewRows(): void
    {
        $this->bulkSelectedWordIds = [];
    }

    public function openGroupEditor(int $wordId): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        Cambradge_word_api::query()->findOrFail($wordId);

        $this->groupWordId = $wordId;
        $this->wordGroupIds = Group_word::query()
            ->where('camb_sound_id', $wordId)
            ->pluck('category_id')
            ->map(fn ($id): string => (string) $id)
            ->values()
            ->all();
        $this->groupEditorOpen = true;
    }

    public function saveWordGroups(): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        $payload = $this->validate([
            'wordGroupIds' => ['array'],
            'wordGroupIds.*' => ['integer'],
        ]);
        $word = Cambradge_word_api::query()->findOrFail($this->groupWordId);
        $ids = collect($payload['wordGroupIds'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();
        $validIds = Category_group_word::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id);

        Group_word::query()
            ->where('camb_sound_id', (int) $word->id)
            ->whereNotIn('category_id', $validIds)
            ->delete();

        foreach ($validIds as $categoryId) {
            Group_word::query()->firstOrCreate(
                [
                    'camb_sound_id' => (int) $word->id,
                    'category_id' => (int) $categoryId,
                ],
                ['word' => (string) $word->word]
            );
        }

        $this->reset(['groupWordId', 'wordGroupIds', 'groupEditorOpen']);
        $this->dispatch('toast', type: 'success', message: 'Word groups saved.');
    }

    public function openWordGroupManager(?int $categoryId = null): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        $this->groupEditorCategoryId = $categoryId;
        $this->groupEditorName = '';

        if ($categoryId !== null) {
            $category = Category_group_word::query()->findOrFail($categoryId);
            $this->groupEditorName = (string) $category->name;
        }

        $this->wordGroupManagerOpen = true;
        $this->resetValidation();
    }

    public function saveWordGroupCategory(): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        $payload = $this->validate([
            'groupEditorName' => ['required', 'string', 'max:120'],
        ]);

        $name = trim($payload['groupEditorName']);

        if ($this->groupEditorCategoryId !== null) {
            Category_group_word::query()
                ->whereKey($this->groupEditorCategoryId)
                ->update(['name' => $name, 'active' => 1]);
        } else {
            Category_group_word::query()->create([
                'name' => $name,
                'active' => 1,
                'order' => ((int) Category_group_word::query()->max('order')) + 1,
            ]);
        }

        $this->reset(['groupEditorCategoryId', 'groupEditorName', 'wordGroupManagerOpen']);
        $this->dispatch('toast', type: 'success', message: 'Word group saved.');
    }

    public function openAudioReplacement(int $wordId): void
    {
        $this->authorizeAudioReplacement();

        $this->audioWordId = $wordId;
        $this->audioInputMode = 'cambridge';
        $this->audioPartialPath = '';
        $this->audioCompleteUrl = '';
        $this->audioUpload = null;
        $this->audioReplacementOpen = true;
    }

    public function replaceAudio(CambridgeAudioDownloadService $downloader): void
    {
        $this->authorizeAudioReplacement();
        $payload = $this->validate([
            'audioInputMode' => ['required', 'in:cambridge,url,upload'],
            'audioPartialPath' => ['nullable', 'string', 'max:500'],
            'audioCompleteUrl' => ['nullable', 'string', 'max:700'],
            'audioUpload' => ['nullable', 'file', 'mimes:mp3,mpeg,mpga,ogg,oga,wav,m4a,aac', 'max:2048'],
        ]);

        try {
            $word = Cambradge_word_api::query()->findOrFail($this->audioWordId);
            $this->replaceWordAudioFromPayload($downloader, $word, $payload['audioInputMode']);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                $this->audioErrorField((string) $payload['audioInputMode']) => $exception->getMessage(),
            ]);
        }

        $this->reset(['audioWordId', 'audioInputMode', 'audioPartialPath', 'audioCompleteUrl', 'audioUpload', 'audioReplacementOpen']);
        $this->dispatch('toast', type: 'success', message: 'Word audio replaced.');
    }

    public function createWordWithAudio(CambridgeAudioDownloadService $downloader): void
    {
        $this->authorizeAudioReplacement();
        $set = $this->selectedSetId ? $this->resolveVisibleSet((int) $this->selectedSetId) : null;

        $payload = $this->validate([
            'newWordText' => [
                'required',
                'string',
                'max:120',
                'regex:'.(string) config('vocabulary.free_play.allowed_pattern'),
            ],
            'newWordAudioMode' => ['required', 'in:cambridge,url,upload'],
            'newWordAudioPartialPath' => ['nullable', 'string', 'max:500'],
            'newWordAudioCompleteUrl' => ['nullable', 'string', 'max:700'],
            'newWordAudioUpload' => ['nullable', 'file', 'mimes:mp3,mpeg,mpga,ogg,oga,wav,m4a,aac', 'max:2048'],
            'newWordWithoutAudio' => ['boolean'],
        ]);

        $wordText = $this->normalizeNewVocabularyWord((string) $payload['newWordText']);
        $word = Cambradge_word_api::query()
            ->whereRaw('LOWER(TRIM(word)) = ?', [mb_strtolower($wordText)])
            ->first();

        if ($word instanceof Cambradge_word_api) {
            $updates = [];

            if (trim((string) $word->difficulty_levels) === '') {
                $estimate = app(VocabularyDifficultyEstimator::class)->estimateWithReason($wordText);
                $updates['difficulty_levels'] = $estimate['level'];

                if ($this->wordQualityMetadataReady()) {
                    $updates['difficulty_reason'] = $estimate['reason'];
                    $updates['difficulty_source'] = 'generated';
                }
            }

            if (trim((string) $word->wrong_spelling) === '') {
                $wrongOptions = app(WrongOptionGenerator::class)->spellingOptionsDetailed(
                    $wordText,
                    null,
                    3,
                    (string) ($updates['difficulty_levels'] ?? $word->difficulty_levels ?? '')
                );

                if ($wrongOptions !== []) {
                    $updates['wrong_spelling'] = implode("\n", array_column($wrongOptions, 'text'));

                    if ($this->wordQualityMetadataReady()) {
                        $updates['wrong_spelling_rules'] = $wrongOptions;
                        $updates['wrong_spelling_source'] = 'generated';
                    }
                }
            }

            if ($updates !== []) {
                $word->forceFill($updates)->save();
            }

            if (! $this->newWordWithoutAudio) {
                try {
                    $this->replaceNewWordAudioFromPayload($downloader, $word, $payload['newWordAudioMode']);
                } catch (\Throwable $exception) {
                    throw ValidationException::withMessages([
                        $this->newWordAudioErrorField((string) $payload['newWordAudioMode']) => $exception->getMessage(),
                    ]);
                }
            }
        } else {
            $storedFilename = null;

            if (! $this->newWordWithoutAudio) {
                try {
                    $storedFilename = $this->storeNewWordAudioFromPayload($downloader, $wordText, $payload['newWordAudioMode']);
                } catch (\Throwable $exception) {
                    throw ValidationException::withMessages([
                        $this->newWordAudioErrorField((string) $payload['newWordAudioMode']) => $exception->getMessage(),
                    ]);
                }
            }

            try {
                $estimate = app(VocabularyDifficultyEstimator::class)->estimateWithReason($wordText);
                $wrongOptions = app(WrongOptionGenerator::class)->spellingOptionsDetailed($wordText, null, 3, $estimate['level']);
                $createPayload = [
                    'word' => $wordText,
                    'image' => null,
                    'us_sound' => $storedFilename,
                    'uk_sound' => null,
                    'difficulty_levels' => $estimate['level'],
                    'wrong_spelling' => $wrongOptions === [] ? null : implode("\n", array_column($wrongOptions, 'text')),
                ];

                if ($this->wordQualityMetadataReady()) {
                    $createPayload['difficulty_reason'] = $estimate['reason'];
                    $createPayload['difficulty_source'] = 'generated';
                    $createPayload['wrong_spelling_rules'] = $wrongOptions === [] ? null : $wrongOptions;
                    $createPayload['wrong_spelling_source'] = $wrongOptions === [] ? 'legacy_unknown' : 'generated';
                }

                $word = Cambradge_word_api::query()->create([
                    ...$createPayload,
                ]);
            } catch (\Throwable $exception) {
                if ($storedFilename !== null) {
                    $downloader->deleteStoredFilename($storedFilename);
                }

                throw ValidationException::withMessages([
                    'newWordText' => 'The audio was stored, but the vocabulary word could not be created.',
                ]);
            }
        }

        if ($set instanceof VocabularySet && $set->isPlayable() && $this->canEditSet($set)) {
            VocabularySetWord::query()->firstOrCreate(
                [
                    'vocabulary_set_id' => (int) $set->id,
                    'word_id' => (int) $word->id,
                ],
                [
                    'position' => $this->nextWordPosition((int) $set->id),
                    'added_by_user_id' => Auth::id(),
                ]
            );
        }

        $createdWithoutAudio = (bool) $this->newWordWithoutAudio;
        $this->reset(['newWordText', 'newWordAudioMode', 'newWordAudioPartialPath', 'newWordAudioCompleteUrl', 'newWordAudioUpload', 'newWordWithoutAudio']);
        $audioMessage = $createdWithoutAudio ? 'without audio' : 'with audio saved';
        $message = $set instanceof VocabularySet && $this->canEditSet($set)
            ? 'Word created '.$audioMessage.' and added to this list.'
            : 'Word created '.$audioMessage.'.';
        $this->wordSearch = $wordText;
        $this->newWordFeedback = $message;
        $this->resetValidation();
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function openImageEditor(int $wordId): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        $word = Cambradge_word_api::query()->findOrFail($wordId);

        $this->imageWordId = $wordId;
        $this->wordImageUpload = null;
        $this->imagePreviewUrl = $this->wordImageUrl((string) ($word->image ?? ''));
        $this->imageEditorOpen = true;
    }

    public function saveWordImage(): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        $payload = $this->validate([
            'wordImageUpload' => ['required', 'image', 'max:2048'],
        ]);
        $word = Cambradge_word_api::query()->findOrFail($this->imageWordId);
        $upload = $payload['wordImageUpload'];
        $extension = strtolower($upload->getClientOriginalExtension() ?: 'jpg');
        $oldImage = (string) ($word->image ?? '');
        $filename = $this->safeMediaStem((string) $word->word).'-'.$word->id.'-'.now()->format('YmdHis').'.'.$extension;
        $basePath = trim((string) config('vocabulary.images.word_image_path'), '/');
        $relativePath = $basePath.'/'.$filename;
        $absolutePath = public_path(str_replace('/', DIRECTORY_SEPARATOR, $relativePath));

        File::ensureDirectoryExists(dirname($absolutePath));
        File::copy($upload->getRealPath(), $absolutePath);
        $word->forceFill(['image' => $relativePath])->save();

        if ($oldImage !== '' && $oldImage !== $relativePath && ! preg_match('/\Ahttps?:\/\//i', $oldImage)) {
            $oldPath = public_path(str_replace('/', DIRECTORY_SEPARATOR, ltrim($oldImage, '/')));
            if (is_file($oldPath)) {
                File::delete($oldPath);
            }
        }

        $this->reset(['imageWordId', 'imagePreviewUrl', 'wordImageUpload', 'imageEditorOpen']);
        $this->dispatch('toast', type: 'success', message: 'Word image saved.');
    }

    public function removeCurrentWordImage(): void
    {
        if ($this->imageWordId === null) {
            return;
        }

        $this->removeWordImage((int) $this->imageWordId);
        $this->reset(['imageWordId', 'imagePreviewUrl', 'wordImageUpload', 'imageEditorOpen']);
    }

    public function removeWordImage(int $wordId): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']), 403);
        $word = Cambradge_word_api::query()->findOrFail($wordId);
        $word->forceFill(['image' => null])->save();

        $this->dispatch('toast', type: 'success', message: 'Word image removed.');
    }

    public function render(): View
    {
        $provider = app(VocabularyWordProvider::class);
        $audioResolver = app(VocabularyAudioResolver::class);
        $wrongOptionGenerator = app(WrongOptionGenerator::class);
        $difficultyEstimator = app(VocabularyDifficultyEstimator::class);
        $schemaReady = $this->schemaReady();
        $sets = collect();
        $selectedSet = null;
        $selectedWords = collect();
        $selectedChildren = collect();
        $selectedChildMetadata = [];
        $selectedChildType = null;
        $searchResults = collect();
        $wordBankRows = collect();
        $wordGroupOptions = collect();

        if ($schemaReady) {
            app(VocabularySourceRegistry::class)->ensureLegacySourceProxies();
            $sets = $this->visibleSets();
            $wordGroupOptions = $this->wordGroupOptions();
            $selectedSet = $this->selectedSetId ? $sets->firstWhere('id', $this->selectedSetId) : null;

            if ($selectedSet instanceof VocabularySet) {
                $sourceWordLimit = $this->listWordEditMode
                    ? 5000
                    : max(50, (int) $this->sourceWordLimit);
                $selectedWords = $this->browseWordsForSelectedSet(
                    $selectedSet,
                    $provider,
                    $audioResolver,
                    $wrongOptionGenerator,
                    $difficultyEstimator,
                    $sourceWordLimit
                );
                $selectedChildren = $selectedSet
                    ->children()
                    ->withCount('memberships')
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get();
                $selectedChildMetadata = app(VocabularySourceRegistry::class)
                    ->batchMetadata($selectedChildren->pluck('id'));
                $selectedChildType = $selectedChildren->first()?->node_type;

                $sourceSearch = Str::lower(trim($this->sourceSearch));
                if ($sourceSearch !== '') {
                    $selectedChildren = $selectedChildren
                        ->filter(fn (VocabularySet $child): bool => str_contains(Str::lower((string) $child->title), $sourceSearch))
                        ->values();
                    $selectedWords = $selectedWords
                        ->filter(fn (array $row): bool => str_contains(Str::lower((string) $row['word']), $sourceSearch))
                        ->values();
                }
            }

            if (trim($this->wordSearch) !== '') {
                $searchResults = $provider
                    ->searchWords($this->wordSearch, 50)
                    ->map(fn (Cambradge_word_api $word): array => $this->wordRow($word, $audioResolver, $wrongOptionGenerator, $difficultyEstimator));
            }

            $wordBankRows = $this->wordBankQuery()
                ->limit(max(50, (int) $this->wordBankLimit))
                ->get()
                ->map(fn (Cambradge_word_api $word): array => $this->wordRow($word, $audioResolver, $wrongOptionGenerator, $difficultyEstimator));
        }

        return view('livewire.teacher.vocabulary-manager', [
            'schemaReady' => $schemaReady,
            'sets' => $sets,
            'rootCards' => $schemaReady ? $this->sourceRootCards($sets) : [],
            'customRootCards' => $schemaReady ? $this->customRootCards($sets) : [],
            'selectedBreadcrumbs' => $selectedSet instanceof VocabularySet ? $this->breadcrumbsForSet($selectedSet, $sets) : [],
            'sourceChildType' => $selectedChildType,
            'viewMode' => $this->viewMode,
            'selectedSet' => $selectedSet,
            'selectedWords' => $selectedWords,
            'selectedChildren' => $selectedChildren,
            'selectedChildMetadata' => $selectedChildMetadata,
            'editableFolderOptions' => $this->editableFolderOptions($sets),
            'wordBankRows' => $wordBankRows,
            'searchResults' => $searchResults,
            'wordGroupOptions' => $wordGroupOptions,
            'canReplaceAudio' => $this->canReplaceAudio(),
            'accessContexts' => $this->accessManagerOpen ? $this->teacherAccessContexts() : collect(),
            'accessStudents' => $this->accessManagerOpen ? $this->accessStudentsForSelectedContext() : collect(),
            'bulkAccessRows' => $this->accessManagerOpen ? $this->bulkAccessRows($this->accessClassDisplayLimit) : collect(),
            'parentUrl' => $selectedSet instanceof VocabularySet ? $this->parentUrlForSet($selectedSet) : null,
            'legacyReport' => $this->legacyReportOpen ? app(LegacyHangmanImportService::class)->report() : collect(),
        ])->layout('components.layouts.app', [
            'title' => $selectedSet instanceof VocabularySet ? $selectedSet->title : 'Library Vocabulary',
            'breadcrumb_links' => $this->navbarBreadcrumbLinks($selectedSet, $sets),
        ]);
    }

    /**
     * @param  Collection<int, VocabularySet>  $sets
     * @return array<int, array<string, mixed>>
     */
    private function sourceRootCards(Collection $sets): array
    {
        $definitions = [
            VocabularySet::SOURCE_LEGACY_CAMBRIDGE => [
                'title' => 'Cambridge',
                'description' => 'Cambridge Path units and lessons already mapped in the database.',
                'icon' => 'icon-base ti tabler-books',
                'tone' => 'primary',
            ],
            VocabularySet::SOURCE_LEGACY_PHONICS => [
                'title' => 'Phonics',
                'description' => 'Phonics levels and lesson word lists from the existing database.',
                'icon' => 'icon-base ti tabler-volume',
                'tone' => 'success',
            ],
            VocabularySet::SOURCE_LEGACY_GROUP => [
                'title' => 'Word Group',
                'description' => 'Theme groups such as fruits, colors, animals, and other categories.',
                'icon' => 'icon-base ti tabler-category-2',
                'tone' => 'warning',
            ],
            VocabularySet::SOURCE_LEGACY_DIFFICULTY => [
                'title' => 'Difficulty',
                'description' => 'Easy, Medium, Hard, Challenging, and other difficulty categories.',
                'icon' => 'icon-base ti tabler-chart-bar',
                'tone' => 'danger',
            ],
            VocabularySet::SOURCE_LEGACY_HANGMAN => [
                'title' => 'Legacy Floatie',
                'description' => 'Old game categories preserved as read-only Floatie source material.',
                'icon' => 'icon-base ti tabler-star',
                'tone' => 'secondary',
            ],
        ];

        return collect($definitions)
            ->map(function (array $definition, string $sourceKind) use ($sets): array {
                $matching = $sets->where('source_kind', $sourceKind);
                $firstRoot = $matching
                    ->whereNull('parent_id')
                    ->sortBy('title')
                    ->first()
                    ?: $matching->sortBy('title')->first();

                return $definition + [
                    'source_kind' => $sourceKind,
                    'count' => $matching->filter(fn (VocabularySet $set): bool => $set->canBeLaunched())->count(),
                    'first_set_id' => $firstRoot instanceof VocabularySet ? (int) $firstRoot->id : null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, VocabularySet>  $sets
     * @return array<int, array{id:int,title:string,description:string,visibility:string,count:int,icon:string,tone:string}>
     */
    private function customRootCards(Collection $sets): array
    {
        return $sets
            ->filter(fn (VocabularySet $set): bool => $set->source_kind === VocabularySet::SOURCE_CUSTOM
                && $set->parent_id === null
                && $set->isFolder())
            ->sortBy([
                ['sort_order', 'asc'],
                ['title', 'asc'],
            ])
            ->map(fn (VocabularySet $set): array => [
                'id' => (int) $set->id,
                'title' => (string) $set->title,
                'description' => (string) ($set->description ?? ''),
                'visibility' => (string) $set->visibility,
                'count' => (int) $set->children()->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)->count(),
                'icon' => 'icon-base ti tabler-folder',
                'tone' => $set->visibility === VocabularySet::VISIBILITY_SHARED ? 'success' : 'info',
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, VocabularySet>  $sets
     * @return array<int, array{id:int,title:string}>
     */
    private function breadcrumbsForSet(VocabularySet $set, Collection $sets): array
    {
        $byId = $sets->keyBy('id');
        $trail = [];
        $current = $set;

        while ($current instanceof VocabularySet) {
            array_unshift($trail, [
                'id' => (int) $current->id,
                'title' => (string) $current->title,
            ]);
            $current = $current->parent_id ? $byId->get((int) $current->parent_id) : null;
        }

        return $trail;
    }

    /**
     * @param  Collection<int, VocabularySet>  $sets
     * @return array<string, string|null>
     */
    private function navbarBreadcrumbLinks(?VocabularySet $selectedSet, Collection $sets): array
    {
        if ($this->viewMode === 'word_bank') {
            return [
                'Library' => route('teacher.get_library'),
                'Vocabulary' => route('teacher.library.vocabulary'),
                'Vocabulary Management' => null,
            ];
        }

        if ($selectedSet instanceof VocabularySet) {
            $links = [
                'Library' => route('teacher.get_library'),
                'Vocabulary' => route('teacher.library.vocabulary'),
            ];

            foreach ($this->breadcrumbsForSet($selectedSet, $sets) as $crumb) {
                $links[$crumb['title']] = (int) $crumb['id'] === (int) $selectedSet->id
                    ? null
                    : route('teacher.library.vocabulary', ['mode' => 'source', 'set' => $crumb['id']]);
            }

            return $links;
        }

        return [
            'Library' => route('teacher.get_library'),
            'Vocabulary' => null,
        ];
    }

    private function schemaReady(): bool
    {
        return Schema::hasTable('vocabulary_sets')
            && Schema::hasTable('vocabulary_set_words');
    }

    private function requireSchema(): void
    {
        abort_unless($this->schemaReady(), 503, 'Vocabulary manager needs the owner-run SQL patch before it can save folders.');
    }

    private function requireAccessSchema(): void
    {
        $this->requireSchema();
        abort_unless(Schema::hasTable('vocabulary_source_access'), 503, 'Vocabulary access controls need the owner-run SQL patch before they can save.');
    }

    private function prepareAccessManager(int $setId): void
    {
        $this->requireAccessSchema();
        $this->accessSetId = $setId;
        $this->resolveVisibleSet($setId);
        $this->accessClassSelections = [];
        $this->accessManagerInstance++;
        $this->accessManagerOpen = true;
        $this->resetValidation();
    }

    /** @return array{0: string, 1: int} */
    private function resolveAccessAudience(): array
    {
        $payload = $this->validate([
            'accessTeacherSubjectClassId' => ['required', 'integer'],
            'accessAudienceType' => ['required', 'in:class,student'],
            'accessStudentId' => ['nullable', 'integer'],
        ]);

        $studentId = (int) ($payload['accessStudentId'] ?? 0);
        if ($payload['accessAudienceType'] === VocabularySourceAccess::AUDIENCE_STUDENT && $studentId <= 0) {
            throw ValidationException::withMessages([
                'accessStudentId' => 'Choose a student.',
            ]);
        }

        $context = TeacherSubjectClass::query()
            ->availableForTeacher()
            ->withActiveStudentSubject(
                $payload['accessAudienceType'] === VocabularySourceAccess::AUDIENCE_STUDENT
                    ? $studentId
                    : null
            )
            ->where('user_teacher_coteacher_id', Auth::id())
            ->whereKey((int) $payload['accessTeacherSubjectClassId'])
            ->firstOrFail();

        if ($payload['accessAudienceType'] === VocabularySourceAccess::AUDIENCE_STUDENT) {
            return [VocabularySourceAccess::AUDIENCE_STUDENT, $studentId];
        }

        return [VocabularySourceAccess::AUDIENCE_CLASS, (int) $context->class_id];
    }

    private function accessStudentsForSelectedContext(): Collection
    {
        if ($this->accessTeacherSubjectClassId === null) {
            return collect();
        }

        $context = TeacherSubjectClass::query()
            ->availableForTeacher()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->whereKey((int) $this->accessTeacherSubjectClassId)
            ->first();

        if (! $context instanceof TeacherSubjectClass) {
            return collect();
        }

        return Student::query()
            ->select('students.id', 'students.first_name', 'students.last_name', 'students.student_email')
            ->join('student_classes_history', 'student_classes_history.student_id', '=', 'students.id')
            ->join('students_subjects', 'students_subjects.student_id', '=', 'students.id')
            ->join('grade_level_subjects', 'grade_level_subjects.id', '=', 'students_subjects.grade_level_subject_id')
            ->where('student_classes_history.class_id', (int) $context->class_id)
            ->whereIn('student_classes_history.status', ['current', 'inactive'])
            ->where('students_subjects.status', 'active')
            ->where('grade_level_subjects.subject_id', (int) $context->subject_id)
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->distinct()
            ->get();
    }

    private function bulkAccessRows(?int $limit = null): Collection
    {
        $contexts = $this->teacherAccessContexts()->unique('class_id')->values();

        if ($contexts->isEmpty() || $this->accessSetId === null) {
            return collect();
        }

        $classIds = $contexts
            ->pluck('class_id')
            ->map(fn ($classId): int => (int) $classId)
            ->filter()
            ->unique()
            ->values();
        $accessByClass = VocabularySourceAccess::query()
            ->where('vocabulary_set_id', (int) $this->accessSetId)
            ->where('audience_type', VocabularySourceAccess::AUDIENCE_CLASS)
            ->whereIn('audience_id', $classIds)
            ->get()
            ->keyBy('audience_id');
        $selectedSet = $this->resolveVisibleSet((int) $this->accessSetId);
        $search = Str::lower(trim($this->accessClassSearch));

        $contextsForRows = $contexts;
        if ($limit !== null && $search === '') {
            $directClassIds = $accessByClass
                ->keys()
                ->map(fn ($classId): int => (int) $classId)
                ->all();
            $directContextRows = $contexts->filter(
                fn (TeacherSubjectClass $context): bool => in_array((int) $context->class_id, $directClassIds, true)
            );
            $fillerRows = $contexts
                ->reject(fn (TeacherSubjectClass $context): bool => in_array((int) $context->class_id, $directClassIds, true))
                ->take($limit);

            $contextsForRows = $directContextRows
                ->concat($fillerRows)
                ->unique('class_id')
                ->values();
        }

        $rowClassIds = $contextsForRows
            ->pluck('class_id')
            ->map(fn ($classId): int => (int) $classId)
            ->filter()
            ->unique()
            ->values();
        $inheritedAccess = $this->inheritedClassAccessForSet($selectedSet, $rowClassIds);
        $folderChildAccess = $selectedSet->isFolder()
            ? $this->folderChildAccessByClass($selectedSet, $rowClassIds)
            : [];

        $rows = $contextsForRows
            ->map(function (TeacherSubjectClass $context) use ($accessByClass, $inheritedAccess, $folderChildAccess): array {
                $classId = (int) $context->class_id;
                $access = $accessByClass->get($classId);
                $hasDirect = $access instanceof VocabularySourceAccess;
                $inherited = $inheritedAccess[$classId] ?? null;
                $childAccess = $folderChildAccess[$classId] ?? null;
                $enabled = $hasDirect
                    ? $access->status === VocabularySourceAccess::STATUS_ENABLED
                    : (bool) ($inherited['enabled'] ?? $childAccess['enabled'] ?? false);
                $customized = false;

                if (is_array($childAccess)) {
                    if ($hasDirect && $access->status === VocabularySourceAccess::STATUS_ENABLED) {
                        $customized = (int) ($childAccess['disabled_count'] ?? 0) > 0;
                    } elseif ($hasDirect && $access->status === VocabularySourceAccess::STATUS_DISABLED) {
                        $customized = (int) ($childAccess['enabled_count'] ?? 0) > 0;
                    } else {
                        $enabledCount = (int) ($childAccess['enabled_count'] ?? 0);
                        $totalCount = (int) ($childAccess['total_count'] ?? 0);
                        $customized = $enabledCount > 0 && $enabledCount < $totalCount;
                    }
                }

                return [
                    'class_id' => $classId,
                    'label' => trim((string) ($context->class_name ?: 'Class #'.$context->class_id)),
                    'subject' => $this->displaySubjectName((string) $context->subject_name),
                    'enabled' => $enabled,
                    'origin' => $hasDirect ? 'direct' : ($inherited ? 'inherited' : ($childAccess ? 'child' : 'none')),
                    'origin_label' => $hasDirect ? 'Direct' : (string) ($inherited['label'] ?? $childAccess['label'] ?? ''),
                    'customized' => $customized,
                ];
            })
            ->when($search !== '', fn (Collection $rows): Collection => $rows
                ->filter(fn (array $row): bool => str_contains(Str::lower($row['label'].' '.$row['subject']), $search)))
            ->sortBy('label')
            ->values();

        if ($limit === null || $rows->count() <= $limit) {
            return $rows;
        }

        if ($search !== '') {
            return $rows->take($limit)->values();
        }

        $enabledRows = $rows->filter(fn (array $row): bool => (bool) $row['enabled'])->values();
        $disabledRows = $rows->reject(fn (array $row): bool => (bool) $row['enabled'])->values();

        $visibleEnabledRows = $enabledRows->take($limit)->values();

        return $visibleEnabledRows
            ->concat($disabledRows->take(max(0, $limit - $visibleEnabledRows->count())))
            ->values();
    }

    private function parentUrlForSet(VocabularySet $set): ?string
    {
        if ($set->parent_id) {
            return route('teacher.library.vocabulary', ['mode' => 'source', 'set' => (int) $set->parent_id]);
        }

        return route('teacher.library.vocabulary');
    }

    /**
     * @param  Collection<int, VocabularySet>  $sets
     * @return list<int>
     */
    private function ancestorIdsForSet(VocabularySet $set, Collection $sets): array
    {
        $byId = $sets->keyBy('id');
        $ancestorIds = [];
        $current = $set->parent_id ? $byId->get((int) $set->parent_id) : null;

        while ($current instanceof VocabularySet) {
            $ancestorIds[] = (int) $current->id;
            $current = $current->parent_id ? $byId->get((int) $current->parent_id) : null;
        }

        return $ancestorIds;
    }

    /**
     * @param  Collection<int, int>  $classIds
     * @return array<int, array{enabled: bool, label: string}>
     */
    private function inheritedClassAccessForSet(VocabularySet $set, Collection $classIds): array
    {
        if (! $set->parent_id) {
            return [];
        }

        $sets = $this->visibleSets();
        $ancestorIds = $this->ancestorIdsForSet($set, $sets);

        if ($ancestorIds === []) {
            return [];
        }

        $ancestorsById = $sets->whereIn('id', $ancestorIds)->keyBy('id');
        $rankBySetId = collect($ancestorIds)->flip();
        $rows = VocabularySourceAccess::query()
            ->whereIn('vocabulary_set_id', $ancestorIds)
            ->where('audience_type', VocabularySourceAccess::AUDIENCE_CLASS)
            ->whereIn('audience_id', $classIds)
            ->get()
            ->sortBy(fn (VocabularySourceAccess $row): int => (int) ($rankBySetId[(int) $row->vocabulary_set_id] ?? 999));

        $inherited = [];
        foreach ($rows as $row) {
            $classId = (int) $row->audience_id;
            if (array_key_exists($classId, $inherited)) {
                continue;
            }

            $ancestor = $ancestorsById->get((int) $row->vocabulary_set_id);
            $inherited[$classId] = [
                'enabled' => $row->status === VocabularySourceAccess::STATUS_ENABLED,
                'label' => $ancestor instanceof VocabularySet ? 'Inherited from '.$ancestor->title : 'Inherited',
            ];
        }

        return $inherited;
    }

    /**
     * @param  Collection<int, int>  $classIds
     * @param  array<int, array{enabled: bool, label: string}>  $inheritedAccess
     * @param  Collection<int, array<string, mixed>>  $defaultRowsByClass
     */
    private function saveFolderAccessRows(
        VocabularySet $set,
        Collection $classIds,
        array $inheritedAccess,
        Collection $defaultRowsByClass
    ): void {
        $childPlayableIds = $this->playableDescendantSets($set)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values();
        $now = now(config('app.timezone'));

        foreach ($classIds as $classId) {
            $classId = (int) $classId;
            $enabled = $this->selectedAccessValue($classId, $defaultRowsByClass);

            if ($childPlayableIds->isNotEmpty()) {
                VocabularySourceAccess::query()
                    ->whereIn('vocabulary_set_id', $childPlayableIds)
                    ->where('audience_type', VocabularySourceAccess::AUDIENCE_CLASS)
                    ->where('audience_id', $classId)
                    ->delete();
            }

            if ($enabled) {
                VocabularySourceAccess::query()->updateOrCreate(
                    [
                        'vocabulary_set_id' => (int) $set->id,
                        'audience_type' => VocabularySourceAccess::AUDIENCE_CLASS,
                        'audience_id' => $classId,
                    ],
                    [
                        'status' => VocabularySourceAccess::STATUS_ENABLED,
                        'enabled_by_user_id' => Auth::id(),
                        'enabled_at' => $now,
                        'revoked_at' => null,
                    ]
                );

                continue;
            }

            $inheritedStatus = $inheritedAccess[$classId]['enabled'] ?? null;

            if ($inheritedStatus === true) {
                VocabularySourceAccess::query()->updateOrCreate(
                    [
                        'vocabulary_set_id' => (int) $set->id,
                        'audience_type' => VocabularySourceAccess::AUDIENCE_CLASS,
                        'audience_id' => $classId,
                    ],
                    [
                        'status' => VocabularySourceAccess::STATUS_DISABLED,
                        'enabled_by_user_id' => Auth::id(),
                        'enabled_at' => null,
                        'revoked_at' => $now,
                    ]
                );

                continue;
            }

            VocabularySourceAccess::query()
                ->where('vocabulary_set_id', (int) $set->id)
                ->where('audience_type', VocabularySourceAccess::AUDIENCE_CLASS)
                ->where('audience_id', $classId)
                ->delete();
        }
    }

    /**
     * @param  Collection<int, int>  $classIds
     * @param  array<int, array{enabled: bool, label: string}>  $inheritedAccess
     * @param  Collection<int, VocabularySourceAccess>  $directAccess
     * @param  Collection<int, array<string, mixed>>  $defaultRowsByClass
     */
    private function savePlayableAccessRows(
        VocabularySet $set,
        Collection $classIds,
        array $inheritedAccess,
        Collection $directAccess,
        Collection $defaultRowsByClass
    ): void {
        $now = now(config('app.timezone'));

        foreach ($classIds as $classId) {
            $classId = (int) $classId;
            $enabled = $this->selectedAccessValue($classId, $defaultRowsByClass);
            $inheritedStatus = $inheritedAccess[$classId]['enabled'] ?? null;
            $hasDirectAccess = $directAccess->has($classId);

            if (! $hasDirectAccess && $inheritedStatus !== null && $enabled === (bool) $inheritedStatus) {
                continue;
            }

            if (! $enabled && ! $hasDirectAccess && $inheritedStatus === null) {
                continue;
            }

            VocabularySourceAccess::query()->updateOrCreate(
                [
                    'vocabulary_set_id' => (int) $set->id,
                    'audience_type' => VocabularySourceAccess::AUDIENCE_CLASS,
                    'audience_id' => $classId,
                ],
                [
                    'status' => $enabled
                        ? VocabularySourceAccess::STATUS_ENABLED
                        : VocabularySourceAccess::STATUS_DISABLED,
                    'enabled_by_user_id' => Auth::id(),
                    'enabled_at' => $enabled ? $now : null,
                    'revoked_at' => $enabled ? null : $now,
                ]
            );
        }
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $defaultRowsByClass
     */
    private function selectedAccessValue(int $classId, Collection $defaultRowsByClass): bool
    {
        if (array_key_exists($classId, $this->accessClassSelections)) {
            return (bool) $this->accessClassSelections[$classId];
        }

        $row = $defaultRowsByClass->get($classId);

        return is_array($row) ? (bool) ($row['enabled'] ?? false) : false;
    }

    /**
     * @param  Collection<int, int>  $classIds
     * @return array<int, array{enabled: bool, enabled_count: int, disabled_count: int, total_count: int, label: string}>
     */
    private function folderChildAccessByClass(VocabularySet $set, Collection $classIds): array
    {
        $descendantIds = $this->playableDescendantSets($set)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values();
        $descendantCount = $descendantIds->count();

        if ($descendantCount === 0) {
            return [];
        }

        $rowsByClass = VocabularySourceAccess::query()
            ->whereIn('vocabulary_set_id', $descendantIds)
            ->where('audience_type', VocabularySourceAccess::AUDIENCE_CLASS)
            ->whereIn('audience_id', $classIds)
            ->get()
            ->groupBy('audience_id');

        $summary = [];
        foreach ($rowsByClass as $classId => $rows) {
            $enabledCount = $rows
                ->filter(fn (VocabularySourceAccess $row): bool => $row->status === VocabularySourceAccess::STATUS_ENABLED)
                ->pluck('vocabulary_set_id')
                ->unique()
                ->count();
            $disabledCount = $rows
                ->filter(fn (VocabularySourceAccess $row): bool => $row->status === VocabularySourceAccess::STATUS_DISABLED)
                ->pluck('vocabulary_set_id')
                ->unique()
                ->count();

            if ($enabledCount === 0 && $disabledCount === 0) {
                continue;
            }

            $summary[(int) $classId] = [
                'enabled' => $enabledCount > 0,
                'enabled_count' => $enabledCount,
                'disabled_count' => $disabledCount,
                'total_count' => $descendantCount,
                'label' => $enabledCount === $descendantCount && $disabledCount === 0
                    ? 'All lessons selected'
                    : $enabledCount.' of '.$descendantCount.' lessons selected',
            ];
        }

        return $summary;
    }

    private function visibleSets(): EloquentCollection
    {
        return VocabularySet::query()
            ->visibleToTeachers(Auth::id())
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->with(['creator:id,name', 'owner:id,name', 'parent:id,title'])
            ->withCount('memberships')
            ->orderBy('visibility')
            ->orderBy('parent_id')
            ->orderBy('title')
            ->get();
    }

    private function validatedParentForNewSet(mixed $parentId): ?VocabularySet
    {
        if (! filled($parentId)) {
            return null;
        }

        $parent = $this->resolveVisibleSet((int) $parentId);

        if (! $this->canEditSet($parent) || ! $parent->isFolder() || $parent->source_kind !== VocabularySet::SOURCE_CUSTOM) {
            throw ValidationException::withMessages([
                'setParentId' => 'Choose one of your editable vocabulary folders as the parent.',
            ]);
        }

        return $parent;
    }

    private function guardChildHomogeneity(?VocabularySet $parent, string $nodeType): void
    {
        if (! $parent instanceof VocabularySet) {
            return;
        }

        $existingChildType = VocabularySet::query()
            ->where('parent_id', (int) $parent->id)
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->value('node_type');

        if ($existingChildType !== null && $existingChildType !== $nodeType) {
            throw ValidationException::withMessages([
                'setNodeType' => $existingChildType === VocabularySet::NODE_FOLDER
                    ? 'This folder already contains folders. Add another folder here, or choose a different parent for a list.'
                    : 'This folder already contains playable lists. Add another list here, or choose a different parent for a folder.',
            ]);
        }
    }

    /**
     * @param  Collection<int, VocabularySet>  $sets
     * @return array<int, array{id:int,title:string}>
     */
    private function editableFolderOptions(Collection $sets): array
    {
        return $sets
            ->filter(fn (VocabularySet $set): bool => $set->isFolder()
                && $set->source_kind === VocabularySet::SOURCE_CUSTOM
                && $this->canEditSet($set))
            ->sortBy('title')
            ->map(fn (VocabularySet $set): array => [
                'id' => (int) $set->id,
                'title' => trim(($set->parent?->title ? $set->parent->title.' / ' : '').$set->title),
            ])
            ->values()
            ->all();
    }

    private function resolveVisibleSet(int $setId): VocabularySet
    {
        $this->requireSchema();

        return VocabularySet::query()
            ->visibleToTeachers(Auth::id())
            ->whereKey($setId)
            ->firstOrFail();
    }

    private function requireSelectedEditableSet(): VocabularySet
    {
        $set = $this->resolveVisibleSet((int) $this->selectedSetId);
        abort_unless($this->canEditSet($set), 403);

        return $set;
    }

    private function requireEditableSet(int $setId): VocabularySet
    {
        $set = $this->resolveVisibleSet($setId);
        abort_unless($this->canEditSet($set), 403);

        return $set;
    }

    private function requireSelectedEditableList(): VocabularySet
    {
        $set = $this->requireSelectedEditableSet();
        abort_unless($set->isPlayable(), 403);

        return $set;
    }

    private function canEditSet(VocabularySet $set): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'super_admin', 'owner'])
            || (int) $set->owner_user_id === (int) $user->id;
    }

    private function canReplaceAudio(): bool
    {
        return Auth::user()?->hasAnyRole(['admin', 'super_admin', 'owner', 'teacher']) ?? false;
    }

    /**
     * @return list<int>
     */
    private function setTreeIds(VocabularySet $set): array
    {
        $ids = [(int) $set->id];

        VocabularySet::query()
            ->where('parent_id', (int) $set->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->each(function (VocabularySet $child) use (&$ids): void {
                $ids = array_merge($ids, $this->setTreeIds($child));
            });

        return $ids;
    }

    /**
     * @return Collection<int, VocabularySet>
     */
    private function playableDescendantSets(VocabularySet $set): Collection
    {
        return VocabularySet::query()
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->where(function ($query) use ($set): void {
                $query->where('parent_id', (int) $set->id);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->flatMap(function (VocabularySet $child): Collection {
                if ($child->canBeLaunched()) {
                    return collect([$child]);
                }

                return $this->playableDescendantSets($child);
            })
            ->values();
    }

    private function copySetTreeToCustom(VocabularySet $source, ?VocabularySet $parent = null): VocabularySet
    {
        $copy = VocabularySet::query()->create([
            'title' => $parent instanceof VocabularySet ? $source->title : $source->title.' copy',
            'description' => $source->description,
            'parent_id' => $parent?->id,
            'node_type' => $source->isFolder() ? VocabularySet::NODE_FOLDER : VocabularySet::NODE_PLAYABLE,
            'set_type' => VocabularySet::TYPE_TEACHER,
            'source_kind' => VocabularySet::SOURCE_CUSTOM,
            'source_key' => null,
            'owner_user_id' => Auth::id(),
            'visibility' => VocabularySet::VISIBILITY_PRIVATE,
            'sort_order' => $this->nextSetSortOrder($parent ? (int) $parent->id : null),
            'created_by_user_id' => Auth::id(),
            'updated_by_user_id' => Auth::id(),
        ]);

        if ($source->isPlayable()) {
            $this->copyPlayableWords($source, $copy);

            return $copy;
        }

        VocabularySet::query()
            ->where('parent_id', (int) $source->id)
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->each(fn (VocabularySet $child): VocabularySet => $this->copySetTreeToCustom($child, $copy));

        return $copy;
    }

    private function copyPlayableWords(VocabularySet $source, VocabularySet $copy): void
    {
        $wordIds = $source->source_kind === VocabularySet::SOURCE_CUSTOM
            ? VocabularySetWord::query()
                ->where('vocabulary_set_id', (int) $source->id)
                ->orderBy('position')
                ->orderBy('id')
                ->pluck('word_id')
                ->map(fn ($id): int => (int) $id)
                ->values()
                ->all()
            : $this->wordIdsForClone($source);

        foreach (array_values(array_unique($wordIds)) as $index => $wordId) {
            VocabularySetWord::query()->firstOrCreate(
                [
                    'vocabulary_set_id' => (int) $copy->id,
                    'word_id' => (int) $wordId,
                ],
                [
                    'position' => $index + 1,
                    'added_by_user_id' => Auth::id(),
                ]
            );
        }
    }

    /**
     * @return list<int>
     */
    private function wordIdsForClone(VocabularySet $source): array
    {
        $provider = app(VocabularyWordProvider::class);
        $sets = $source->canBeLaunched()
            ? collect([$source])
            : $this->playableDescendantSets($source);

        return $sets
            ->flatMap(function (VocabularySet $set) use ($provider): array {
                if ($this->usesMappedVocabularyTable($set)) {
                    return $provider
                        ->browseWordRecordsForSet($set, 5000)
                        ->pluck('id')
                        ->map(fn ($id): int => (int) $id)
                        ->all();
                }

                return collect($provider->playableWordsForSet($set, 'hangman', 500))
                    ->map(fn (array $payload): ?int => $this->wordIdFromPayload($payload))
                    ->filter()
                    ->all();
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function browseWordsForSelectedSet(
        VocabularySet $selectedSet,
        VocabularyWordProvider $provider,
        VocabularyAudioResolver $audioResolver,
        WrongOptionGenerator $wrongOptionGenerator,
        VocabularyDifficultyEstimator $difficultyEstimator,
        int $sourceWordLimit
    ): Collection {
        if ($selectedSet->source_kind === VocabularySet::SOURCE_CUSTOM) {
            return $selectedSet
                ->words()
                ->limit($sourceWordLimit)
                ->get()
                ->map(fn (Cambradge_word_api $word): array => $this->wordRow($word, $audioResolver, $wrongOptionGenerator, $difficultyEstimator));
        }

        if ($this->usesMappedVocabularyTable($selectedSet)) {
            return $provider
                ->browseWordRecordsForSet($selectedSet, $sourceWordLimit)
                ->map(fn (Cambradge_word_api $word): array => $this->wordRow($word, $audioResolver, $wrongOptionGenerator, $difficultyEstimator));
        }

        return collect($provider->playableWordsForSet($selectedSet, 'hangman', $sourceWordLimit))
            ->map(fn (array $payload): array => $this->wordRowFromPayload($payload));
    }

    private function usesMappedVocabularyTable(VocabularySet $set): bool
    {
        return in_array($set->source_kind, [
            VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
            VocabularySet::SOURCE_LEGACY_PHONICS,
            VocabularySet::SOURCE_LEGACY_GROUP,
            VocabularySet::SOURCE_LEGACY_DIFFICULTY,
        ], true);
    }

    private function wordIdFromPayload(array $payload): ?int
    {
        $id = (int) ($payload['id'] ?? 0);

        if ($id > 0) {
            return $id;
        }

        $wordText = trim((string) ($payload['text'] ?? ''));

        if ($wordText === '') {
            return null;
        }

        $word = Cambradge_word_api::query()
            ->whereRaw('LOWER(TRIM(word)) = ?', [mb_strtolower($wordText)])
            ->first();

        if (! $word instanceof Cambradge_word_api) {
            $word = Cambradge_word_api::query()->create([
                'word' => $wordText,
                'image' => null,
                'us_sound' => null,
                'uk_sound' => null,
                'difficulty_levels' => null,
                'wrong_spelling' => null,
            ]);
        }

        return (int) $word->id;
    }

    private function authorizeAudioReplacement(): void
    {
        abort_unless($this->canReplaceAudio(), 403);
    }

    private function replaceWordAudioFromPayload(CambridgeAudioDownloadService $downloader, Cambradge_word_api $word, string $mode): void
    {
        match ($mode) {
            'cambridge' => $downloader->replaceFromPartialPath($word, $this->audioPartialPath),
            'url' => $downloader->replaceFromCompleteUrl($word, $this->audioCompleteUrl),
            'upload' => $downloader->replaceFromLocalFile($word, $this->audioUpload?->getRealPath() ?? '', $this->audioUpload?->getClientOriginalName()),
            default => throw new \InvalidArgumentException('Choose an audio source.'),
        };
    }

    private function replaceNewWordAudioFromPayload(CambridgeAudioDownloadService $downloader, Cambradge_word_api $word, string $mode): void
    {
        match ($mode) {
            'cambridge' => $downloader->replaceFromPartialPath($word, $this->newWordAudioPartialPath),
            'url' => $downloader->replaceFromCompleteUrl($word, $this->newWordAudioCompleteUrl),
            'upload' => $downloader->replaceFromLocalFile($word, $this->newWordAudioUpload?->getRealPath() ?? '', $this->newWordAudioUpload?->getClientOriginalName()),
            default => throw new \InvalidArgumentException('Choose an audio source.'),
        };
    }

    private function storeNewWordAudioFromPayload(CambridgeAudioDownloadService $downloader, string $word, string $mode): string
    {
        return match ($mode) {
            'cambridge' => $downloader->storeNewFromPartialPath($word, $this->newWordAudioPartialPath),
            'url' => $downloader->storeNewFromCompleteUrl($word, $this->newWordAudioCompleteUrl),
            'upload' => $downloader->storeNewFromLocalFile($word, $this->newWordAudioUpload?->getRealPath() ?? '', $this->newWordAudioUpload?->getClientOriginalName()),
            default => throw new \InvalidArgumentException('Choose an audio source.'),
        };
    }

    private function audioErrorField(string $mode): string
    {
        return match ($mode) {
            'url' => 'audioCompleteUrl',
            'upload' => 'audioUpload',
            default => 'audioPartialPath',
        };
    }

    private function newWordAudioErrorField(string $mode): string
    {
        return match ($mode) {
            'url' => 'newWordAudioCompleteUrl',
            'upload' => 'newWordAudioUpload',
            default => 'newWordAudioPartialPath',
        };
    }

    private function safeMediaStem(string $word): string
    {
        return Str::of($word)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString() ?: 'word';
    }

    private function normalizeNewVocabularyWord(string $word): string
    {
        $normalized = Str::of($word)
            ->squish()
            ->lower()
            ->toString();

        $allCapsWords = collect(config('vocabulary.games.all_caps_words', []))
            ->map(fn ($value): string => Str::upper((string) $value))
            ->all();

        return in_array(Str::upper($normalized), $allCapsWords, true)
            ? Str::upper($normalized)
            : $normalized;
    }

    private function wordQualityMetadataReady(): bool
    {
        static $ready = null;

        if ($ready !== null) {
            return $ready;
        }

        $ready = Schema::hasColumn('cambradge_words_api', 'wrong_spelling_rules')
            && Schema::hasColumn('cambradge_words_api', 'wrong_spelling_source')
            && Schema::hasColumn('cambradge_words_api', 'difficulty_reason')
            && Schema::hasColumn('cambradge_words_api', 'difficulty_source');

        return $ready;
    }

    /**
     * @return array<string, mixed>
     */
    private function manualDifficultyUpdate(?string $difficulty): array
    {
        $updates = ['difficulty_levels' => $difficulty];

        if ($this->wordQualityMetadataReady()) {
            $updates['difficulty_reason'] = null;
            $updates['difficulty_source'] = 'manual';
        }

        return $updates;
    }

    private function applyWrongOptionGeneration(Cambradge_word_api $word): void
    {
        $difficulty = trim((string) ($word->difficulty_levels ?? ''));
        $options = app(WrongOptionGenerator::class)->spellingOptionsDetailed((string) $word->word, null, 3, $difficulty);

        $updates = [
            'wrong_spelling' => $options === [] ? null : implode("\n", array_column($options, 'text')),
        ];

        if ($this->wordQualityMetadataReady()) {
            $updates['wrong_spelling_rules'] = $options === [] ? null : $options;
            $updates['wrong_spelling_source'] = $options === [] ? 'legacy_unknown' : 'generated';
        }

        $word->forceFill($updates)->save();
    }

    private function applyDifficultyEstimate(Cambradge_word_api $word): void
    {
        $estimate = app(VocabularyDifficultyEstimator::class)->estimateWithReason((string) $word->word);
        $updates = ['difficulty_levels' => $estimate['level']];

        if ($this->wordQualityMetadataReady()) {
            $updates['difficulty_reason'] = $estimate['reason'];
            $updates['difficulty_source'] = 'generated';
        }

        $word->forceFill($updates)->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBulkPreview(string $action): array
    {
        $metadataReady = $this->wordQualityMetadataReady();
        $query = $this->wordBankQuery();

        if ($metadataReady && $action === 'accept_difficulty') {
            $query->where('difficulty_source', 'generated');
        }

        $offset = max(0, $this->bulkBatchPage) * 500;
        $rows = $query
            ->offset($offset)
            ->limit(501)
            ->get();
        $tooMany = $rows->count() > 500;
        $rows = $rows->take(500);
        $updateIds = [];
        $skippedManual = [];
        $preview = [];

        foreach ($rows as $word) {
            $difficultySource = (string) ($word->difficulty_source ?? 'legacy_unknown');

            if ($action === 'accept_difficulty' && $metadataReady && $difficultySource === 'manual') {
                $skippedManual[] = (string) $word->word;

                continue;
            }

            if ($action === 'accept_difficulty' && trim((string) $word->difficulty_levels) === '') {
                continue;
            }

            $updateIds[] = (int) $word->id;
            $preview[] = $this->bulkPreviewRow($word, $action);
        }

        return [
            'action' => $action,
            'label' => match ($action) {
                'wrong_options' => 'Regenerate wrong options',
                'difficulty' => 'Re-estimate difficulty',
                default => 'Accept suggested levels',
            },
            'filter' => $this->wordBankFilterSummary(),
            'batch_page' => $this->bulkBatchPage,
            'batch_label' => 'Rows '.($offset + 1).' - '.($offset + count($updateIds)),
            'has_previous' => $this->bulkBatchPage > 0,
            'too_many' => $tooMany,
            'update_count' => count($updateIds),
            'skip_count' => count($skippedManual),
            'skipped_manual' => array_slice($skippedManual, 0, 10),
            'preview' => $preview,
            'update_ids' => $updateIds,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bulkPreviewRow(Cambradge_word_api $word, string $action): array
    {
        if ($action === 'wrong_options') {
            $options = app(WrongOptionGenerator::class)->spellingOptionsDetailed(
                (string) $word->word,
                null,
                3,
                (string) ($word->difficulty_levels ?? '')
            );

            return [
                'id' => (int) $word->id,
                'word' => (string) $word->word,
                'before' => trim((string) ($word->wrong_spelling ?? '')),
                'after' => implode(', ', array_column($options, 'text')),
            ];
        }

        if ($action === 'difficulty') {
            $estimate = app(VocabularyDifficultyEstimator::class)->estimateWithReason((string) $word->word);

            return [
                'id' => (int) $word->id,
                'word' => (string) $word->word,
                'before' => trim((string) ($word->difficulty_levels ?? '')),
                'after' => 'Level '.$estimate['level'].' - '.$estimate['reason'],
            ];
        }

        return [
            'id' => (int) $word->id,
            'word' => (string) $word->word,
            'before' => trim((string) ($word->difficulty_reason ?? 'suggested')),
            'after' => 'accepted as manual',
        ];
    }

    private function resetBulkPreview(): void
    {
        $this->bulkPreviewOpen = false;
        $this->bulkAction = '';
        $this->bulkBatchPage = 0;
        $this->bulkPreview = [];
        $this->bulkSelectedWordIds = [];
    }

    private function wordBankQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Cambradge_word_api::query()
            ->orderBy('word')
            ->orderBy('id');

        if (trim($this->wordSearch) !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($this->wordSearch)).'%';
            $query->where('word', 'like', $term);
        }

        if (in_array($this->wordBankDifficultyFilter, ['1', '2', '3', '4', '5', '6'], true)) {
            $query->where('difficulty_levels', $this->wordBankDifficultyFilter);
        }

        if ((int) $this->wordBankGroupFilter > 0 && Schema::hasTable('group_words')) {
            $query->whereIn('id', Group_word::query()
                ->where('category_id', (int) $this->wordBankGroupFilter)
                ->select('camb_sound_id'));
        }

        return $query;
    }

    private function wordBankFilterSummary(): string
    {
        $parts = [];

        if (trim($this->wordSearch) !== '') {
            $parts[] = 'search "'.trim($this->wordSearch).'"';
        }

        if ($this->wordBankDifficultyFilter !== '') {
            $parts[] = 'level '.$this->wordBankDifficultyFilter;
        }

        if ((int) $this->wordBankGroupFilter > 0) {
            $parts[] = 'group '.$this->wordBankGroupFilter;
        }

        return $parts === [] ? 'All word bank rows' : implode(', ', $parts);
    }

    /**
     * @return array<string, mixed>
     */
    private function wordRow(
        Cambradge_word_api $word,
        VocabularyAudioResolver $audioResolver,
        WrongOptionGenerator $wrongOptionGenerator,
        VocabularyDifficultyEstimator $difficultyEstimator,
    ): array {
        $audio = $audioResolver->resolve($word);
        $storedWrongOptions = trim((string) ($word->wrong_spelling ?? ''));
        $storedWrongRules = is_array($word->wrong_spelling_rules ?? null) ? $word->wrong_spelling_rules : [];
        $wrongSource = (string) ($word->wrong_spelling_source ?? ($storedWrongOptions === '' ? 'generated' : 'legacy_unknown'));
        $generatedWrongOptions = $wrongOptionGenerator->spellingOptionsDetailed((string) $word->word, null, 3, (string) ($word->difficulty_levels ?? ''));
        $storedDifficulty = trim((string) ($word->difficulty_levels ?? ''));
        $difficultySource = (string) ($word->difficulty_source ?? ($storedDifficulty === '' ? 'generated' : 'legacy_unknown'));
        $estimatedDifficulty = $difficultyEstimator->estimateWithReason((string) $word->word);
        $difficultyReason = trim((string) ($word->difficulty_reason ?? ''));
        $displayDifficultyReason = $difficultyReason !== '' ? $difficultyReason : $estimatedDifficulty['reason'];
        $ruleBadges = $wrongSource === 'generated' && $storedWrongRules !== []
            ? $storedWrongRules
            : [];

        return [
            'id' => (int) $word->id,
            'word' => (string) $word->word,
            'wrong_spelling' => $storedWrongOptions,
            'wrong_options_preview' => $storedWrongOptions !== ''
                ? $storedWrongOptions
                : implode(', ', array_column($generatedWrongOptions, 'text')),
            'wrong_options_generated' => $wrongSource === 'generated',
            'wrong_options_missing' => $storedWrongOptions === '',
            'wrong_options_suggested' => $storedWrongOptions === '',
            'wrong_option_rules' => collect($ruleBadges)
                ->filter(fn ($rule): bool => is_array($rule) && isset($rule['label']))
                ->map(fn (array $rule): string => (string) $rule['label'])
                ->unique()
                ->values()
                ->all(),
            'difficulty' => $storedDifficulty,
            'suggested_difficulty' => $estimatedDifficulty['level'],
            'difficulty_inferred' => $difficultySource !== 'manual',
            'difficulty_reason' => $displayDifficultyReason,
            'image' => (string) ($word->image ?? ''),
            'image_url' => $this->wordImageUrl((string) ($word->image ?? '')),
            'groups' => $this->groupLabelsForWord((int) $word->id),
            'audio' => $audio->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function wordRowFromPayload(array $payload): array
    {
        return [
            'id' => (int) ($payload['id'] ?? 0),
            'word' => (string) ($payload['displayText'] ?? $payload['text'] ?? ''),
            'wrong_spelling' => implode(', ', (array) ($payload['wrongOptions'] ?? [])),
            'wrong_options_preview' => implode(', ', (array) ($payload['wrongOptions'] ?? [])),
            'wrong_options_generated' => true,
            'wrong_options_missing' => false,
            'wrong_options_suggested' => false,
            'wrong_option_rules' => [],
            'difficulty' => '',
            'suggested_difficulty' => '',
            'difficulty_inferred' => false,
            'difficulty_reason' => '',
            'image' => '',
            'image_url' => null,
            'groups' => [],
            'audio' => [
                'available' => filled($payload['audioUrl'] ?? null),
                'url' => (string) ($payload['audioUrl'] ?? ''),
                'source' => 'resolved audio',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function groupLabelsForWord(int $wordId): array
    {
        if (! Schema::hasTable('group_words') || ! Schema::hasTable('category_group_word')) {
            return [];
        }

        return Category_group_word::query()
            ->whereIn('id', Group_word::query()
                ->where('camb_sound_id', $wordId)
                ->select('category_id'))
            ->orderBy('order')
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name): string => (string) $name)
            ->values()
            ->all();
    }

    private function wordImageUrl(string $image): ?string
    {
        $image = trim($image);

        if ($image === '') {
            return null;
        }

        if (preg_match('/\Ahttps?:\/\//i', $image)) {
            return $image;
        }

        return '/'.ltrim(str_replace('\\', '/', $image), '/');
    }

    private function nextSetSortOrder(?int $parentId = null): int
    {
        return ((int) VocabularySet::query()
            ->where('owner_user_id', Auth::id())
            ->where('parent_id', $parentId)
            ->max('sort_order')) + 1;
    }

    private function nextWordPosition(int $setId): int
    {
        return ((int) VocabularySetWord::query()
            ->where('vocabulary_set_id', $setId)
            ->max('position')) + 1;
    }

    private function reindexWords(int $setId): void
    {
        VocabularySetWord::query()
            ->where('vocabulary_set_id', $setId)
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->values()
            ->each(function (VocabularySetWord $membership, int $index): void {
                $membership->forceFill(['position' => $index + 1])->save();
            });
    }

    private function cleanText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function teacherAccessContexts(): Collection
    {
        $user = Auth::user();

        if (! $user?->hasAnyRole(['teacher', 'admin', 'super_admin', 'owner'])) {
            return collect();
        }

        return TeacherSubjectClass::query()
            ->availableForTeacher()
            ->when($user->hasRole('teacher') && ! $user->hasAnyRole(['admin', 'super_admin', 'owner']), fn ($query) => $query
                ->where('user_teacher_coteacher_id', (int) $user->id))
            ->orderBy('class_name')
            ->orderBy('subject_name')
            ->limit(1000)
            ->get();
    }

    private function displaySubjectName(string $subjectName): string
    {
        $subjectName = trim($subjectName);

        return Str::lower($subjectName) === 'english' ? 'Language and Literature' : $subjectName;
    }

    private function wordGroupOptions(): Collection
    {
        if (! Schema::hasTable('category_group_word')) {
            return collect();
        }

        return Category_group_word::query()
            ->where('active', 1)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
