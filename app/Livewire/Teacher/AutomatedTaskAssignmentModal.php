<?php

namespace App\Livewire\Teacher;

use App\Models\MainDailySessionSubscription;
use App\Models\MainDailySessionTemplate;
use App\Models\Student;
use App\Models\TeacherSubjectClass;
use App\Services\AutomatedTaskAssignmentService;
use App\Services\AutomatedTaskSubscriptionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class AutomatedTaskAssignmentModal extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public bool $show = false;

    public ?int $templateId = null;

    public ?int $activeVersionId = null;

    public string $search = '';

    public string $statusFilter = 'all';

    public int $perPage = 8;

    /** @var array<int, bool> */
    public array $selectedStudentIds = [];

    /** @var array<int, true> */
    public array $touchedStudentIds = [];

    /** @var array<int, array<string, mixed>> */
    public array $rowForms = [];

    #[On('open-automated-task-assignment-modal')]
    public function open(int $templateId, ?int $versionId = null): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId)->load([
            'versions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
        ]);

        $activeVersion = $versionId !== null
            ? $template->versions->firstWhere('id', $versionId)
            : $template->versions->first();

        $this->resetValidation();
        $this->show = true;
        $this->templateId = $template->id;
        $this->activeVersionId = $activeVersion?->id !== null ? (int) $activeVersion->id : null;
        $this->search = '';
        $this->statusFilter = 'all';
        $this->selectedStudentIds = [];
        $this->touchedStudentIds = [];
        $this->rowForms = [];
        $this->resetPage();
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStudentIds(mixed $value, string $key): void
    {
        $this->touchedStudentIds[(int) $key] = true;
    }

    public function saveBulk(
        AutomatedTaskAssignmentService $assignmentService,
        AutomatedTaskSubscriptionService $subscriptionService
    ): void {
        $template = $this->resolveOwnedTemplateOrFail((int) $this->templateId)->load([
            'versions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
        ]);

        $activeVersion = $template->versions->firstWhere('id', $this->activeVersionId);
        abort_if($activeVersion === null, 404);

        $allBulkRows = $this->bulkRowsForTemplate($template, false);
        $visibleBulkRows = $this->bulkRowsForTemplate($template, true);
        $this->initializeSelectedStudents($allBulkRows, (int) $activeVersion->id);
        $managedStudentIds = $visibleBulkRows
            ->pluck('student_id')
            ->merge(array_keys($this->touchedStudentIds))
            ->map(fn ($value): int => (int) $value)
            ->unique()
            ->values();
        $managedBulkRows = $allBulkRows
            ->filter(fn (array $row): bool => $managedStudentIds->contains((int) $row['student_id']))
            ->values();

        $requiresVersionAssignmentWrite = $managedBulkRows->contains(function (array $row) use ($activeVersion): bool {
            return $this->studentIsSelected((int) $row['student_id'])
                && ! in_array((int) $activeVersion->id, $row['current_version_ids'] ?? [], true);
        });

        Validator::make([], [])->after(function ($validator) use ($assignmentService, $activeVersion, $requiresVersionAssignmentWrite): void {
            if ($requiresVersionAssignmentWrite && ! $assignmentService->versionIsAssignable((int) $activeVersion->id)) {
                $validator->errors()->add(
                    'bulk_assignment',
                    'This version is not ready for assignment yet. Add meaningful content before assigning or moving students into it.'
                );
            }
        })->validate();

        $today = Carbon::today();

        DB::transaction(function () use ($managedBulkRows, $template, $activeVersion, $today, $assignmentService, $subscriptionService): void {
            foreach ($managedBulkRows as $row) {
                $studentId = (int) $row['student_id'];
                $selected = $this->studentIsSelected($studentId);
                $currentVersionIds = $row['current_version_ids'] ?? [];
                $isAssignedToActiveVersion = in_array((int) $activeVersion->id, $currentVersionIds, true);

                $subscription = MainDailySessionSubscription::query()
                    ->forStudent($studentId)
                    ->forTemplate($template->id)
                    ->lockForUpdate()
                    ->first();

                if ($isAssignedToActiveVersion && ! $selected) {
                    $assignmentService->unassignVersion(
                        $studentId,
                        $template->id,
                        (int) $activeVersion->id,
                        $today->copy()->subDay(),
                        (int) Auth::id()
                    );

                    continue;
                }

                if (! $selected) {
                    continue;
                }

                if ($subscription === null || ! (bool) $subscription->is_active) {
                    $subscription = $subscriptionService->subscribe(
                        $studentId,
                        $template->id,
                        $today
                    );
                }

                if (! $isAssignedToActiveVersion) {
                    $assignmentService->createAssignment(
                        $studentId,
                        $template->id,
                        (int) $activeVersion->id,
                        $today,
                        (int) Auth::id()
                    );

                    continue;
                }
            }
        });

        $this->dispatch('automated-task-assignment-saved');
        $this->close();
    }

    public function saveRow(
        int $studentId,
        AutomatedTaskAssignmentService $assignmentService,
        AutomatedTaskSubscriptionService $subscriptionService
    ): void {
        $template = $this->resolveOwnedTemplateOrFail((int) $this->templateId);
        $student = $this->resolveEligibleStudentOrFail($template, $studentId);
        $state = data_get($this->rowForms, "{$studentId}.subscription_state", 'not_subscribed');
        $versionId = $this->normalizeNullableInt(data_get($this->rowForms, "{$studentId}.version_id"));
        $today = Carbon::today();

        Validator::make([
            'subscription_state' => $state,
            'version_id' => $versionId,
        ], [
            'subscription_state' => ['required', Rule::in(['not_subscribed', 'active', 'paused'])],
            'version_id' => ['nullable', 'integer'],
        ])->after(function ($validator) use ($template, $versionId, $assignmentService): void {
            if ($versionId !== null && ! $template->versions()->whereKey($versionId)->exists()) {
                $validator->errors()->add('version_id', 'Choose one of this template\'s versions.');
            }

            if ($versionId !== null && ! $assignmentService->versionIsAssignable($versionId)) {
                $validator->errors()->add('version_id', 'That version is not ready yet. Add meaningful content before assigning students to it.');
            }
        })->validate();

        $subscription = MainDailySessionSubscription::query()
            ->forStudent((int) $student->id)
            ->forTemplate($template->id)
            ->first();

        $isCurrentlyPaused = $subscription !== null
            && (bool) $subscription->is_active
            && $subscription->isPaused();

        if ($isCurrentlyPaused) {
            $this->savePausedRowVersionOnly(
                $student,
                $template,
                $versionId,
                $today,
                $assignmentService
            );

            $this->dispatch('automated-task-assignment-saved');

            return;
        }

        if ($state === 'paused') {
            Validator::make([], [])->after(function ($validator): void {
                $validator->errors()->add(
                    'subscription_state',
                    'Paused subscription state is controlled by admin. Teachers can only assign or unassign versions.'
                );
            })->validate();
        }

        DB::transaction(function () use (
            $student,
            $template,
            $state,
            $versionId,
            $today,
            $assignmentService,
            $subscriptionService,
            $subscription
        ): void {
            $currentAssignment = $assignmentService->resolveEffectiveAssignment((int) $student->id, $template->id, $today);

            if ($state === 'not_subscribed') {
                if ($subscription !== null) {
                    $subscriptionService->deactivate((int) $student->id, $template->id);
                }

                if ($currentAssignment !== null) {
                    $assignmentService->unassign((int) $student->id, $template->id, $today->copy()->subDay(), (int) Auth::id());
                }

                return;
            }

            $subscriptionService->subscribe((int) $student->id, $template->id, $today);

            if ($versionId === null) {
                if ($currentAssignment !== null) {
                    $assignmentService->unassign((int) $student->id, $template->id, $today->copy()->subDay(), (int) Auth::id());
                }

                return;
            }

            $alreadyAssignedToVersion = $currentAssignment !== null
                && (int) $currentAssignment->version_id === $versionId;

            if (! $alreadyAssignedToVersion) {
                $assignmentService->createAssignment((int) $student->id, $template->id, $versionId, $today, (int) Auth::id());
            }
        });

        $this->dispatch('automated-task-assignment-saved');
    }

    private function savePausedRowVersionOnly(
        object $student,
        MainDailySessionTemplate $template,
        ?int $versionId,
        Carbon $today,
        AutomatedTaskAssignmentService $assignmentService
    ): void {
        DB::transaction(function () use ($student, $template, $versionId, $today, $assignmentService): void {
            $currentAssignment = $assignmentService->resolveEffectiveAssignment(
                (int) $student->id,
                $template->id,
                $today
            );

            if ($versionId === null) {
                if ($currentAssignment !== null) {
                    $assignmentService->unassign(
                        (int) $student->id,
                        $template->id,
                        $today->copy()->subDay(),
                        (int) Auth::id()
                    );
                }

                return;
            }

            $alreadyAssignedToVersion = $currentAssignment !== null
                && (int) $currentAssignment->version_id === $versionId;

            if (! $alreadyAssignedToVersion) {
                $assignmentService->createAssignment(
                    (int) $student->id,
                    $template->id,
                    $versionId,
                    $today,
                    (int) Auth::id()
                );
            }
        });
    }

    public function render(): View
    {
        if (! $this->show || $this->templateId === null) {
            return view('livewire.teacher.automated-task-assignment-modal', [
                'template' => null,
                'activeVersion' => null,
                'activeVersionReady' => false,
                'versions' => collect(),
                'students' => null,
                'summary' => ['active' => 0, 'paused' => 0, 'unassigned' => 0, 'not_subscribed' => 0],
                'rows' => [],
                'bulkSections' => [],
            ]);
        }

        $template = $this->resolveOwnedTemplateOrFail($this->templateId)->load([
            'versions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
        ]);

        $activeVersion = $template->versions->firstWhere('id', $this->activeVersionId) ?? $template->versions->first();
        $this->activeVersionId = $activeVersion?->id !== null ? (int) $activeVersion->id : null;

        $baseQuery = $this->baseEligibleStudentsQuery($template);
        $summary = $this->summaryCounts(clone $baseQuery, $template);
        $students = $this->applyStatusFilter(clone $baseQuery, $template)->paginate($this->perPage);
        $rows = $this->buildRowsFromStudents(collect($students->items()), $template);

        $allBulkRows = $activeVersion ? $this->bulkRowsForTemplate($template, false) : collect();
        $visibleBulkRows = $activeVersion ? $this->bulkRowsForTemplate($template, true) : collect();

        if ($activeVersion) {
            $this->initializeSelectedStudents($allBulkRows, (int) $activeVersion->id);
        }

        return view('livewire.teacher.automated-task-assignment-modal', [
            'template' => $template,
            'activeVersion' => $activeVersion,
            'activeVersionReady' => $activeVersion
                ? app(AutomatedTaskAssignmentService::class)->versionIsAssignable((int) $activeVersion->id)
                : false,
            'versions' => $template->versions,
            'students' => $students,
            'summary' => $summary,
            'rows' => $rows,
            'bulkSections' => $activeVersion ? $this->buildBulkSections($visibleBulkRows, (int) $activeVersion->id) : [],
        ]);
    }

    private function bulkRowsForTemplate(MainDailySessionTemplate $template, bool $applySearch): SupportCollection
    {
        return collect(
            $this->buildRowsFromStudents(
                $this->baseEligibleStudentsQuery($template, $applySearch)->get(),
                $template
            )
        )->reject(fn (array $row): bool => (bool) $row['is_paused_subscription'])
            ->values();
    }

    private function baseEligibleStudentsQuery(MainDailySessionTemplate $template, bool $applySearch = true): EloquentBuilder
    {
        $query = Student::query()
            ->whereIn('students.id', $this->eligibleStudentIdsSubquery($template))
            ->where(function ($lifecycleQuery): void {
                $lifecycleQuery->whereNull('students.account_status')
                    ->orWhere('students.account_status', '')
                    ->orWhere('students.account_status', 'active');
            })
            ->with([
                'parent:id,first_name,last_name',
                'currentClass:id,title',
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('students.id');

        if ($applySearch && $this->search !== '') {
            $search = trim($this->search);

            $query->where(function ($innerQuery) use ($search): void {
                $innerQuery->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('students.id', 'like', "%{$search}%")
                    ->orWhereHas('parent', function ($parentQuery) use ($search): void {
                        $parentQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function applyStatusFilter(EloquentBuilder $query, MainDailySessionTemplate $template): EloquentBuilder
    {
        return match ($this->statusFilter) {
            'active' => $query->whereExists($this->activeSubscriptionSubquery($template->id)),
            'paused' => $query->whereExists($this->pausedSubscriptionSubquery($template->id)),
            'unassigned' => $query
                ->whereExists($this->activeSubscriptionSubquery($template->id))
                ->whereNotExists($this->currentAssignmentSubquery($template->id)),
            'not_subscribed' => $query->whereNotExists($this->subscribedSubscriptionSubquery($template->id)),
            default => $query,
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRowsFromStudents(SupportCollection $students, MainDailySessionTemplate $template): array
    {
        $studentIds = $students
            ->pluck('id')
            ->map(fn ($value): int => (int) $value)
            ->all();

        if (empty($studentIds)) {
            return [];
        }

        $today = Carbon::today()->toDateString();

        $subscriptions = MainDailySessionSubscription::query()
            ->forTemplate($template->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        $assignments = DB::table('main_daily_session_student_assignments as assignments')
            ->select('assignments.*', 'versions.display_name as version_display_name')
            ->leftJoin('main_daily_session_versions as versions', 'versions.id', '=', 'assignments.version_id')
            ->where('assignments.main_daily_session_template_id', $template->id)
            ->whereIn('assignments.student_id', $studentIds)
            ->where('assignments.effective_from_date', '<=', $today)
            ->where(function ($query) use ($today): void {
                $query->whereNull('assignments.effective_to_date')
                    ->orWhere('assignments.effective_to_date', '>=', $today);
            })
            ->get()
            ->groupBy('student_id');

        $rows = $students->map(function (Student $student) use ($subscriptions, $assignments): array {
            $subscription = $subscriptions->get($student->id);
            $studentAssignments = $assignments->get($student->id, collect());
            $firstAssignment = $studentAssignments->first();
            $displayName = $student->display_name;
            $parentName = $student->parent?->display_name ?? 'No parent linked';
            $fatherName = trim((string) ($student->father_name ?? ''));
            $className = $student->currentClass?->title ?? 'No current class';
            $studentWithFather = trim($student->first_name.' '.($fatherName !== '' ? $fatherName : ($student->parent?->first_name ?? '')));

            $subscriptionState = 'not_subscribed';

            if ($subscription !== null && ! (bool) $subscription->is_active) {
                $subscriptionState = 'not_subscribed';
            } elseif ($subscription !== null && $subscription->isPaused()) {
                $subscriptionState = 'paused';
            } elseif ($subscription !== null && (bool) $subscription->is_active) {
                $subscriptionState = 'active';
            }

            if (! array_key_exists($student->id, $this->rowForms)) {
                $this->rowForms[$student->id] = [
                    'subscription_state' => $subscriptionState,
                    'version_id' => $firstAssignment->version_id ?? null,
                ];
            } else {
                if ($subscriptionState === 'paused') {
                    $this->rowForms[$student->id]['subscription_state'] = 'paused';
                } else {
                    $this->rowForms[$student->id]['subscription_state'] ??= $subscriptionState;
                }

                if (! array_key_exists('version_id', $this->rowForms[$student->id])) {
                    $this->rowForms[$student->id]['version_id'] = $firstAssignment->version_id ?? null;
                }
            }

            $currentVersionIds = $studentAssignments
                ->pluck('version_id')
                ->map(fn ($value): int => (int) $value)
                ->values()
                ->all();
            $currentVersionNames = $studentAssignments
                ->pluck('version_display_name')
                ->filter()
                ->values()
                ->all();

            return [
                'student_id' => (int) $student->id,
                'display_name' => $displayName,
                'student_with_father' => $studentWithFather !== '' ? $studentWithFather : $displayName,
                'parent_name' => $parentName,
                'class_name' => $className,
                'subscription_state' => $subscriptionState,
                'current_version_id' => $firstAssignment->version_id ?? null,
                'current_version_name' => $currentVersionNames === [] ? 'Unassigned' : implode(', ', $currentVersionNames),
                'current_version_ids' => $currentVersionIds,
                'current_version_names' => $currentVersionNames,
                'lifecycle_label' => $student->lifecycleStatusLabel(),
                'lifecycle_tone' => $student->lifecycleStatusTone() ?? 'secondary',
                'is_paused_subscription' => $subscriptionState === 'paused',
                'is_active_subscription' => $subscriptionState === 'active',
            ];
        })->values();

        $duplicateNames = $rows
            ->groupBy('display_name')
            ->map(fn ($group): int => $group->count())
            ->filter(fn ($count): bool => $count > 1);

        return $rows->map(function (array $row) use ($duplicateNames): array {
            $row['duplicate_name'] = $duplicateNames->has($row['display_name']);

            return $row;
        })->all();
    }

    /**
     * @return array<string, array{title: string, description: string, rows: array<int, array<string, mixed>>}>
     */
    private function buildBulkSections(SupportCollection $rows, int $activeVersionId): array
    {
        return [
            'assigned_here' => [
                'title' => 'Assigned to this version',
                'description' => 'These students stay selected by default. Unselect one to remove them from this version on save.',
                'rows' => $rows
                    ->filter(fn (array $row): bool => in_array($activeVersionId, $row['current_version_ids'] ?? [], true))
                    ->values()
                    ->all(),
            ],
            'unassigned' => [
                'title' => 'Not assigned to any version of this template',
                'description' => 'Select students here to assign this routine version for future generation.',
                'rows' => $rows
                    ->filter(fn (array $row): bool => empty($row['current_version_ids'] ?? []))
                    ->values()
                    ->all(),
            ],
            'assigned_elsewhere' => [
                'title' => 'Assigned to another version of this template',
                'description' => 'Select students here to MOVE them to this version for future generation. Leaving them unselected keeps their current version unchanged.',
                'rows' => $rows
                    ->filter(fn (array $row): bool => ! in_array($activeVersionId, $row['current_version_ids'] ?? [], true)
                        && ! empty($row['current_version_ids'] ?? []))
                    ->values()
                    ->all(),
            ],
        ];
    }

    private function initializeSelectedStudents(SupportCollection $rows, int $activeVersionId): void
    {
        foreach ($rows as $row) {
            $studentId = (int) $row['student_id'];

            if (! array_key_exists($studentId, $this->selectedStudentIds)) {
                $this->selectedStudentIds[$studentId] = in_array($activeVersionId, $row['current_version_ids'] ?? [], true);
            }
        }
    }

    private function studentIsSelected(int $studentId): bool
    {
        $value = data_get($this->selectedStudentIds, $studentId, false);

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ?? false;
    }

    private function summaryCounts(EloquentBuilder $query, MainDailySessionTemplate $template): array
    {
        return [
            'active' => (clone $query)->whereExists($this->activeSubscriptionSubquery($template->id))->count(),
            'paused' => (clone $query)->whereExists($this->pausedSubscriptionSubquery($template->id))->count(),
            'unassigned' => (clone $query)
                ->whereExists($this->activeSubscriptionSubquery($template->id))
                ->whereNotExists($this->currentAssignmentSubquery($template->id))
                ->count(),
            'not_subscribed' => (clone $query)->whereNotExists($this->subscribedSubscriptionSubquery($template->id))->count(),
        ];
    }

    private function activeSubscriptionSubquery(int $templateId): QueryBuilder
    {
        return DB::query()
            ->selectRaw('1')
            ->from('main_daily_session_subscriptions as subscriptions')
            ->whereColumn('subscriptions.student_id', 'students.id')
            ->where('subscriptions.main_daily_session_template_id', $templateId)
            ->where('subscriptions.is_active', 1)
            ->whereNull('subscriptions.paused_at');
    }

    private function pausedSubscriptionSubquery(int $templateId): QueryBuilder
    {
        return DB::query()
            ->selectRaw('1')
            ->from('main_daily_session_subscriptions as subscriptions')
            ->whereColumn('subscriptions.student_id', 'students.id')
            ->where('subscriptions.main_daily_session_template_id', $templateId)
            ->where('subscriptions.is_active', 1)
            ->whereNotNull('subscriptions.paused_at');
    }

    private function subscribedSubscriptionSubquery(int $templateId): QueryBuilder
    {
        return DB::query()
            ->selectRaw('1')
            ->from('main_daily_session_subscriptions as subscriptions')
            ->whereColumn('subscriptions.student_id', 'students.id')
            ->where('subscriptions.main_daily_session_template_id', $templateId)
            ->where('subscriptions.is_active', 1);
    }

    private function currentAssignmentSubquery(int $templateId): QueryBuilder
    {
        $today = Carbon::today()->toDateString();

        return DB::query()
            ->selectRaw('1')
            ->from('main_daily_session_student_assignments as assignments')
            ->whereColumn('assignments.student_id', 'students.id')
            ->where('assignments.main_daily_session_template_id', $templateId)
            ->where('assignments.effective_from_date', '<=', $today)
            ->where(function ($query) use ($today): void {
                $query->whereNull('assignments.effective_to_date')
                    ->orWhere('assignments.effective_to_date', '>=', $today);
            });
    }

    private function resolveOwnedTemplateOrFail(int $templateId): MainDailySessionTemplate
    {
        TeacherSubjectClass::query()
            ->where('subject_id', function ($query) use ($templateId): void {
                $query->select('subject_id')
                    ->from('main_daily_session_templates')
                    ->where('id', $templateId)
                    ->where('created_by_user_id', Auth::id())
                    ->limit(1);
            })
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->firstOrFail();

        return MainDailySessionTemplate::query()
            ->whereKey($templateId)
            ->where('created_by_user_id', Auth::id())
            ->firstOrFail();
    }

    private function resolveEligibleStudentOrFail(MainDailySessionTemplate $template, int $studentId): Student
    {
        return $this->baseEligibleStudentsQuery($template, false)
            ->whereKey($studentId)
            ->firstOrFail();
    }

    private function eligibleStudentIdsSubquery(MainDailySessionTemplate $template): QueryBuilder
    {
        return DB::table('students_subjects')
            ->select('students_subjects.student_id')
            ->join('grade_level_subjects', 'grade_level_subjects.id', '=', 'students_subjects.grade_level_subject_id')
            ->where('students_subjects.academic_year_id', \App\Models\AcademicYear::currentId())
            ->where('students_subjects.status', 'active')
            ->where('grade_level_subjects.subject_id', $template->subject_id)
            ->whereIn('students_subjects.class_subject_id', $this->ownedClassSubjectIds($template))
            ->distinct();
    }

    /**
     * @return array<int, int>
     */
    private function ownedClassSubjectIds(MainDailySessionTemplate $template): array
    {
        return TeacherSubjectClass::query()
            ->where('subject_id', $template->subject_id)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->pluck('class_subject_id')
            ->filter()
            ->map(fn ($value): int => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
