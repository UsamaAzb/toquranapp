<?php

namespace App\Livewire\Teacher;

use App\Helpers\Helpers;
use App\Models\DailyAttachmentFile;
use App\Models\DailySession;
use App\Models\DailySessionTask;
use App\Models\TaskType;
use App\Models\TeacherSubjectClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ShowDailySessionTask extends Component
{
    use WithFileUploads; // لرفع الملفات

    private const MAX_ATTACHMENT_FILE_KB = 51200;

    public int $defaultTaskTypeId = 7;

    public bool $show = false;

    public ?int $dailySessionId = null;

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

    // إظهار/إخفاء فورم اليوتيوب
    public bool $showYoutubeForm = false;

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

    protected function resolveOwnedDailySessionOrFail(?int $dailySessionId = null): DailySession
    {
        return DailySession::query()
            ->whereKey($dailySessionId ?? $this->dailySessionId)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from((new TeacherSubjectClass)->getTable())
                    ->whereColumn('teacher_subject_classes.subject_id', 'daily_sessions.subject_id')
                    ->where('teacher_subject_classes.user_teacher_coteacher_id', Auth::id());
            })
            ->firstOrFail();
    }

    protected function resolveOwnedDailyTaskOrFail(?int $taskId = null): DailySessionTask
    {
        return DailySessionTask::query()
            ->whereKey($taskId ?? $this->taskId)
            ->whereHas('dailySession', function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw('1')
                        ->from((new TeacherSubjectClass)->getTable())
                        ->whereColumn('teacher_subject_classes.subject_id', 'daily_sessions.subject_id')
                        ->where('teacher_subject_classes.user_teacher_coteacher_id', Auth::id());
                });
            })
            ->firstOrFail();
    }

    private function dailyTaskSupportsTaskType(): bool
    {
        return Schema::hasColumn((new DailySessionTask)->getTable(), 'task_type_id');
    }

    private function dailyTaskPayload(): array
    {
        $payload = [
            'title' => $this->title,
            'description' => $this->description,
            'default_points' => (int) ($this->default_points ?? 0),
            'max_points' => (int) ($this->max_points ?? 0),
        ];

        if ($this->dailyTaskSupportsTaskType()) {
            $payload['task_type_id'] = (int) ($this->task_type_id ?? $this->defaultTaskTypeId);
        }

        return $payload;
    }

    #[On('open-daily-session-task-modal')]
    public function open(int $dailySessionId = 0): void
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

        $this->link_title_input = '';
        $this->link_url_input = '';
        $this->youtube_title_input = '';
        $this->youtube_url_input = '';

        $this->showLinkForm = false;
        $this->showYoutubeForm = false;
        $this->uploadsInProgress = false;

        $this->isEdit = false;
        $this->locked = false;

        $this->default_points = 5;
        $this->max_points = 10;

        $this->task_type_id = $this->defaultTaskTypeId;

        $this->dailySessionId = $this->resolveOwnedDailySessionOrFail($dailySessionId)->id;
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

        $t = $this->selectedType; // array|null

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

        if (($linkTitle !== '' && $linkUrl === '')) {
            $this->addError('links_pending', ' Please enter the "Link URL" then click "Add Link".');

            return; // مهم جداً: يمنع الحفظ ويمنع قفل البوب
        }
        if (($linkTitle === '' && $linkUrl !== '')) {
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

        $this->resetErrorBag(['content', 'files']);

        if ($this->uploadsInProgress) {
            $this->addError('files', 'Wait for uploads to finish before saving.');

            return;
        }
        $this->validate($this->rules()); // قواعد تحت

        if (! $this->hasTaskContent()) {
            $this->addError('content', 'Add a description or at least one attachment.');

            return;
        }

        $dailySession = $this->resolveOwnedDailySessionOrFail($this->dailySessionId);
        $storedPaths = [];

        try {
            DB::transaction(function () use ($dailySession, &$storedPaths): void {
                $maxSort = DailySessionTask::where('daily_session_id', $this->dailySessionId)->max('sort') ?? 0;

                $task = DailySessionTask::create($this->dailyTaskPayload() + [
                    'daily_session_id' => $dailySession->id,
                    'sort' => $maxSort + 1,
                ]);

                $this->createAttachmentsForTask($task, $dailySession, $storedPaths);
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
            'link_title_input', 'link_url_input',
            'youtube_title_input', 'youtube_url_input',
        ]);

        $this->show = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Task saved.']);
        $this->dispatch('daily-session-task-added', dailySessionId: $this->dailySessionId)
            ->to(DailySessionsBoard::class);

        $this->dispatch('daily-session-task-saved', dailySessionId: $this->dailySessionId);

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

        if (! $this->hasTaskContent()) {
            $this->addError('content', 'Add a description or at least one attachment.');

            return;
        }

        // 1) تعديل بيانات التاسك — scoped to current daily session
        $task = $this->resolveOwnedDailyTaskOrFail($this->taskId);
        $dailySession = $this->resolveOwnedDailySessionOrFail($this->dailySessionId);
        $storedPaths = [];
        $pathsToDelete = [];

        try {
            DB::transaction(function () use ($task, $dailySession, &$storedPaths, &$pathsToDelete): void {
                $task->update($this->dailyTaskPayload());

                $this->createAttachmentsForTask($task, $dailySession, $storedPaths);

                if (! empty($this->attachmentsToDelete)) {
                    $attachments = DailyAttachmentFile::where('daily_session_task_id', $task->id)
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
        $this->show = false;
        $this->isEdit = false;

        $this->dispatch('daily-session-task-saved', dailySessionId: $this->dailySessionId)
            ->to(DailySessionsBoard::class);

    }

    private function createAttachmentsForTask(DailySessionTask $task, DailySession $dailySession, array &$storedPaths): void
    {
        foreach ($this->finalFiles as $upload) {
            $storedPath = Storage::disk('public')->putFile('attachments', $upload, 'public');
            if ($storedPath === false) {
                throw new \RuntimeException('Failed to store attachment file.');
            }

            $storedPaths[] = $storedPath;

            DailyAttachmentFile::create([
                'daily_session_task_id' => $task->id,
                'title' => $upload->getClientOriginalName(),
                'description' => $this->description,
                'type' => 'file',
                'path' => $storedPath,
                'file_size' => $upload->getSize(),
                'subject_id' => $dailySession->subject_id,
            ]);
        }

        foreach ($this->links as $link) {
            DailyAttachmentFile::create([
                'daily_session_task_id' => $task->id,
                'title' => $link['title'],
                'description' => $this->description,
                'type' => 'link',
                'path' => $link['url'],
                'file_size' => null,
                'subject_id' => $dailySession->subject_id,
            ]);
        }

        foreach ($this->youtubes as $yt) {
            DailyAttachmentFile::create([
                'daily_session_task_id' => $task->id,
                'title' => $yt['title'],
                'description' => $this->description,
                'type' => 'youtube',
                'path' => trim(Helpers::youtubeToEmbed($yt['url'])),
                'file_size' => null,
                'subject_id' => $dailySession->subject_id,
            ]);
        }
    }

    protected function rules(): array
    {
        $rules = [
            'dailySessionId' => ['required', 'integer', 'exists:daily_sessions,id'],
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
            fn ($upload): bool => $this->temporaryUploadKey($upload) !== $uploadKey
        ));

        $this->files = array_values(array_filter(
            $this->files,
            fn ($upload): bool => $this->temporaryUploadKey($upload) !== $uploadKey
        ));

        $this->rebuildAttachmentState();
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

    // end new

    #[On('open-daily-session-task-edit-modal')]
    public function openEdit(int $taskId): void
    {
        $this->resetValidation();

        // نجيب التاسك بالمرفقات
        $task = DailySessionTask::with('attachments')
            ->whereKey($taskId)
            ->whereHas('dailySession', function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw('1')
                        ->from((new TeacherSubjectClass)->getTable())
                        ->whereColumn('teacher_subject_classes.subject_id', 'daily_sessions.subject_id')
                        ->where('teacher_subject_classes.user_teacher_coteacher_id', Auth::id());
                });
            })
            ->firstOrFail();

        // 1) تعبئة بيانات التاسك الأساسية
        $this->taskId = $task->id;
        $this->dailySessionId = (int) $task->daily_session_id;
        $this->task_type_id = (int) ($task->task_type_id ?? $this->defaultTaskTypeId);
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

        $this->link_title_input = '';
        $this->link_url_input = '';
        $this->youtube_title_input = '';
        $this->youtube_url_input = '';

        $this->showLinkForm = false;
        $this->showYoutubeForm = false;
        $this->uploadsInProgress = false;

        // 3) تحميل المرفقات الموجودة من الـ DB
        $this->existingAttachments = $task->attachments
            ->map(function ($att): array {
                return [
                    'id' => $att->id,
                    'type' => $att->type,
                    'title' => $att->title,
                    'url' => $att->type === 'file'
                        ? Storage::disk('public')->url($att->path)
                        : $att->path,
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
        $this->task_type_id = (int) ($task->task_type_id ?? $this->defaultTaskTypeId);

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
            || ! empty($this->existingYoutubes);
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
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function render()
    {
        return view('livewire.teacher.show-daily-session-task', [
            'taskTypes' => $this->taskTypes,
        ]);
    }
}
