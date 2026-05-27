<?php

namespace App\Livewire\Teacher;

use App\Models\MainDailySessionMainTask;
use App\Models\MainDailySessionTemplate;
use App\Models\MainDailySessionVersion;
use App\Models\MainDailySessionVersionTask;
use App\Models\TeacherSubjectClass;
use App\Services\AutomatedTaskAssignmentService;
use App\Services\AutomatedTaskPublishValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class AutomatedTasksBoard extends Component
{
    public int $subjectId;

    public string $templateScope = 'working';

    public string $boardUrl = '';

    /** @var array{message: string, tone: string}|null */
    public ?array $boardFeedback = null;

    public bool $createTemplateOpen = false;

    public array $draftTemplate = [
        'title' => '',
        'recurrence_kind' => 'daily',
        'recurrence_interval' => 1,
        'recurrence_weekdays' => [],
        'recurrence_day_of_month' => null,
    ];

    /** @var array<int, array<string, mixed>> */
    public array $templateForms = [];

    /** @var array<int, int|null> */
    public array $activeVersionByTemplate = [];

    /** @var array<int, array<int, array<string, mixed>>> */
    public array $versionTaskForms = [];

    /** @var array<int, array<int, string>> */
    public array $publishErrors = [];

    /** @var array<int, true> */
    public array $expandedTemplates = [];

    /** @var array<int, true> */
    public array $settingsOpen = [];

    /** @var array<string, true> */
    public array $savedVersionTaskKeys = [];

    public function mount(int $subjectId): void
    {
        $this->subjectId = $this->ensureOwnedSubjectOrFail($subjectId);
        $this->boardUrl = request()->url();
        $this->templateScope = $this->normalizeTemplateScope(
            (string) request()->query('automated_scope', $this->templateScope)
        );
        $this->boardFeedback = Session::pull('automated_task_board_feedback');
        $this->syncBoardState();
    }

    #[On('automated-task-main-task-saved')]
    #[On('automated-task-assignment-saved')]
    public function refreshBoard(): void
    {
        $this->syncBoardState();
    }

    public function setTemplateScope(string $scope): void
    {
        $this->templateScope = $this->normalizeTemplateScope($scope);
        $this->syncBoardState();
    }

    public function createTemplate(): void
    {
        $payload = $this->validatedTemplatePayload($this->draftTemplate, true);

        $template = MainDailySessionTemplate::create($payload + [
            'subject_id' => $this->subjectId,
            'created_by_user_id' => Auth::id(),
            'status' => 'draft',
        ]);

        $version = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Version 1',
            'sort_order' => 1,
        ]);

        $this->draftTemplate = [
            'title' => '',
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'recurrence_weekdays' => [],
            'recurrence_day_of_month' => null,
        ];

        $this->activeVersionByTemplate[$template->id] = $version->id;
        $this->expandedTemplates[$template->id] = true;
        $this->createTemplateOpen = false;
        $this->syncBoardState();
    }

    public function toggleCreateTemplateForm(): void
    {
        $this->createTemplateOpen = ! $this->createTemplateOpen;
    }

    public function saveTemplate(int $templateId): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);

        if ($template->isArchived()) {
            $this->templateForms[$templateId] = $this->templateFormFromModel($template);
            $this->syncBoardState();

            return;
        }

        $form = $this->templateForms[$templateId] ?? [];
        $payload = $this->validatedTemplatePayload($form, false, $templateId);

        $template->update($payload);
        unset($this->settingsOpen[$templateId]);
        $this->syncBoardState();
    }

    public function addVersion(int $templateId): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);
        $nextSortOrder = ((int) $template->versions()->max('sort_order')) + 1;

        $version = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Version '.$nextSortOrder,
            'sort_order' => $nextSortOrder,
        ]);

        $this->activeVersionByTemplate[$template->id] = $version->id;
        $this->syncBoardState();
    }

    public function deleteVersion(int $versionId, AutomatedTaskAssignmentService $assignmentService): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId);
        $templateId = (int) $version->main_daily_session_template_id;

        $assignmentService->deleteVersion($version->id, Carbon::today(), (int) Auth::id());

        unset($this->activeVersionByTemplate[$templateId]);
        $this->publishErrors[$templateId] = [];
        $this->syncBoardState();
    }

    public function renameVersion(int $versionId, string $displayName): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId);
        $cleanName = trim($displayName);

        if ($cleanName === '') {
            return;
        }

        Validator::make([
            'display_name' => $cleanName,
        ], [
            'display_name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('main_daily_session_versions', 'display_name')
                    ->where('main_daily_session_template_id', $version->main_daily_session_template_id)
                    ->ignore($version->id),
            ],
        ])->validate();

        $version->update(['display_name' => $cleanName]);
        $this->syncBoardState();
    }

    public function setActiveVersion(int $templateId, int $versionId): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);

        if (! $template->versions()->whereKey($versionId)->exists()) {
            return;
        }

        $this->activeVersionByTemplate[$templateId] = $versionId;
    }

    public function openSettings(int $templateId): void
    {
        $this->settingsOpen[$templateId] = true;
    }

    public function closeSettings(int $templateId): void
    {
        unset($this->settingsOpen[$templateId]);
    }

    public function saveVersionTask(int $versionId, int $mainTaskId): void
    {
        $version = $this->resolveOwnedVersionOrFail($versionId)->loadMissing('template');
        $mainTask = $this->resolveOwnedMainTaskOrFail($mainTaskId);

        abort_unless(
            $version->main_daily_session_template_id === $mainTask->main_daily_session_template_id,
            404
        );

        $form = data_get($this->versionTaskForms, "{$versionId}.{$mainTaskId}", []);
        $enabled = (bool) data_get($form, 'enabled', false);

        if (! $enabled) {
            MainDailySessionVersionTask::query()
                ->where('version_id', $versionId)
                ->where('main_task_id', $mainTaskId)
                ->delete();

            $this->publishErrors[$version->main_daily_session_template_id] = [];
            $this->savedVersionTaskKeys[$versionId.':'.$mainTaskId] = true;
            $this->syncBoardState();

            return;
        }

        Validator::make([
            'description_override' => data_get($form, 'description_override'),
        ], [
            'description_override' => ['nullable', 'string'],
        ])->validate();

        MainDailySessionVersionTask::updateOrCreate(
            [
                'version_id' => $versionId,
                'main_task_id' => $mainTaskId,
            ],
            [
                'description_override' => $this->cleanNullableText(data_get($form, 'description_override')),
                'sort_order' => $mainTask->sort_order ?? 0,
            ]
        );

        $this->publishErrors[$version->main_daily_session_template_id] = [];
        $this->savedVersionTaskKeys[$versionId.':'.$mainTaskId] = true;
        $this->syncBoardState();
    }

    public function publishTemplate(int $templateId, AutomatedTaskPublishValidator $validator): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);
        $result = $validator->validate($template->fresh());

        $this->publishErrors[$templateId] = $result->errors;

        if ($result->passes) {
            $template->update(['status' => 'active']);
            $this->publishErrors[$templateId] = [];
        }

        $this->syncBoardState();
    }

    public function sendTemplateToDraft(int $templateId): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);
        $template->update(['status' => 'draft']);
        $this->publishErrors[$templateId] = [];
        $this->syncBoardState();
    }

    public function archiveTemplate(int $templateId): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);
        $template->update(['status' => 'archived']);
        $this->templateScope = 'archived';
        Session::flash('automated_task_board_feedback', [
            'message' => 'Template archived. You can restore it here any time.',
            'tone' => 'warning',
        ]);

        $this->redirect($this->scopeUrl('archived'), navigate: false);
    }

    public function restoreTemplate(int $templateId): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);
        $template->update(['status' => 'draft']);
        $this->publishErrors[$templateId] = [];
        Session::flash('automated_task_board_feedback', [
            'message' => 'Template restored to the working queue. Switch to Working queue to continue editing.',
            'tone' => 'success',
        ]);

        $this->redirect($this->scopeUrl('working'), navigate: false);
    }

    public function openHistoryPanel(int $templateId): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);

        $this->dispatch('open-automated-task-history-panel', templateId: $template->id)
            ->to(AutomatedTaskGeneratedHistoryPanel::class);
    }

    public function openAssignmentModal(int $templateId, ?int $versionId = null): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);

        if ($versionId !== null) {
            abort_unless($template->versions()->whereKey($versionId)->exists(), 404);
            $this->activeVersionByTemplate[$templateId] = $versionId;
        }

        $this->dispatch('open-automated-task-assignment-modal', templateId: $template->id, versionId: $versionId)
            ->to(AutomatedTaskAssignmentModal::class);
    }

    public function openMainTaskModal(int $templateId, ?int $mainTaskId = null): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);

        if ($mainTaskId !== null) {
            $mainTask = $this->resolveOwnedMainTaskOrFail($mainTaskId);

            abort_unless(
                (int) $mainTask->main_daily_session_template_id === $template->id,
                404
            );
        }

        $this->dispatch(
            'open-automated-task-main-task-modal',
            templateId: $template->id,
            mainTaskId: $mainTaskId
        )->to(AutomatedTaskMainTaskModal::class);
    }

    public function dismissBoardFeedback(): void
    {
        $this->boardFeedback = null;
    }

    public function render(): View
    {
        $templates = $this->renderTemplates();
        $this->ensureTemplateForms($templates);
        $versionAssignmentCounts = $this->aggregateVersionAssignmentCounts($templates);
        $versionAffectedStudentNames = $this->versionAffectedStudentNames($templates);
        $diagnostics = $this->buildVersionDiagnostics($templates);
        $scopeCounts = $this->scopeCounts();

        return view('livewire.teacher.automated-tasks-board', [
            'templates' => $templates,
            'scopeCounts' => $scopeCounts,
            'versionAssignmentCounts' => $versionAssignmentCounts,
            'versionAffectedStudentNames' => $versionAffectedStudentNames,
            'diagnostics' => $diagnostics,
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
        $templates = $this->ownedTemplateQuery()
            ->with([
                'versions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                'mainTasks' => fn ($query) => $query
                    ->with(['attachments' => fn ($attachmentQuery) => $attachmentQuery->orderBy('sort_order')->orderBy('id')])
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'versions.versionTasks',
            ])
            ->get();

        foreach ($templates as $template) {
            $this->templateForms[$template->id] = $this->templateFormFromModel($template);

            $activeVersionId = $this->activeVersionByTemplate[$template->id] ?? null;

            if (! $template->versions->contains('id', $activeVersionId)) {
                $this->activeVersionByTemplate[$template->id] = $template->versions->first()?->id;
            }

            foreach ($template->versions as $version) {
                foreach ($template->mainTasks as $mainTask) {
                    $existing = $version->versionTasks->firstWhere('main_task_id', $mainTask->id);

                    $this->versionTaskForms[$version->id][$mainTask->id] = [
                        'enabled' => $existing !== null,
                        'description_override' => $existing?->description_override,
                    ];
                }
            }

            $this->publishErrors[$template->id] = $this->publishErrors[$template->id] ?? [];
        }
    }

    private function renderTemplates(): EloquentCollection
    {
        return $this->templateQuery()
            ->with([
                'versions' => fn ($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->with([
                        'versionTasks' => fn ($versionTaskQuery) => $versionTaskQuery
                            ->orderBy('sort_order')
                            ->orderBy('id')
                            ->with([
                                'mainTask.attachments' => fn ($attachmentQuery) => $attachmentQuery
                                    ->orderBy('sort_order')
                                    ->orderBy('id'),
                            ]),
                    ]),
                'mainTasks' => fn ($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->with([
                        'attachments' => fn ($attachmentQuery) => $attachmentQuery
                            ->orderBy('sort_order')
                            ->orderBy('id'),
                        'taskType',
                    ]),
            ])
            ->orderByDesc('id')
            ->get();
    }

    private function ensureTemplateForms(EloquentCollection $templates): void
    {
        foreach ($templates as $template) {
            if (! isset($this->templateForms[$template->id])) {
                $this->templateForms[$template->id] = $this->templateFormFromModel($template);
            }
        }
    }

    private function templateFormFromModel(MainDailySessionTemplate $template): array
    {
        return [
            'title' => (string) $template->title,
            'recurrence_kind' => (string) $template->recurrence_kind,
            'recurrence_interval' => (int) ($template->recurrence_interval ?? 1),
            'recurrence_weekdays' => $template->recurrence_weekdays
                ? $this->numericWeekdaysToTextKeys(
                    array_values(array_filter(explode(',', (string) $template->recurrence_weekdays)))
                )
                : [],
            'recurrence_day_of_month' => $template->recurrence_day_of_month,
        ];
    }

    private function templateQuery(): Builder
    {
        $query = $this->ownedTemplateQuery();

        if ($this->templateScope === 'archived') {
            return $query->where('status', 'archived');
        }

        return $query->whereIn('status', ['draft', 'active']);
    }

    private function scopeCounts(): array
    {
        return [
            'working' => $this->ownedTemplateQuery()
                ->whereIn('status', ['draft', 'active'])
                ->count(),
            'archived' => $this->ownedTemplateQuery()
                ->where('status', 'archived')
                ->count(),
        ];
    }

    private function aggregateVersionAssignmentCounts(EloquentCollection $templates): array
    {
        $versionIds = $templates
            ->flatMap(fn (MainDailySessionTemplate $template) => $template->versions->pluck('id'))
            ->map(fn ($value): int => (int) $value)
            ->values();

        if ($versionIds->isEmpty()) {
            return [];
        }

        $today = now()->toDateString();

        return DB::table('main_daily_session_student_assignments')
            ->select('version_id', DB::raw('COUNT(DISTINCT student_id) as aggregate_count'))
            ->whereIn('version_id', $versionIds->all())
            ->where(function ($query) use ($today): void {
                $query->whereNull('effective_to_date')
                    ->orWhere('effective_to_date', '>=', $today);
            })
            ->groupBy('version_id')
            ->pluck('aggregate_count', 'version_id')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    private function versionAffectedStudentNames(EloquentCollection $templates): array
    {
        $versionIds = $templates
            ->flatMap(fn (MainDailySessionTemplate $template) => $template->versions->pluck('id'))
            ->map(fn ($value): int => (int) $value)
            ->values();

        if ($versionIds->isEmpty()) {
            return [];
        }

        $today = now()->toDateString();

        return DB::table('main_daily_session_student_assignments as assignments')
            ->join('students', 'students.id', '=', 'assignments.student_id')
            ->select(
                'assignments.version_id',
                'students.id as student_id',
                'students.first_name',
                'students.last_name'
            )
            ->whereIn('assignments.version_id', $versionIds->all())
            ->where(function ($query) use ($today): void {
                $query->whereNull('assignments.effective_to_date')
                    ->orWhere('assignments.effective_to_date', '>=', $today);
            })
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->orderBy('students.id')
            ->get()
            ->groupBy('version_id')
            ->map(function ($students): array {
                return $students
                    ->take(5)
                    ->map(function ($student): string {
                        $name = trim(((string) $student->first_name).' '.((string) $student->last_name));

                        return $name !== '' ? $name : 'Student #'.$student->student_id;
                    })
                    ->values()
                    ->all();
            })
            ->all();
    }

    private function buildVersionDiagnostics(EloquentCollection $templates): array
    {
        $validator = app(AutomatedTaskPublishValidator::class);
        $output = [];

        foreach ($templates as $template) {
            foreach ($template->versions as $version) {
                $output[$template->id][$version->id] = $validator->diagnoseVersion($version);
            }
        }

        return $output;
    }

    private function validatedTemplatePayload(array $input, bool $creating, ?int $templateId = null): array
    {
        $uniqueTitleRule = Rule::unique('main_daily_session_templates', 'title')
            ->where('subject_id', $this->subjectId)
            ->where('created_by_user_id', Auth::id());

        if (! $creating && $templateId !== null) {
            $uniqueTitleRule = $uniqueTitleRule->ignore($templateId);
        }

        $weekdayRules = $input['recurrence_kind'] === 'weekly'
            ? ['required', 'array', 'min:1']
            : ['nullable', 'array'];

        $validated = Validator::make($input, [
            'title' => [
                'required',
                'string',
                'max:191',
                $uniqueTitleRule,
            ],
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
            'recurrence_kind' => $validated['recurrence_kind'],
            'recurrence_interval' => $validated['recurrence_kind'] === 'daily'
                ? (int) $validated['recurrence_interval']
                : 1,
            'recurrence_weekdays' => ($validated['recurrence_kind'] ?? null) === 'weekly'
                ? implode(',', $this->normalizeWeekdaysToNumeric($validated['recurrence_weekdays'] ?? []))
                : null,
            'recurrence_day_of_month' => ($validated['recurrence_kind'] ?? null) === 'monthly'
                ? (int) $validated['recurrence_day_of_month']
                : null,
        ];
    }

    private function resolveOwnedTemplateOrFail(int $templateId): MainDailySessionTemplate
    {
        return $this->ownedTemplateQuery()
            ->whereKey($templateId)
            ->firstOrFail();
    }

    private function resolveOwnedVersionOrFail(int $versionId): MainDailySessionVersion
    {
        return MainDailySessionVersion::query()
            ->whereKey($versionId)
            ->whereHas('template', fn ($query) => $query
                ->where('subject_id', $this->subjectId)
                ->where('created_by_user_id', Auth::id()))
            ->firstOrFail();
    }

    private function resolveOwnedMainTaskOrFail(int $mainTaskId): MainDailySessionMainTask
    {
        return MainDailySessionMainTask::query()
            ->whereKey($mainTaskId)
            ->whereHas('template', fn ($query) => $query
                ->where('subject_id', $this->subjectId)
                ->where('created_by_user_id', Auth::id()))
            ->firstOrFail();
    }

    private function ownedTemplateQuery(): Builder
    {
        return MainDailySessionTemplate::query()
            ->forSubject($this->subjectId)
            ->where('created_by_user_id', Auth::id());
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

    private function normalizeTemplateScope(string $scope): string
    {
        return in_array($scope, ['working', 'archived'], true) ? $scope : 'working';
    }

    public function scopeUrl(string $scope): string
    {
        return $this->boardUrl.'?automated_scope='.$this->normalizeTemplateScope($scope);
    }
}
