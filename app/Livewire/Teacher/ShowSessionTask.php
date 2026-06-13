<?php

namespace App\Livewire\Teacher;

use App\Helpers\Helpers;
use App\Http\Controllers\VocabularyAssignmentController;
use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\GeneralLibraryResource;
use App\Models\LibraryResource;
use App\Models\SessionMaterial;
use App\Models\SessionTask;
use App\Models\TaskType;
use App\Models\VocabularyGameAssignment;
use App\Models\VocabularySet;
use App\Services\Library\LegacyLibraryTaskResourceCatalog;
use App\Services\Library\GeneralLibraryAttachmentAdapter;
use App\Services\Library\LibraryFileRetentionService;
use App\Services\Library\LibraryResourceAttachmentWriter;
use App\Services\Library\LibraryResourceQuery;
use App\Services\Library\LibraryResourceValidator;
use App\Services\SeriesLibrarySourceResolver;
use App\Services\Vocabulary\VocabularySourceRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ShowSessionTask extends Component
{
    use WithFileUploads; // لرفع الملفات

    private const MAX_ATTACHMENT_FILE_KB = 51200;

    public bool $show = false;

    public ?int $sessionId = null;

    /** الخيارات القادمة من DB */
    public array $taskTypes = [];      // ← لازم تكون public

    public ?int $task_type_id = null;  // ← الـ select بيرتبط بيه

    // حقول الـ Task
    public ?string $title = null;

    public ?string $description = null;

    public ?int $default_points = 5;

    public ?int $max_points = 10;

    public ?string $attach_title = null;

    public ?string $due_date = null;

    public ?string $link = null;     // للنوع link

    public ?string $youtube = null;  // للنوع youtube

    // ملفات متعددة عند اختيار النوع "file"
    public array $files = []; // wire:model multiple

    public ?int $taskId = null;

    public bool $isEdit = false;

    public bool $uploadsInProgress = false;

    public bool $locked = false; // true = قراءة فقط

    public ?int $sessionSubjectId = null;

    public array $selectedLibraryResourceIds = [];

    public array $selectedLibraryResources = [];

    public array $existingFiles = [];

    public array $existingLinks = [];

    public array $existingYoutubes = [];

    // IDs للمرفقات اللي المفروض تتحذف فعليًا عند الضغط على Update Task
    public array $attachmentsToDelete = [];

    // new

    // ملفات مؤقتة تُختار من input قبل الحفظ النهائي
    public array $fileBuffer = [];       // المجموعة اللي لسه مختاراها من الـ input

    public array $finalFiles = [];       // كل الملفات اللي هتتحفظ مع التاسك

    // لينكات عادية
    public string $link_title_input = '';

    public string $link_url_input = '';

    public array $links = []; // كل اللينكات المضافة قبل الحفظ

    // لينكات يوتيوب
    public string $youtube_title_input = '';

    public string $youtube_url_input = '';

    public array $youtubes = []; // كل لينكات اليوتيوب المضافة

    // لعرض كل المرفقات القديمة عند الـ Edit
    public array $existingAttachments = [];  // files + links + youtube

    public array $attachmentDraftOrder = [];

    // إظهار/إخفاء فورم اليوتيوب
    public bool $showYoutubeForm = false;

    public int $defaultTaskTypeId = 7;

    public array $vocabularyGameSetIds = [];

    public array $vocabularyAllowedGames = ['hangman', 'missing_letter', 'spelling_choice'];

    public string $vocabularyDifficultyPolicy = VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE;

    // ولو عندك نفس الفكرة للـ Link، كمان ضيف:
    public bool $showLinkForm = false;

    protected array $messages = [
        'files.max' => 'Each file must be 50 MB or less.',
        'files.*.max' => 'Each file must be 50 MB or less.',
        'files.*.extensions' => 'Unsupported file type. Please upload a document, image, audio file, or video we allow.',
        'finalFiles.*.max' => 'Each file must be 50 MB or less.',
        'finalFiles.*.extensions' => 'Unsupported file type. Please upload a document, image, audio file, or video we allow.',
    ];

    public function clickAddLink(): void
    {

        $this->reset([

            'link_title_input', 'link_url_input',
            'youtube_title_input', 'youtube_url_input',
        ]);
        $this->showLinkForm = true;
        $this->showYoutubeForm = false; // لو حابة تقفلي اليوتيوب لما تفتحي اللينك
    }

    public function clickAddYoutube(): void
    {
        $this->reset([

            'link_title_input', 'link_url_input',
            'youtube_title_input', 'youtube_url_input',
        ]);
        $this->showYoutubeForm = true;
        $this->showLinkForm = false; // نفس الفكرة
    }

    public function setUploadsInProgress(bool $value): void
    {
        $this->uploadsInProgress = $value;
    }

    public function mount(): void
    {
        // تحميل الأنواع من DB (id/name/slug)
        $this->taskTypes = TaskType::orderBy('title')
            ->get(['id', 'title', 'table_name', 'default_points', 'max_points'])
            ->map(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'table_name' => $t->table_name,
                'default_points' => $t->default_points,
                'max_points' => $t->max_points,
            ])
            ->toArray();
    }

    protected function resolveOwnedSessionOrFail(?int $sessionId = null): ClassSession
    {
        return ClassSession::query()
            ->whereKey($sessionId ?? $this->sessionId)
            ->normal()
            ->whereHas('teacherSubjectClass', fn ($q) => $q->where('user_teacher_coteacher_id', Auth::id()))
            ->firstOrFail();
    }

    protected function resolveOwnedTaskOrFail(?int $taskId = null): SessionTask
    {
        return SessionTask::query()
            ->whereKey($taskId ?? $this->taskId)
            ->whereHas('classSession', fn ($q) => $q
                ->normal()
                ->whereHas('teacherSubjectClass', fn ($qq) => $qq->where('user_teacher_coteacher_id', Auth::id())))
            ->firstOrFail();
    }

    #[On('open-session-task-modal')]
    public function open(int $sessionId = 0): void
    {
        $this->resetValidation();
        // $this->reset(['title','description','files','task_type_id', 'link', 'youtube']);

        $this->reset([
            'title',
            'description',
            'files',
            'task_type_id',
            'link',
            'youtube',
            'existingFiles', // لو ضفناها قبل كده
            'taskId',
            'existingLinks',
            'existingYoutubes',
            'finalFiles',
            'youtubes',
            'link_title_input',
            'link_url_input',
            'youtube_title_input',
            'youtube_url_input',
            'attachmentsToDelete',
            'existingAttachments',
            'attachmentDraftOrder',
            'selectedLibraryResourceIds',
            'selectedLibraryResources',
            'vocabularyGameSetIds',
            'vocabularyAllowedGames',
            'vocabularyDifficultyPolicy',

        ]);

        $this->files = [];
        $this->finalFiles = [];
        $this->links = [];
        $this->youtubes = [];
        $this->existingFiles = [];
        $this->existingLinks = [];
        $this->existingYoutubes = [];
        $this->attachmentsToDelete = [];
        $this->existingAttachments = [];
        $this->attachmentDraftOrder = [];
        $this->selectedLibraryResourceIds = [];
        $this->selectedLibraryResources = [];
        $this->vocabularyGameSetIds = [];
        $this->vocabularyAllowedGames = ['hangman', 'missing_letter', 'spelling_choice'];
        $this->vocabularyDifficultyPolicy = VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE;

        $this->link_title_input = '';
        $this->link_url_input = '';
        $this->youtube_title_input = '';
        $this->youtube_url_input = '';

        $this->showLinkForm = false;
        $this->showYoutubeForm = false;
        $this->uploadsInProgress = false;

        $this->isEdit = false;
        $this->locked = false;
        // ✅ default type في حالة add فقط
        $this->task_type_id = $this->defaultTaskTypeId;
        $this->default_points = 5;
        $this->max_points = 10;
        $session = $this->resolveOwnedSessionOrFail($sessionId);
        $this->sessionId = (int) $session->id;   // ← هتستقبل 15 صح
        $this->sessionSubjectId = (int) $session->subject_id;
        $this->show = true;
    }

    // ↓↓↓ ضعها أسفل الخصائص
    // public function getIsFileTypeProperty(): bool
    // {
    //     return (int) $this->task_type_id === 7; // file
    // }
    // public function getIsLinkTypeProperty(): bool
    // {
    //     return (int) $this->task_type_id === 9; // link
    // }
    // public function getIsYoutubeTypeProperty(): bool
    // {
    //     return (int) $this->task_type_id === 8; // youtube
    // }

    public function getIsFileTypeProperty(): bool
    {
        return true;
    }

    public function getIsLinkTypeProperty(): bool
    {
        return true;
    }

    public function getIsYoutubeTypeProperty(): bool
    {
        return true;
    }

    // 1) ارجعي النوع المختار كـ array أو null
    public function getSelectedTypeProperty(): ?array
    {
        if (! $this->task_type_id) {
            return null;
        }

        return collect($this->taskTypes)->firstWhere('id', (int) $this->task_type_id);
    }

    // 2) هل النوع المختار من نوع ملفات؟
    // public function getIsFileTypeProperty(): bool
    // {
    //     $t = $this->selectedType; // ← يستخدم getSelectedTypeProperty
    //     if (!$t) return false;
    //
    //     $titleLower = strtolower($t['title'] ?? '');
    //     $tableName  = $t['table_name'] ?? '';
    //
    //     // أيهما ينطبق من جدولك: table_name=attachment_files أو title=file
    //     return $tableName === 'attachment_files' || $titleLower === 'file';
    // }

    // هُوك على تغيير النوع: املى النقاط من TaskType
    // public function updatedTaskTypeId($value): void
    // {
    //     $this->task_type_id = $value ? (int) $value : null; // ← تأكيد أنها int
    //     $t = $this->selectedType;
    //
    //     if ($t) {
    //         $this->max_points     = $t['max_points'] ?? null;
    //         $this->default_points = $t['default_points'] ?? null;
    //     } else {
    //         $this->max_points = $this->default_points = null;
    //     }
    // }

    public function updatedTaskTypeId(): void
    {
        // منطق نقاطك الافتراضية لو كان موجود...
        //   if ($this->selectedType) {
        //       $this->default_points = (int) ($this->selectedType['default_points'] ?? 0);
        //       $this->max_points     = (int) ($this->selectedType['max_points'] ?? 0);
        //   }

        $t = $this->selectedType;

        // fallback آمن يمنع الصفر
        $dp = data_get($t, 'default_points');
        $mp = data_get($t, 'max_points');

        $this->default_points = is_null($dp) ? 5 : (int) $dp;
        $this->max_points = is_null($mp) ? 10 : (int) $mp;

        // نظّف مدخلات النوع الآخر
        // Attachments are independent from the launch task type.
        $this->rebuildAttachmentState();
    }

    // save on add task
    public function save(): void
    {

        // امسحي أي رسائل قديمة خاصة بـ "نسيتي تضيفي"
        $this->resetErrorBag(['links_pending', 'youtubes_pending']);

        // ✅ لو المستخدم كتب لينك ولم يضغط Add Link
        $linkTitle = trim((string) $this->link_title_input);
        $linkUrl = trim((string) $this->link_url_input);

        // if (($linkTitle !== '' || $linkUrl !== '') ) {
        //     $this->addError('links_pending', ' Please click "Add Link" first.');
        //     return; // مهم جداً: يمنع الحفظ ويمنع قفل البوب
        // }

        if (($linkTitle !== '' && $linkUrl !== '')) {
            $this->addError('links_pending', ' Please click "Add Link" first.');

            return; // مهم جداً: يمنع الحفظ ويمنع قفل البوب
        }

        if (($linkTitle !== '' && $linkUrl == '')) {
            $this->addError('links_pending', ' Please enter the "Link URL" then click "Add Link".');

            return; // مهم جداً: يمنع الحفظ ويمنع قفل البوب
        }
        if (($linkTitle == '' && $linkUrl !== '')) {
            $this->addError('links_pending', ' Please enter the "Link Title" then click "Add Link".');

            return; // مهم جداً: يمنع الحفظ ويمنع قفل البوب
        }

        // ✅ لو المستخدم كتب يوتيوب ولم يضغط Add Youtube
        $ytTitle = trim((string) $this->youtube_title_input);
        $ytUrl = trim((string) $this->youtube_url_input);

        if (($ytTitle !== '' && $ytUrl !== '')) {
            $this->addError('youtubes_pending', ' Please click "Add Youtube" first.');

            return;
        }
        if (($ytTitle !== '' && $ytUrl == '')) {
            $this->addError('youtubes_pending', ' Please  enter the "Youtube Link" then  click "Add Youtube".');

            return;
        }
        if (($ytTitle == '' && $ytUrl !== '')) {
            $this->addError('youtubes_pending', ' Please  enter the "Youtube Title" then click "Add Youtube" .');

            return;
        }

        $this->resetErrorBag(['content', 'files']);

        if ($this->uploadsInProgress) {
            $this->addError('files', 'Wait for uploads to finish before saving.');

            return;
        }
        $this->validate($this->rules()); // قواعد تحت

        $session = $this->resolveOwnedSessionOrFail($this->sessionId);
        $this->sessionSubjectId = (int) $session->subject_id;
        $this->refreshSelectedLibraryResources();

        if (! $this->hasTaskContent()) {
            $this->addError('content', 'Add a description or at least one attachment.');

            return;
        }

        $sessionMaterialId = SessionMaterial::where('session_id', $session->id)->value('id');
        $storedPaths = [];

        try {
            DB::transaction(function () use ($session, $sessionMaterialId, &$storedPaths): void {
                $task = SessionTask::create([
                    'class_session_id' => $session->id,
                    'task_type_id' => $this->task_type_id,
                    'title' => $this->title,
                    'description' => $this->description,
                    'default_points' => $this->default_points,
                    'max_points' => $this->max_points,
                    'session_material_id' => $sessionMaterialId,
                    'created_by_teacher_id' => Auth::id(),
                ]);

                $maxSort = SessionTask::where('class_session_id', $this->sessionId)->max('sort') ?? 0;
                $task->update(['sort' => $maxSort + 1]);

                $this->createAttachmentsForTask($task, $session, $storedPaths);
                $this->createVocabularyGameAttachments($task, $session);
            });
        } catch (\Throwable $e) {
            foreach ($storedPaths as $storedPath) {
                $this->deletePublicPathQuietly($storedPath);
            }

            report($e);
            $this->dispatch('toast', ['type' => 'danger', 'message' => 'Failed to save task.']);

            return;
        }

        $this->reset([
            'title', 'description', 'task_type_id', 'default_points', 'max_points',
            'files', 'finalFiles', 'links', 'youtubes',
            'selectedLibraryResourceIds', 'selectedLibraryResources',
            'attachmentDraftOrder',
            'vocabularyGameSetIds', 'vocabularyAllowedGames', 'vocabularyDifficultyPolicy',
            'link_title_input', 'link_url_input',
            'youtube_title_input', 'youtube_url_input',
        ]);

        $this->show = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Task saved.']);
        $this->dispatch('session-task-added', sessionId: $this->sessionId)
            ->to(SessionsBoard::class);

    }

    public function updateTask(): void
    {
        if ($this->locked) {
            return;
        }

        // امسحي رسائل "pending" القديمة
        $this->resetErrorBag(['links_pending', 'youtubes_pending']);

        $linkTitle = trim((string) $this->link_title_input);
        $linkUrl = trim((string) $this->link_url_input);

        if ($linkTitle !== '' && $linkUrl !== '') {
            $this->addError('links_pending', ' Please click "Add Link" first.');

            return;
        }
        if (($linkTitle !== '' && $linkUrl == '')) {
            $this->addError('links_pending', ' Please enter the "Link URL" then click "Add Link".');

            return; // مهم جداً: يمنع الحفظ ويمنع قفل البوب
        }
        if (($linkTitle == '' && $linkUrl !== '')) {
            $this->addError('links_pending', ' Please enter the "Link Title" then click "Add Link".');

            return; // مهم جداً: يمنع الحفظ ويمنع قفل البوب
        }

        $ytTitle = trim((string) $this->youtube_title_input);
        $ytUrl = trim((string) $this->youtube_url_input);

        if ($ytTitle !== '' && $ytUrl !== '') {
            $this->addError('youtubes_pending', ' Please click "Add Youtube" first.');

            return;
        }

        if (($ytTitle !== '' && $ytUrl == '')) {
            $this->addError('youtubes_pending', ' Please  enter the "Youtube Link" then  click "Add Youtube".');

            return;
        }
        if (($ytTitle == '' && $ytUrl !== '')) {
            $this->addError('youtubes_pending', ' Please  enter the "Youtube Title" then click "Add Youtube" .');

            return;
        }

        // نفس قواعد الـ save()
        $this->resetErrorBag(['content', 'files']);

        if ($this->uploadsInProgress) {
            $this->addError('files', 'Wait for uploads to finish before saving.');

            return;
        }
        $this->validate($this->rules());

        $task = $this->resolveOwnedTaskOrFail($this->taskId);
        $task->loadMissing('classSession');
        abort_unless((int) $task->class_session_id === (int) $this->sessionId && $task->classSession, 404);

        $session = $task->classSession;
        $this->sessionSubjectId = (int) $session->subject_id;
        $this->refreshSelectedLibraryResources();

        if (! $this->hasTaskContent()) {
            $this->addError('content', 'Add a description or at least one attachment.');

            return;
        }

        // 1) تعديل بيانات التاسك
        $storedPaths = [];
        $pathsToDelete = [];

        try {
            DB::transaction(function () use ($task, $session, &$storedPaths, &$pathsToDelete): void {
                $task->update([
                    'task_type_id' => (int) $this->task_type_id,
                    'title' => $this->title,
                    'description' => $this->description,
                    'default_points' => (int) ($this->default_points ?? 0),
                    'max_points' => (int) ($this->max_points ?? 0),
                ]);

                $this->createAttachmentsForTask($task, $session, $storedPaths);
                $this->createVocabularyGameAttachments($task, $session);

                if (! empty($this->attachmentsToDelete)) {
                    $attachments = AttachmentFile::where('session_task_id', $task->id)
                        ->whereIn('id', $this->attachmentsToDelete)
                        ->get();

                    foreach ($attachments as $attachment) {
                        if ($attachment->hasStoredFilePath()) {
                            $pathsToDelete[] = $attachment->path;
                        }

                        $attachment->delete();
                    }
                }
            });
        } catch (\Throwable $e) {
            foreach ($storedPaths as $storedPath) {
                $this->deletePublicPathQuietly($storedPath);
            }

            report($e);
            $this->dispatch('toast', ['type' => 'danger', 'message' => 'Failed to update task.']);

            return;
        }

        foreach ($pathsToDelete as $storedPath) {
            $this->deletePublicPathQuietly($storedPath);
        }

        $this->attachmentsToDelete = [];
        $this->finalFiles = [];
        $this->links = [];
        $this->youtubes = [];
        $this->selectedLibraryResourceIds = [];
        $this->selectedLibraryResources = [];
        $this->attachmentDraftOrder = [];
        $this->vocabularyGameSetIds = [];
        $this->vocabularyAllowedGames = ['hangman', 'missing_letter', 'spelling_choice'];
        $this->vocabularyDifficultyPolicy = VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE;
        $this->show = false;
        $this->isEdit = false;

        $this->dispatch('session-task-updated', sessionId: $this->sessionId);

    }

    private function createAttachmentsForTask(SessionTask $task, ClassSession $session, array &$storedPaths): void
    {
        $this->syncAttachmentDraftOrder();
        $sortOrder = 1;
        $libraryWriter = app(LibraryResourceAttachmentWriter::class);

        foreach ($this->attachmentDraftOrder as $draftKey) {
            [$kind, $rawKey] = $this->splitDraftKey((string) $draftKey);

            if ($kind === 'existing') {
                $attachmentId = (int) $rawKey;

                if ($attachmentId > 0 && ! in_array($attachmentId, $this->attachmentsToDelete, true)) {
                    $sortAttributes = $this->withAttachmentSortOrder([], $sortOrder);

                    if ($sortAttributes !== []) {
                        AttachmentFile::query()
                            ->whereKey($attachmentId)
                            ->where('session_task_id', $task->id)
                            ->update($sortAttributes);
                    }

                    $sortOrder++;
                }

                continue;
            }

            if ($kind === 'pending_file') {
                $upload = $this->findPendingFileByKey($rawKey);

                if ($upload === null) {
                    continue;
                }

                $storedPath = Storage::disk('public')->putFile('attachments', $upload, 'public');
                if ($storedPath === false) {
                    throw new \RuntimeException('Failed to store attachment file.');
                }

                $storedPaths[] = $storedPath;

                AttachmentFile::create($this->withAttachmentSortOrder([
                    'session_task_id' => $task->id,
                    'title' => $upload->getClientOriginalName(),
                    'description' => $this->description,
                    'type' => 'file',
                    'path' => $storedPath,
                    'file_size' => $upload->getSize(),
                    'subject_id' => $session->subject_id,
                    'class_id' => $session->class_id,
                    'teacher_subject_class_id' => $session->teacher_subject_classes_id,
                ], $sortOrder));

                $sortOrder++;

                continue;
            }

            if ($kind === 'link') {
                $link = $this->findLinkByKey($rawKey);

                if ($link === null) {
                    continue;
                }

                AttachmentFile::create($this->withAttachmentSortOrder([
                    'session_task_id' => $task->id,
                    'title' => $link['title'],
                    'description' => $this->description,
                    'type' => 'link',
                    'path' => $link['url'],
                    'file_size' => null,
                    'subject_id' => $session->subject_id,
                    'class_id' => $session->class_id,
                    'teacher_subject_class_id' => $session->teacher_subject_classes_id,
                ], $sortOrder));

                $sortOrder++;

                continue;
            }

            if ($kind === 'youtube') {
                $youtube = $this->findYoutubeByKey($rawKey);

                if ($youtube === null) {
                    continue;
                }

                AttachmentFile::create($this->withAttachmentSortOrder([
                    'session_task_id' => $task->id,
                    'title' => $youtube['title'],
                    'description' => $this->description,
                    'type' => 'youtube',
                    'path' => trim(Helpers::youtubeToEmbed($youtube['url'])),
                    'file_size' => null,
                    'subject_id' => $session->subject_id,
                    'class_id' => $session->class_id,
                    'teacher_subject_class_id' => $session->teacher_subject_classes_id,
                ], $sortOrder));

                $sortOrder++;

                continue;
            }

            if ($kind === 'library') {
                if ($libraryWriter->writeOneForTaskAtSortOrder($task, $session, $rawKey, (int) Auth::id(), $sortOrder)) {
                    $sortOrder++;
                }
            }
        }
    }

    private function createVocabularyGameAttachments(SessionTask $task, ClassSession $session): void
    {
        $selectedSetIds = $this->selectedVocabularyGameSetIds();

        if ($selectedSetIds === []) {
            return;
        }

        abort_unless(Schema::hasTable('vocabulary_game_assignments'), 503);

        $sets = VocabularySet::query()
            ->visibleToTeachers(Auth::id())
            ->whereIn('id', $selectedSetIds)
            ->get()
            ->keyBy('id');

        $allowedGames = $this->normalizedVocabularyAllowedGames();
        $difficultyPolicy = $this->normalizedVocabularyDifficultyPolicy();

        foreach ($selectedSetIds as $setId) {
            $set = $sets->get($setId);

            abort_unless($set instanceof VocabularySet && $set->canBeLaunched(), 422);

            $assignment = VocabularyGameAssignment::query()->create([
                'vocabulary_set_id' => (int) $set->id,
                'assigned_by_user_id' => Auth::id(),
                'audience_type' => VocabularyGameAssignment::AUDIENCE_CLASS,
                'audience_id' => (int) $session->class_id,
                'allowed_games' => $allowedGames,
                'difficulty_policy' => $difficultyPolicy,
                'status' => VocabularyGameAssignment::STATUS_ACTIVE,
            ]);

            $sortOrder = ((int) AttachmentFile::query()
                ->where('session_task_id', $task->id)
                ->max('sort_order')) + 1;

            AttachmentFile::query()->create($this->withAttachmentSortOrder([
                'session_task_id' => (int) $task->id,
                'title' => 'Vocab Game: '.$set->title,
                'description' => $this->description,
                'type' => 'link',
                'path' => VocabularyAssignmentController::assignmentUrl($assignment),
                'file_size' => null,
                'subject_id' => (int) $session->subject_id,
                'class_id' => (int) $session->class_id,
                'teacher_subject_class_id' => (int) $session->teacher_subject_classes_id,
            ], $sortOrder));
        }
    }

    private function selectedVocabularyGameSetIds(): array
    {
        return collect($this->vocabularyGameSetIds)
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizedVocabularyAllowedGames(): array
    {
        $allowed = collect($this->vocabularyAllowedGames)
            ->map(fn ($game): string => (string) $game)
            ->filter(fn (string $game): bool => in_array($game, ['hangman', 'missing_letter', 'spelling_choice'], true))
            ->unique()
            ->values()
            ->all();

        return $allowed === [] ? ['hangman', 'missing_letter', 'spelling_choice'] : $allowed;
    }

    private function normalizedVocabularyDifficultyPolicy(): string
    {
        return in_array($this->vocabularyDifficultyPolicy, [
            VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE,
            'sprout',
            'climber',
            'champion',
        ], true)
            ? $this->vocabularyDifficultyPolicy
            : VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE;
    }

    private function withAttachmentSortOrder(array $attributes, int $sortOrder): array
    {
        if (Schema::hasColumn((new AttachmentFile)->getTable(), 'sort_order')) {
            $attributes['sort_order'] = $sortOrder;
        }

        return $attributes;
    }

    protected function rules(): array
    {
        $rules = [
            'sessionId' => ['required', 'integer', 'exists:class_sessions,id'],
            'task_type_id' => ['required', 'integer', 'exists:task_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_points' => ['required', 'integer', 'min:0'],
            'default_points' => ['required', 'integer', 'min:0', 'lte:max_points'], // default ≤ max

            // ملفات
            'finalFiles' => ['nullable', 'array'],
            'finalFiles.*' => ['file', 'max:'.self::MAX_ATTACHMENT_FILE_KB, 'extensions:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,gif,mp4,mov,m4v,webm,ogg,mp3,wav'],

            // لينكات
            'links' => ['nullable', 'array'],
            'links.*.title' => ['required', 'string', 'max:255'],
            'links.*.url' => ['required', 'url', 'max:2048'],

            // يوتيوب
            'youtubes' => ['nullable', 'array'],
            'youtubes.*.title' => ['required', 'string', 'max:255'],
            'youtubes.*.url' => ['required', 'url', 'max:2048'],
            'vocabularyGameSetIds' => ['array'],
            'vocabularyGameSetIds.*' => ['integer'],
            'vocabularyAllowedGames' => ['array'],
            'vocabularyAllowedGames.*' => ['in:hangman,missing_letter,spelling_choice'],
            'vocabularyDifficultyPolicy' => ['required', 'in:student_choice,sprout,climber,champion'],

        ]; // قواعد Laravel للـ integer/min/lte. :contentReference[oaicite:2]{index=2}

        return $rules;
    }

    // new
    public function updatedFiles(): void
    {
        // كل مرة تختاري ملفات من الـ input، نضيفها لـ finalFiles
        $this->appendUniqueFinalUploads($this->files);

        // نفرغ الـ buffer عشان تقدري تعملي اختيار جديد من غير لخبطه
        $this->files = [];
        $this->rebuildAttachmentState();
    }

    // حذف ملف من اللستة قبل الحفظ
    public function removeFile(int $index): void
    {
        unset($this->finalFiles[$index]);
        $this->finalFiles = array_values($this->finalFiles);
        $this->rebuildAttachmentState();
    }

    public function removePendingFile(string $uploadKey): void
    {
        $this->finalFiles = array_values(array_filter(
            $this->finalFiles,
            fn ($upload, int|string $index): bool => $this->temporaryUploadKey($upload) !== $uploadKey
                && $this->pendingFileKey($upload, $index) !== $uploadKey,
            ARRAY_FILTER_USE_BOTH
        ));

        $this->files = array_values(array_filter(
            $this->files,
            fn ($upload, int|string $index): bool => $this->temporaryUploadKey($upload) !== $uploadKey
                && $this->pendingFileKey($upload, $index) !== $uploadKey,
            ARRAY_FILTER_USE_BOTH
        ));

        $this->rebuildAttachmentState();
    }

    public function reorderPendingFiles(array $orderedKeys): void
    {
        if ($this->locked || $orderedKeys === []) {
            return;
        }

        $order = array_flip(array_map('strval', $orderedKeys));

        usort(
            $this->finalFiles,
            fn ($left, $right): int => $this->pendingFileOrderIndex($left, $order)
                <=> $this->pendingFileOrderIndex($right, $order)
        );

        $this->finalFiles = array_values($this->finalFiles);
        $this->attachmentDraftOrder = $this->currentDraftKeys();
        $this->rebuildAttachmentState();
    }

    public function pendingFileKey(mixed $upload, int|string|null $index = null): string
    {
        $uploadKey = $this->temporaryUploadKey($upload);

        if ($uploadKey !== null) {
            return $uploadKey;
        }

        if (is_object($upload) && method_exists($upload, 'getClientOriginalName')) {
            return (string) $upload->getClientOriginalName();
        }

        return 'pending-file-'.(string) ($index ?? 0);
    }

    private function pendingFileOrderIndex(mixed $upload, array $order): int
    {
        $candidateKeys = [$this->pendingFileKey($upload)];

        if (is_object($upload) && method_exists($upload, 'getClientOriginalName')) {
            $candidateKeys[] = (string) $upload->getClientOriginalName();
        }

        $uploadKey = $this->temporaryUploadKey($upload);
        if ($uploadKey !== null) {
            $candidateKeys[] = $uploadKey;
        }

        foreach ($candidateKeys as $key) {
            if (isset($order[(string) $key])) {
                return (int) $order[(string) $key];
            }
        }

        return PHP_INT_MAX;
    }

    public function addLink(): void
    {

        // نمسح الأخطاء القديمة الخاصة باللينك
        $this->resetErrorBag(['link_title_input', 'link_url_input']);

        // Validation للحقول اللي مربوطة بالـ form
        $this->validate([
            'link_title_input' => ['required', 'string', 'max:255'],
            'link_url_input' => ['required', 'url', 'max:2048'],
        ]);

        $url = trim($this->link_url_input);

        $this->links[] = [
            'key' => $this->newDraftItemKey('link'),
            'title' => trim($this->link_title_input) ?: $url,
            'url' => $url,
        ];

        // نفرّغ المدخلات بعد الإضافة
        $this->link_title_input = '';
        $this->link_url_input = '';
        $this->rebuildAttachmentState();
    }

    public function addYoutube(): void
    {

        $this->resetErrorBag(['youtube_title_input', 'youtube_url_input']);

        $this->validate([
            'youtube_title_input' => ['required', 'string', 'max:255'],
            'youtube_url_input' => ['required', 'url', 'max:2048'],
        ]);

        $url = trim($this->youtube_url_input);

        $this->youtubes[] = [
            'key' => $this->newDraftItemKey('youtube'),
            'title' => trim($this->youtube_title_input) ?: $url,
            'url' => $url,
        ];

        $this->youtube_title_input = '';
        $this->youtube_url_input = '';
        $this->rebuildAttachmentState();
    }

    public function removeLink(int $index): void
    {
        unset($this->links[$index]);
        $this->links = array_values($this->links);
        $this->rebuildAttachmentState();
    }

    public function removeYoutube(int $index): void
    {
        unset($this->youtubes[$index]);
        $this->youtubes = array_values($this->youtubes);
        $this->rebuildAttachmentState();
    }

    public function openLibraryPicker(): void
    {
        if ($this->locked) {
            return;
        }

        $session = $this->resolveOwnedSessionOrFail($this->sessionId);
        $this->sessionSubjectId = (int) $session->subject_id;

        $this->dispatch(
            'open-library-picker',
            subjectId: $this->sessionSubjectId,
            selectedResourceIds: $this->selectedLibraryResourceIds
        );
    }

    #[On('library-resources-selected')]
    public function useLibraryResources(array $resourceIds): void
    {
        $this->selectedLibraryResourceIds = collect($resourceIds)
            ->map(fn ($id): string => trim((string) $id))
            ->filter(fn (string $id): bool => $id !== '')
            ->unique()
            ->values()
            ->all();

        $this->refreshSelectedLibraryResources();
        $this->resetErrorBag('content');
    }

    public function removeLibraryResource(string $resourceId): void
    {
        $resourceId = trim($resourceId);
        $this->selectedLibraryResourceIds = array_values(array_filter(
            $this->selectedLibraryResourceIds,
            fn ($selectedId): bool => (string) $selectedId !== $resourceId
        ));

        $this->refreshSelectedLibraryResources();
    }

    public function reorderSelectedLibraryResources(array $orderedIds): void
    {
        if ($this->locked) {
            return;
        }

        $orderedIds = collect($orderedIds)
            ->map(fn ($id): string => trim((string) $id))
            ->filter(fn (string $id): bool => $id !== '')
            ->unique()
            ->values()
            ->all();

        if ($orderedIds === []) {
            return;
        }

        $selectedById = [];
        foreach ($this->selectedLibraryResourceIds as $selectedId) {
            $selectedById[(string) $selectedId] = (string) $selectedId;
        }

        $reordered = [];
        foreach ($orderedIds as $orderedId) {
            if (isset($selectedById[$orderedId])) {
                $reordered[] = $selectedById[$orderedId];
            }
        }

        foreach ($this->selectedLibraryResourceIds as $selectedId) {
            if (! in_array((string) $selectedId, $orderedIds, true)) {
                $reordered[] = (string) $selectedId;
            }
        }

        $this->selectedLibraryResourceIds = array_values(array_unique($reordered));
        $this->refreshSelectedLibraryResources();
    }

    public function reorderAttachmentDraftItems(array $orderedKeys): void
    {
        if ($this->locked || $orderedKeys === []) {
            return;
        }

        $validKeys = array_flip($this->currentDraftKeys());
        $orderedKeys = collect($orderedKeys)
            ->map(fn ($key): string => trim((string) $key))
            ->filter(fn (string $key): bool => isset($validKeys[$key]))
            ->unique()
            ->values()
            ->all();

        foreach ($this->currentDraftKeys() as $key) {
            if (! in_array($key, $orderedKeys, true)) {
                $orderedKeys[] = $key;
            }
        }

        $this->attachmentDraftOrder = $orderedKeys;
        $this->applyDraftOrderToSourceArrays();
        $this->rebuildAttachmentState();
    }

    public function removeDraftAttachmentItem(string $draftKey): void
    {
        if ($this->locked) {
            return;
        }

        [$kind, $rawKey] = $this->splitDraftKey($draftKey);

        if ($kind === 'existing') {
            $this->markAttachmentForDeletion((int) $rawKey);

            return;
        }

        if ($kind === 'pending_file') {
            $this->removePendingFile($rawKey);

            return;
        }

        if ($kind === 'library') {
            $this->removeLibraryResource($rawKey);

            return;
        }

        if ($kind === 'link') {
            $index = $this->findLinkIndexByKey($rawKey);
            if ($index !== null) {
                $this->removeLink($index);
            }

            return;
        }

        if ($kind === 'youtube') {
            $index = $this->findYoutubeIndexByKey($rawKey);
            if ($index !== null) {
                $this->removeYoutube($index);
            }
        }
    }

    // end new

    #[On('open-session-task-edit-modal')]
    public function openEdit(int $taskId): void
    {
        $this->resetValidation();

        // نجيب التاسك بالمرفقات
        $task = SessionTask::with('attachments')
            ->whereKey($taskId)
            ->whereHas('classSession', fn ($q) => $q
                ->normal()
                ->whereHas('teacherSubjectClass', fn ($qq) => $qq->where('user_teacher_coteacher_id', Auth::id())))
            ->firstOrFail();

        // 1) تعبئة بيانات التاسك الأساسية
        $this->taskId = $task->id;
        $this->sessionId = (int) $task->class_session_id;
        $this->sessionSubjectId = (int) ($task->classSession?->subject_id ?? 0);
        $this->task_type_id = (int) $task->task_type_id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->default_points = (int) ($task->default_points ?? 0);
        $this->max_points = (int) ($task->max_points ?? 0);

        // 2) تفريغ كل الـ arrays الخاصة بالمرفقات + مدخلات الفورم
        $this->files = [];
        $this->finalFiles = [];
        $this->links = [];
        $this->youtubes = [];
        $this->existingAttachments = [];
        $this->existingFiles = [];
        $this->existingLinks = [];
        $this->existingYoutubes = [];
        $this->attachmentsToDelete = [];
        $this->selectedLibraryResourceIds = [];
        $this->selectedLibraryResources = [];
        $this->attachmentDraftOrder = [];
        $this->vocabularyGameSetIds = [];
        $this->vocabularyAllowedGames = ['hangman', 'missing_letter', 'spelling_choice'];
        $this->vocabularyDifficultyPolicy = VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE;

        $this->link_title_input = '';
        $this->link_url_input = '';
        $this->youtube_title_input = '';
        $this->youtube_url_input = '';

        $this->showLinkForm = false;
        $this->showYoutubeForm = false;
        $this->uploadsInProgress = false;

        // 3) تحميل المرفقات الموجودة من الـ DB
        $this->existingAttachments = $task->attachments
            ->map(function ($att) use ($task): array {
                $displayType = $this->isVocabularyGameUrl((string) $att->path) ? 'vocabulary_game' : $att->type;

                return [
                    'id' => $att->id,
                    'type' => $displayType,
                    'title' => $att->title,
                    'url' => $att->type === 'file'
                        ? route('teacher.sessions.attachment.show', [
                            'session' => $task->class_session_id,
                            'attachment' => $att->id,
                        ])
                        : $att->path,
                    'session_id' => (int) $task->class_session_id,
                    'task_id' => (int) $task->id,
                    'size' => $att->file_size,
                ];
            })
            ->values()
            ->all();

        $this->rebuildAttachmentState();

        // 4) حالة المودال
        // لو عندك status للتاسك:
        $this->locked = ($task->status === 'published');
        // $this->locked = false;
        // ✅ هنا ياخد اللي في DB
        $this->task_type_id = $task->task_type_id;
        $this->isEdit = true;
        $this->show = true;
    }

    // public function deleteAttachment(int $attachmentId): void
    // {
    //     // نجيب الـ attachment من الـ DB
    //     $att = AttachmentFile::find($attachmentId);

    //     if (!$att) {
    //         return;
    //     }

    //     // لو نوعه ملف نحذف الملف من الـ storage
    //     if ($att->type === 'file' && $att->path) {
    //         try {
    //             if (Storage::disk('public')->exists($att->path)) {
    //                 Storage::disk('public')->delete($att->path);
    //             }
    //         } catch (\Throwable $e) {
    //             // ممكن تتجاهلي الخطأ أو تعملي log لو حابة
    //         }
    //     }

    //     // نحذف السجل من الـ DB
    //     $att->delete();

    //     // نحدِّث الـ arrays اللي بتظهر في الواجهة

    //     // existingFiles
    //     foreach ($this->existingFiles as $i => $file) {
    //         if (($file['id'] ?? null) === $attachmentId) {
    //             unset($this->existingFiles[$i]);
    //         }
    //     }
    //     $this->existingFiles = array_values($this->existingFiles);

    //     // existingLinks
    //     foreach ($this->existingLinks as $i => $link) {
    //         if (($link['id'] ?? null) === $attachmentId) {
    //             unset($this->existingLinks[$i]);
    //         }
    //     }
    //     $this->existingLinks = array_values($this->existingLinks);

    //     // existingYoutubes
    //     foreach ($this->existingYoutubes as $i => $yt) {
    //         if (($yt['id'] ?? null) === $attachmentId) {
    //             unset($this->existingYoutubes[$i]);
    //         }
    //     }
    //     $this->existingYoutubes = array_values($this->existingYoutubes);
    // }

    public function markAttachmentForDeletion(int $attachmentId): void
    {
        // لو الـ ID مش موجود قبل كده، نضيفه لقائمة الحذف
        if (! in_array($attachmentId, $this->attachmentsToDelete, true)) {
            $this->attachmentsToDelete[] = $attachmentId;
        }
        $this->rebuildAttachmentState();
    }

    public function openAttachmentStudyViewer(int $sessionId, int $taskId, int $attachmentId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);

        abort_unless((int) $task->class_session_id === $sessionId, 404);
        abort_unless(
            $task->attachments->contains(fn (AttachmentFile $attachment): bool => (int) $attachment->id === $attachmentId),
            404
        );

        $this->dispatch(
            'open-teacher-attachment-study-viewer',
            sessionId: $sessionId,
            taskId: $task->id,
            attachmentId: $attachmentId
        );
    }

    #[On('reorder-session-task-attachments')]
    public function reorderExistingAttachments(int $taskId, array $orderedIds): void
    {
        if ($this->locked || ! Schema::hasColumn((new AttachmentFile)->getTable(), 'sort_order')) {
            return;
        }

        $task = $this->resolveOwnedTaskOrFail($taskId);
        $orderedIds = array_values(array_unique(array_map('intval', $orderedIds)));

        if ($orderedIds === []) {
            return;
        }

        $validIds = AttachmentFile::query()
            ->where('session_task_id', $task->id)
            ->whereIn('id', $orderedIds)
            ->pluck('id')
            ->map(fn ($value) => (int) $value)
            ->all();

        $orderedIds = array_values(array_intersect($orderedIds, $validIds));

        if ($orderedIds === []) {
            return;
        }

        DB::transaction(function () use ($task, $orderedIds): void {
            foreach ($orderedIds as $index => $attachmentId) {
                AttachmentFile::query()
                    ->whereKey($attachmentId)
                    ->where('session_task_id', $task->id)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        $byId = [];
        foreach ($this->existingAttachments as $attachment) {
            $byId[(int) ($attachment['id'] ?? 0)] = $attachment;
        }

        $reordered = [];
        foreach ($orderedIds as $attachmentId) {
            if (isset($byId[$attachmentId])) {
                $reordered[] = $byId[$attachmentId];
            }
        }

        foreach ($this->existingAttachments as $attachment) {
            if (! in_array((int) ($attachment['id'] ?? 0), $orderedIds, true)) {
                $reordered[] = $attachment;
            }
        }

        $this->existingAttachments = $reordered;
        $this->rebuildAttachmentState();
        $this->dispatch('session-task-updated', sessionId: $task->class_session_id);
    }

    private function rebuildAttachmentState(): void
    {
        $activeAttachments = array_values(array_filter(
            $this->existingAttachments,
            fn (array $attachment): bool => ! in_array($attachment['id'] ?? null, $this->attachmentsToDelete, true)
        ));

        $this->existingFiles = array_values(array_map(
            fn (array $attachment): array => [
                'id' => $attachment['id'],
                'title' => $attachment['title'],
                'url' => $attachment['url'],
                'size' => $attachment['size'] ?? null,
            ],
            array_filter($activeAttachments, fn (array $attachment): bool => ($attachment['type'] ?? null) === 'file')
        ));

        $this->existingLinks = array_values(array_map(
            fn (array $attachment): array => [
                'id' => $attachment['id'],
                'title' => $attachment['title'],
                'url' => $attachment['url'],
            ],
            array_filter($activeAttachments, fn (array $attachment): bool => ($attachment['type'] ?? null) === 'link')
        ));

        $this->existingYoutubes = array_values(array_map(
            fn (array $attachment): array => [
                'id' => $attachment['id'],
                'title' => $attachment['title'],
                'url' => $attachment['url'],
            ],
            array_filter($activeAttachments, fn (array $attachment): bool => ($attachment['type'] ?? null) === 'youtube')
        ));

        $this->syncAttachmentDraftOrder();
    }

    #[Computed]
    public function attachmentDraftItems(): array
    {
        $this->syncAttachmentDraftOrder();

        $itemsByKey = [];

        foreach ($this->existingAttachments as $attachment) {
            if (in_array($attachment['id'] ?? null, $this->attachmentsToDelete, true)) {
                continue;
            }

            $type = (string) ($attachment['type'] ?? 'file');
            $key = 'existing:'.(string) ($attachment['id'] ?? '');
            $itemsByKey[$key] = [
                'key' => $key,
                'kind' => 'existing',
                'type' => $type,
                'title' => (string) ($attachment['title'] ?? 'Attachment'),
                'url' => (string) ($attachment['url'] ?? ''),
                'attachmentId' => (int) ($attachment['id'] ?? 0),
                'sessionId' => (int) ($attachment['session_id'] ?? $this->sessionId),
                'taskId' => (int) ($attachment['task_id'] ?? $this->taskId),
                'meta' => $this->draftItemMeta('existing', $type),
                'size' => $attachment['size'] ?? null,
            ];
        }

        foreach ($this->finalFiles as $index => $upload) {
            $rawKey = $this->pendingFileKey($upload, $index);
            $key = 'pending_file:'.$rawKey;
            $itemsByKey[$key] = [
                'key' => $key,
                'kind' => 'pending_file',
                'type' => 'file',
                'title' => is_object($upload) && method_exists($upload, 'getClientOriginalName')
                    ? (string) $upload->getClientOriginalName()
                    : 'New file',
                'url' => '',
                'meta' => 'New file',
                'size' => is_object($upload) && method_exists($upload, 'getSize') ? $upload->getSize() : null,
            ];
        }

        foreach ($this->selectedLibraryResources as $resource) {
            $type = (string) ($resource['type'] ?? 'link');
            $key = 'library:'.(string) ($resource['id'] ?? '');
            $isVocabularyGame = $type === 'vocabulary_game';
            $itemsByKey[$key] = [
                'key' => $key,
                'kind' => 'library',
                'type' => $type,
                'title' => (string) ($resource['title'] ?? 'Library source'),
                'url' => '',
                'meta' => $isVocabularyGame ? 'Vocab Games link' : 'Library source',
                'detail' => (string) ($resource['detail'] ?? ''),
                'size' => null,
            ];
        }

        foreach ($this->links as $index => $link) {
            $rawKey = $this->linkKey($link, $index);
            $key = 'link:'.$rawKey;
            $itemsByKey[$key] = [
                'key' => $key,
                'kind' => 'link',
                'type' => 'link',
                'title' => (string) ($link['title'] ?? 'Link'),
                'url' => (string) ($link['url'] ?? ''),
                'meta' => 'New link',
                'size' => null,
            ];
        }

        foreach ($this->youtubes as $index => $youtube) {
            $rawKey = $this->youtubeKey($youtube, $index);
            $key = 'youtube:'.$rawKey;
            $itemsByKey[$key] = [
                'key' => $key,
                'kind' => 'youtube',
                'type' => 'youtube',
                'title' => (string) ($youtube['title'] ?? 'YouTube'),
                'url' => (string) ($youtube['url'] ?? ''),
                'meta' => 'New YouTube link',
                'size' => null,
            ];
        }

        $items = [];
        foreach ($this->attachmentDraftOrder as $key) {
            if (isset($itemsByKey[$key])) {
                $items[] = $this->decorateDraftItem($itemsByKey[$key]);
            }
        }

        return $items;
    }

    private function syncAttachmentDraftOrder(): void
    {
        $validKeys = $this->currentDraftKeys();
        $validLookup = array_flip($validKeys);

        $ordered = collect($this->attachmentDraftOrder)
            ->map(fn ($key): string => (string) $key)
            ->filter(fn (string $key): bool => isset($validLookup[$key]))
            ->unique()
            ->values()
            ->all();

        foreach ($validKeys as $key) {
            if (! in_array($key, $ordered, true)) {
                $ordered[] = $key;
            }
        }

        $this->attachmentDraftOrder = $ordered;
    }

    private function currentDraftKeys(): array
    {
        $keys = [];

        foreach ($this->existingAttachments as $attachment) {
            if (! in_array($attachment['id'] ?? null, $this->attachmentsToDelete, true)) {
                $keys[] = 'existing:'.(string) ($attachment['id'] ?? '');
            }
        }

        foreach ($this->finalFiles as $index => $upload) {
            $keys[] = 'pending_file:'.$this->pendingFileKey($upload, $index);
        }

        foreach ($this->selectedLibraryResourceIds as $resourceId) {
            $keys[] = 'library:'.(string) $resourceId;
        }

        foreach ($this->links as $index => $link) {
            $keys[] = 'link:'.$this->linkKey($link, $index);
        }

        foreach ($this->youtubes as $index => $youtube) {
            $keys[] = 'youtube:'.$this->youtubeKey($youtube, $index);
        }

        return array_values(array_unique(array_filter($keys, fn (string $key): bool => ! str_ends_with($key, ':'))));
    }

    private function applyDraftOrderToSourceArrays(): void
    {
        $order = array_flip($this->attachmentDraftOrder);

        usort(
            $this->finalFiles,
            fn ($left, $right): int => ($order['pending_file:'.$this->pendingFileKey($left)] ?? PHP_INT_MAX)
                <=> ($order['pending_file:'.$this->pendingFileKey($right)] ?? PHP_INT_MAX)
        );
        $this->finalFiles = array_values($this->finalFiles);

        usort(
            $this->links,
            fn (array $left, array $right): int => ($order['link:'.$this->linkKey($left)] ?? PHP_INT_MAX)
                <=> ($order['link:'.$this->linkKey($right)] ?? PHP_INT_MAX)
        );
        $this->links = array_values($this->links);

        usort(
            $this->youtubes,
            fn (array $left, array $right): int => ($order['youtube:'.$this->youtubeKey($left)] ?? PHP_INT_MAX)
                <=> ($order['youtube:'.$this->youtubeKey($right)] ?? PHP_INT_MAX)
        );
        $this->youtubes = array_values($this->youtubes);

        $selectedById = [];
        foreach ($this->selectedLibraryResourceIds as $resourceId) {
            $selectedById[(string) $resourceId] = (string) $resourceId;
        }

        uksort(
            $selectedById,
            fn (string $left, string $right): int => ($order['library:'.$left] ?? PHP_INT_MAX)
                <=> ($order['library:'.$right] ?? PHP_INT_MAX)
        );
        $this->selectedLibraryResourceIds = array_values($selectedById);
        $this->refreshSelectedLibraryResources();
    }

    private function splitDraftKey(string $draftKey): array
    {
        $parts = explode(':', $draftKey, 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function findPendingFileByKey(string $rawKey): mixed
    {
        foreach ($this->finalFiles as $index => $upload) {
            if ($this->pendingFileKey($upload, $index) === $rawKey) {
                return $upload;
            }
        }

        return null;
    }

    private function findLinkByKey(string $rawKey): ?array
    {
        $index = $this->findLinkIndexByKey($rawKey);

        return $index === null ? null : $this->links[$index];
    }

    private function findYoutubeByKey(string $rawKey): ?array
    {
        $index = $this->findYoutubeIndexByKey($rawKey);

        return $index === null ? null : $this->youtubes[$index];
    }

    private function findLinkIndexByKey(string $rawKey): ?int
    {
        foreach ($this->links as $index => $link) {
            if ($this->linkKey($link, $index) === $rawKey) {
                return $index;
            }
        }

        return null;
    }

    private function findYoutubeIndexByKey(string $rawKey): ?int
    {
        foreach ($this->youtubes as $index => $youtube) {
            if ($this->youtubeKey($youtube, $index) === $rawKey) {
                return $index;
            }
        }

        return null;
    }

    private function linkKey(array $link, ?int $index = null): string
    {
        return (string) ($link['key'] ?? 'link-'.$index);
    }

    private function youtubeKey(array $youtube, ?int $index = null): string
    {
        return (string) ($youtube['key'] ?? 'youtube-'.$index);
    }

    private function newDraftItemKey(string $prefix): string
    {
        return $prefix.'-'.str_replace('.', '', uniqid('', true));
    }

    private function draftItemMeta(string $kind, string $type): string
    {
        if ($kind === 'existing') {
            return match ($type) {
                'youtube' => 'Saved YouTube link',
                'link' => 'Saved link',
                default => 'Saved file',
            };
        }

        return match ($type) {
            'youtube' => 'YouTube',
            'link' => 'Link',
            default => 'File',
        };
    }

    private function decorateDraftItem(array $item): array
    {
        $type = (string) ($item['type'] ?? 'file');

        $item['icon'] = match ($type) {
            'vocabulary_game' => 'tabler-balloon',
            'youtube' => 'tabler-brand-youtube',
            'link' => 'tabler-link',
            default => 'tabler-file-description',
        };
        $item['iconClass'] = match ($type) {
            'vocabulary_game' => 'w14-attachment-icon--link',
            'youtube' => 'w14-attachment-icon--youtube',
            'link' => 'w14-attachment-icon--link',
            default => (($item['kind'] ?? '') === 'pending_file' ? 'w14-attachment-icon--pending' : 'w14-attachment-icon--file'),
        };

        return $item;
    }

    private function isVocabularyGameUrl(string $path): bool
    {
        return str_contains($path, '/vocabulary/games/assignment/')
            || str_contains($path, 'vocabulary/games/assignment/');
    }

    private function refreshSelectedLibraryResources(): void
    {
        $subjectId = (int) ($this->sessionSubjectId ?? 0);

        if ($this->selectedLibraryResourceIds === [] || $subjectId <= 0) {
            $this->selectedLibraryResourceIds = [];
            $this->selectedLibraryResources = [];
            $this->syncAttachmentDraftOrder();

            return;
        }

        $requestedIds = $this->selectedLibraryResourceIds;
        $libraryResourceIds = collect($requestedIds)
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $legacyResourceIds = collect($requestedIds)
            ->filter(fn ($id): bool => is_string($id) && str_starts_with($id, 'series__'))
            ->unique()
            ->values()
            ->all();
        $generalResourceIds = collect($requestedIds)
            ->filter(fn ($id): bool => is_string($id) && str_starts_with($id, GeneralLibraryAttachmentAdapter::GENERAL_PREFIX))
            ->map(fn (string $id): int => (int) substr($id, strlen(GeneralLibraryAttachmentAdapter::GENERAL_PREFIX)))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $activeSectionIds = app(LibraryResourceQuery::class)->activeSectionIdsForOwner((int) Auth::id(), $subjectId);

        if ($activeSectionIds === [] && $legacyResourceIds === [] && $generalResourceIds === []) {
            $this->selectedLibraryResourceIds = [];
            $this->selectedLibraryResources = [];
            $this->syncAttachmentDraftOrder();

            return;
        }

        $libraryResources = $libraryResourceIds === [] || $activeSectionIds === []
            ? collect()
            : LibraryResource::query()
                ->whereIn('id', $libraryResourceIds)
                ->where('owner_user_id', Auth::id())
                ->where('subject_id', $subjectId)
                ->whereIn('library_section_id', $activeSectionIds)
                ->where('status', LibraryResource::STATUS_ACTIVE)
                ->get();

        $selectedResources = $libraryResources
            ->map(fn (LibraryResource $resource): array => [
                'id' => (string) $resource->id,
                'title' => (string) $resource->title,
                'type' => (string) $resource->resource_type,
                'detail' => $resource->isFile()
                    ? (string) ($resource->original_filename ?: basename((string) $resource->file_path))
                    : (string) parse_url((string) $resource->external_url, PHP_URL_HOST),
            ])
            ->all();

        $generalResources = Schema::hasTable('general_library_resources')
            ? GeneralLibraryResource::query()
            ->whereIn('id', $generalResourceIds)
            ->where('status', GeneralLibraryResource::STATUS_ACTIVE)
            ->get()
            ->map(fn (GeneralLibraryResource $resource): array => [
                'id' => GeneralLibraryAttachmentAdapter::GENERAL_PREFIX.$resource->id,
                'title' => (string) $resource->title,
                'type' => (string) $resource->resource_type,
                'detail' => (string) ($resource->folder?->title ?? 'Shared Library'),
            ])
            ->all()
            : [];

        foreach ($generalResources as $resource) {
            $selectedResources[] = $resource;
        }

        $legacyResources = app(LegacyLibraryTaskResourceCatalog::class)
            ->findManyForSubject(Auth::user(), $subjectId, $legacyResourceIds);

        foreach ($legacyResources as $resource) {
            $isVocabularyGame = ($resource['source_type'] ?? '') === SeriesLibrarySourceResolver::SOURCE_VOCABULARY_LIST;
            $selectedResources[] = [
                'id' => $resource['id'],
                'title' => $resource['title'],
                'type' => $isVocabularyGame ? 'vocabulary_game' : 'link',
                'detail' => $isVocabularyGame
                    ? (string) ($resource['description'] ?: 'Vocab Games lesson')
                    : 'Legacy Library source',
            ];
        }

        $order = array_flip(array_map('strval', $requestedIds));
        usort(
            $selectedResources,
            fn (array $left, array $right): int => ($order[(string) $left['id']] ?? PHP_INT_MAX) <=> ($order[(string) $right['id']] ?? PHP_INT_MAX)
        );

        $this->selectedLibraryResourceIds = collect($selectedResources)
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();
        $this->selectedLibraryResources = $selectedResources;
        $this->syncAttachmentDraftOrder();
    }

    private function hasTaskContent(): bool
    {
        if (trim((string) $this->description) !== '') {
            return true;
        }

        return ! empty($this->finalFiles)
            || ! empty($this->links)
            || ! empty($this->youtubes)
            || ! empty($this->existingFiles)
            || ! empty($this->existingLinks)
            || ! empty($this->existingYoutubes)
            || collect($this->existingAttachments)->contains(fn (array $attachment): bool => ($attachment['type'] ?? '') === 'vocabulary_game')
            || ! empty($this->selectedLibraryResourceIds)
            || $this->selectedVocabularyGameSetIds() !== [];
    }

    private function appendUniqueFinalUploads(array $uploads): void
    {
        $existingKeys = [];

        foreach ($this->finalFiles as $existingUpload) {
            $existingKey = $this->temporaryUploadKey($existingUpload);

            if ($existingKey !== null) {
                $existingKeys[$existingKey] = true;
            }
        }

        foreach ($uploads as $upload) {
            $uploadKey = $this->temporaryUploadKey($upload);

            if ($uploadKey !== null && isset($existingKeys[$uploadKey])) {
                continue;
            }

            if ($uploadKey !== null) {
                $existingKeys[$uploadKey] = true;
            }

            $this->finalFiles[] = $upload;
        }

        $this->finalFiles = array_values($this->finalFiles);
    }

    private function temporaryUploadKey(mixed $upload): ?string
    {
        if (is_object($upload) && method_exists($upload, 'getFilename')) {
            return (string) $upload->getFilename();
        }

        return null;
    }

    private function deletePublicPathQuietly(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        try {
            app(LibraryFileRetentionService::class)->deleteIfUnreferenced($path);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    #[Computed]
    public function vocabularyGameOptions(): array
    {
        if (! Schema::hasTable('vocabulary_sets') || ! Schema::hasTable('vocabulary_game_assignments')) {
            return [];
        }

        app(VocabularySourceRegistry::class)->ensureLegacySourceProxies();

        return VocabularySet::query()
            ->visibleToTeachers(Auth::id())
            ->playable()
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->with('parent.parent.parent')
            ->withCount('memberships')
            ->orderBy('parent_id')
            ->orderBy('title')
            ->limit(120)
            ->get()
            ->map(fn (VocabularySet $set): array => [
                'id' => (int) $set->id,
                'title' => $this->vocabularySetBreadcrumb($set),
                'source_kind' => (string) $set->source_kind,
                'words' => (int) $set->memberships_count,
            ])
            ->all();
    }

    private function vocabularySetBreadcrumb(VocabularySet $set): string
    {
        $parts = [];
        $current = $set;

        while ($current) {
            array_unshift($parts, (string) $current->title);
            $current = $current->parent;
        }

        return implode(' / ', array_filter($parts));
    }

    public function render()
    {
        return view('livewire.teacher.show-session-task', [
            'taskTypes' => $this->taskTypes,
            'taskFileAcceptAttribute' => LibraryResourceValidator::acceptAttribute(),
            'taskFileAllowedExtensions' => LibraryResourceValidator::allowedExtensions(),
            'taskFileMaxBytes' => self::MAX_ATTACHMENT_FILE_KB * 1024,
        ]);
    }
}
