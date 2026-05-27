<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\PunishmentAgreement;
use App\Models\PunishmentType;
use App\Models\RewardDisciplinePoint;
use App\Models\SessionTaskStudent;
use App\Models\Student;
use App\Models\StudentTaskApprovalEvent;
use App\Models\Student_punishment;
use App\Models\Student_Session_Discipline;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use App\Services\RewardProgressionService;
use App\Support\LifecycleGate;
use App\Support\ParentBehaviorSubjectResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RewardDisciplinePoints extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    protected static ?bool $subjectsHasActiveColumn = null;

    public int $historyPage = 1;

    public ?int $classSessionId = null;

    public int $historyPerPage = 20;

    public int $historyTotal = 0;

    public ?string $historyTypeFilter = null;

    public string $historyTab = 'behavior';

    public ?string $historyStartDate = null;

    public ?string $historyEndDate = null;

    public array $historyFilterCounts = [
        'Positive' => 0,
        'Slip' => 0,
        'No Way' => 0,
        'Consequences' => 0,
    ];

    public ?int $selectedSubjectId = null;

    public array $subjectFilters = [];

    public int $taskHistoryPerPage = 10;

    public int $taskHistoryTotal = 0;

    public array $taskHistoryRows = [];

    public array $taskHistorySummary = [
        'completed' => 0,
        'points' => 0,
    ];

    public int $studentId;

    public ?int $teacherSubjectClassesId = null;

    public ?int $pointsInput = null;

    public string $activeTab = 'Positive';

    public array $positiveBehaviors = [];
    // public array $needsWorkBehaviors = [];

    public array $slipBehaviors = [];

    public array $noWayBehaviors = [];

    public ?array $recentAward = null;      // لرسالة التأكيد

    public string $studentName = '';

    public array $sessionDisciplines = [];

    public string $userRole = 'teacher'; // القيمة الافتراضية

    public bool $showDescModal = false;

    public ?int $pendingBehaviorId = null;

    public string $pendingBehaviorTitle = '';

    public int $pendingBehaviorPoints = 0;

    public string $pendingBehaviorType = 'Positive';

    public ?string $descriptionInput = null;

    public ?int $academicYearId = null;

    public ?int $selectedBehaviorId = null;     // behavior المختار من dropdown

    public ?string $pendingType = null;         // النوع اللي فتحنا عليه الـ popup: Positive/Slip/No Way

    public array $modalBehaviors = [];          // قائمة behaviors للـ dropdown

    // Punishment inside behavior modal (Slip / No Way)
    public array $punishmentAgreements = [];          // agreements buttons

    public ?int $selectedPunishmentAgreementId = null;

    public ?int $punishmentTypeId = null;             // Minor Slip / Serious Action

    public array $behaviorCounts = [];     // عدد مرات كل behavior

    public array $behaviorHistory = [];    // تفاصيل behavior في popup

    public int $behaviorHistoryPerPage = 20;

    public int $behaviorHistoryTotal = 0;

    public bool $showBehaviorHistoryModal = false;

    public ?int $historyBehaviorId = null;

    public int $slipPunishmentsCount = 0;

    public int $noWayPunishmentsCount = 0;

    public bool $showPunishmentHistoryModal = false;

    public ?string $punishmentHistoryType = null; // Slip | No Way

    public array $punishmentHistory = [];

    public int $punishmentHistoryPerPage = 20;

    public int $punishmentHistoryTotal = 0;

    public int $total_post_point = 0;

    public int $total_negative_point = 0;

    public ?string $pointsLabStateSignature = null;

    public ?string $punishmentNote = null;

    #[On('reward-discipline-points:refresh')]
    public function refreshAfterExternalBehaviorChange(?string $type = null): void
    {
        if (in_array($type, ['Positive', 'Slip', 'No Way'])) {
            $this->activeTab = $type;
        }

        $this->recentAward = null;
        $this->loadSessionDisciplines();
        $this->loadBehaviorCounts();
        $this->loadPunishmentCounts();
        $this->loadTaskHistoryIfActive();
        $this->pointsLabStateSignature = $this->pointsLabStateSignature();
        $this->resetPage('historyPage');
    }

    protected function parentLifecycleAllowsBehavior(): bool
    {
        if ($this->userRole !== 'parent') {
            return true;
        }

        $lifecycleGate = LifecycleGate::inspect($this->studentId);
        if (! $lifecycleGate->denied()) {
            return true;
        }

        $this->addError('selectedBehaviorId', LifecycleGate::NEUTRAL_MESSAGE);
        $this->dispatch('toast', type: 'warning', message: LifecycleGate::NEUTRAL_MESSAGE);

        return false;
    }

    protected function resolveTeacherSubjectClassForStudent(): ?TeacherSubjectClass
    {
        if ($this->userRole === 'teacher') {
            if (! $this->teacherSubjectClassesId) {
                return null;
            }

            $teacherSubjectClass = TeacherSubjectClass::query()
                ->whereKey($this->teacherSubjectClassesId)
                ->where('user_teacher_coteacher_id', auth()->id())
                ->availableForTeacher()
                ->firstOrFail();

            abort_unless(
                StudentsSubject::query()
                    ->where('student_id', $this->studentId)
                    ->where('class_subject_id', $teacherSubjectClass->class_subject_id)
                    ->where('status', 'active')
                    ->exists(),
                403
            );

            return $teacherSubjectClass;
        }

        if ($this->userRole === 'parent') {
            $parentModel = Auth::user()?->parent_user;
            abort_unless(
                $parentModel && $parentModel->students()->where('students.id', $this->studentId)->exists(),
                403
            );

            return app(ParentBehaviorSubjectResolver::class)->resolveForStudent($this->studentId);
        }

        return null;
    }

    protected function syncResolvedTeacherSubjectClassId(): ?TeacherSubjectClass
    {
        $teacherSubjectClass = $this->resolveTeacherSubjectClassForStudent();
        $this->teacherSubjectClassesId = $teacherSubjectClass?->id;

        return $teacherSubjectClass;
    }

    public function mount(
        int $studentId,
        ?int $teacherSubjectClassesId = null, ?int $academicYearId = null
    ): void {
        $this->studentId = $studentId;
        $this->teacherSubjectClassesId = $teacherSubjectClassesId;
        $this->academicYearId = $academicYearId ?? AcademicYear::currentId();
        $this->studentName = Student::whereKey($studentId)->value('first_name') ?? '';

        $user = Auth::user();
        if ($user?->hasRole('teacher')) {
            $this->userRole = 'teacher';
        } elseif ($user?->hasRole('student')) {
            $this->userRole = 'student';
        } elseif ($user?->hasRole('parent')) {
            $this->userRole = 'parent';
        } else {
            $this->userRole = 'other';
        }

        $this->assertMountedStudentAccess();

        if (! $this->parentLifecycleAllowsBehavior()) {
            return;
        }

        if (in_array($this->userRole, ['teacher', 'parent'], true)) {
            $this->teacherSubjectClassesId = $this->resolveTeacherSubjectClassForStudent()?->id;
        }

        $this->loadSubjectFilters();
        $this->resetPage('historyPage');
        $this->loadSessionDisciplines();
        $this->loadBehaviorCounts();
        $this->loadBehaviors();
        $this->loadPunishmentCounts();
        $this->loadTaskHistoryIfActive();
        $this->pointsLabStateSignature = $this->pointsLabStateSignature();

    }

    protected function assertMountedStudentAccess(): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($this->userRole === 'student') {
            abort_unless(
                Student::query()
                    ->whereKey($this->studentId)
                    ->where('user_id', $user->id)
                    ->exists(),
                403
            );
        }

        if ($this->userRole === 'parent') {
            $parentModel = $user->parent_user;
            abort_unless(
                $parentModel && $parentModel->students()->where('students.id', $this->studentId)->exists(),
                403
            );
        }
    }

    protected function loadBehaviors(): void
    {
        $baseQuery = RewardDisciplinePoint::active()
            ->where(function ($q) {
                $q->whereNull('student_id')
                    ->orWhere('student_id', $this->studentId);
            });

        $this->positiveBehaviors = (clone $baseQuery)
            ->where('type', 'Positive')
            ->orderBy('teacher_desc', 'desc')
            ->orderBy('sort')
            ->get()
            ->toArray();

        // $this->needsWorkBehaviors = (clone $baseQuery)
        //     ->where('type', 'Slip')
        //     ->orderBy('sort')
        //     ->get()
        //     ->toArray();

        $this->slipBehaviors = (clone $baseQuery)
            ->where('type', 'Slip')
            ->orderBy('sort')
            ->get()
            ->toArray();

        $this->noWayBehaviors = (clone $baseQuery)
            ->where('type', 'No Way')
            ->orderBy('sort')
            ->get()
            ->toArray();

    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['Positive', 'Slip', 'No Way'])) {
            return;
        }

        $this->activeTab = $tab;
        $this->recentAward = null;

    }

    public function setHistoryTypeFilter(?string $filter): void
    {
        if ($this->historyTypeFilter === $filter) {
            $filter = null;
        }

        if (! in_array($filter, [null, 'Positive', 'Slip', 'No Way', 'Consequences'], true)) {
            return;
        }

        $this->historyTypeFilter = $filter;
        $this->historyPerPage = 20;
        $this->loadSessionDisciplines();
    }

    public function setHistoryTab(string $tab): void
    {
        if (! in_array($tab, ['behavior', 'tasks'], true)) {
            return;
        }

        $this->historyTab = $tab;

        if ($tab === 'tasks') {
            $this->taskHistoryPerPage = 10;
            $this->loadTaskHistory();
        }
    }

    public function setSubjectFilter(mixed $subjectId = null): void
    {
        if ($this->userRole === 'teacher') {
            return;
        }

        $subjectId = blank($subjectId) ? null : (int) $subjectId;
        $allowedSubjectIds = collect($this->subjectFilters)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        if ($subjectId !== null && ! in_array($subjectId, $allowedSubjectIds, true)) {
            return;
        }

        $this->selectedSubjectId = $subjectId;
        $this->historyPerPage = 20;
        $this->taskHistoryPerPage = 10;
        $this->recentAward = null;
        $this->loadSessionDisciplines();
        $this->loadBehaviorCounts();
        $this->loadPunishmentCounts();
        $this->loadTaskHistoryIfActive();
        $this->pointsLabStateSignature = $this->pointsLabStateSignature();
    }

    public function loadMoreTaskHistory(): void
    {
        $this->taskHistoryPerPage += 10;
        $this->loadTaskHistory();
    }

    public function applyHistoryDateRange(?string $startDate = null, ?string $endDate = null): void
    {
        $this->historyStartDate = $this->normalizeHistoryDate($startDate);
        $this->historyEndDate = $this->normalizeHistoryDate($endDate);
        $this->historyPerPage = 20;
        $this->loadSessionDisciplines();
    }

    public function clearHistoryDateRange(): void
    {
        $this->historyStartDate = null;
        $this->historyEndDate = null;
        $this->historyPerPage = 20;
        $this->loadSessionDisciplines();
    }

    #[Computed]
    public function historyFilters(): array
    {
        return [
            ['label' => 'Good', 'value' => 'Positive', 'color' => 'success', 'icon' => 'tabler-thumb-up'],
            ['label' => 'Slips', 'value' => 'Slip', 'color' => 'warning', 'icon' => 'tabler-alert-circle'],
            ['label' => 'Red Flag', 'value' => 'No Way', 'color' => 'danger', 'icon' => 'tabler-alert-triangle'],
            ['label' => 'Consequences', 'value' => 'Consequences', 'color' => 'info', 'icon' => 'tabler-clipboard-list'],
        ];
    }

    #[Computed]
    public function historyDateValue(): string
    {
        if (! $this->historyStartDate || ! $this->historyEndDate) {
            return '';
        }

        return \Carbon\Carbon::parse($this->historyStartDate)->format('m/d/Y')
            .' - '
            .\Carbon\Carbon::parse($this->historyEndDate)->format('m/d/Y');
    }

    #[Computed]
    public function behaviorModalView(): array
    {
        $type = $this->selectedBehaviorType ?? $this->pendingType ?? $this->activeTab;
        $selectedBehaviorId = (int) ($this->selectedBehaviorId ?? 0);
        $selected = [];

        $behaviors = array_map(function (array $behavior) use ($selectedBehaviorId, &$selected): array {
            $behavior['id'] = (int) ($behavior['id'] ?? 0);
            $behavior['icon_url'] = $this->assetPath($behavior['icon_path'] ?? null);

            if ($selectedBehaviorId > 0 && $behavior['id'] === $selectedBehaviorId) {
                $selected = $behavior;
            }

            return $behavior;
        }, array_values($this->modalBehaviors));

        return [
            'type' => $type,
            'color' => self::behaviorTypeColor($type),
            'behaviors' => $behaviors,
            'selected' => $selected,
            'selected_id' => $selectedBehaviorId,
            'point_options' => self::behaviorPointOptions($type),
        ];
    }

    public function assetPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalizedPath = ltrim($path, '/');

        if (Str::startsWith($normalizedPath, ['http://', 'https://'])) {
            return $normalizedPath;
        }

        return asset(ltrim(
            Str::startsWith($normalizedPath, 'public/')
                ? Str::after($normalizedPath, 'public/')
                : $normalizedPath,
            '/'
        ));
    }

    public function loadMoreHistory(): void
    {
        $this->historyPerPage += 20;
        $this->loadSessionDisciplines();
    }

    protected function loadSubjectFilters(): void
    {
        $this->subjectFilters = [];

        if ($this->userRole === 'teacher') {
            $teacherSubjectClass = $this->teacherSubjectClassesId
                ? TeacherSubjectClass::query()->find($this->teacherSubjectClassesId)
                : null;

            if ($teacherSubjectClass) {
                $this->selectedSubjectId = (int) $teacherSubjectClass->subject_id;
                $this->subjectFilters = [[
                    'id' => (int) $teacherSubjectClass->subject_id,
                    'title' => $teacherSubjectClass->subject_name ?: 'Selected subject',
                ]];
            }

            return;
        }

        if (! in_array($this->userRole, ['student', 'parent'], true)) {
            return;
        }

        $query = StudentsSubject::query()
            ->from('students_subjects as ss')
            ->join('grade_level_subjects as gls', 'gls.id', '=', 'ss.grade_level_subject_id')
            ->join('subjects as subjects', 'subjects.id', '=', 'gls.subject_id')
            ->where('ss.student_id', $this->studentId)
            ->where('ss.status', 'active')
            ->where('ss.academic_year_id', $this->academicYearId)
            ->where('gls.status', 'active')
            ->where('gls.academic_year_id', $this->academicYearId);

        self::$subjectsHasActiveColumn ??= Schema::hasColumn('subjects', 'active');

        if (self::$subjectsHasActiveColumn) {
            $query->where(function ($query): void {
                $query->whereNull('subjects.active')
                    ->orWhere('subjects.active', true);
            });
        }

        $this->subjectFilters = $query
            ->orderBy('subjects.title')
            ->get([
                'subjects.id',
                'subjects.title',
            ])
            ->map(fn ($subject): array => [
                'id' => (int) $subject->id,
                'title' => (string) $subject->title,
            ])
            ->unique('id')
            ->values()
            ->toArray();

        if ($this->selectedSubjectId !== null && ! collect($this->subjectFilters)->contains('id', $this->selectedSubjectId)) {
            $this->selectedSubjectId = null;
        }
    }

    protected static function behaviorTypeColor(?string $type): string
    {
        return match ($type) {
            'Positive' => 'success',
            'Slip' => 'warning',
            'No Way' => 'danger',
            default => 'primary',
        };
    }

    protected static function behaviorPointOptions(?string $type): array
    {
        return match ($type) {
            'Slip' => range(1, 5),
            'No Way' => range(5, 10),
            default => range(1, 10),
        };
    }

    protected function normalizeHistoryDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function resetBehaviorModalState(): void
    {
        $this->resetErrorBag();

        $this->pendingBehaviorId = null;
        $this->pendingBehaviorTitle = '';
        $this->pendingBehaviorPoints = 0;
        $this->pendingBehaviorType = 'Positive';
        $this->pendingType = null;
        $this->descriptionInput = '';
        $this->selectedBehaviorId = null;
        $this->modalBehaviors = [];
        $this->pointsInput = null;
        $this->punishmentAgreements = [];
        $this->selectedPunishmentAgreementId = null;
        $this->punishmentTypeId = null;
        $this->punishmentNote = null;
    }

    public function clearRecentAward(): void
    {
        $this->recentAward = null;
    }

    // open popup in studend card in parent account

    public function openAddBehaviorModal(int $studentId, string $type): void
    {
        // اسمح للـ teacher/parent فقط
        if ($this->userRole !== 'parent') {
            return;
        }

        // حدّث الطالب (لأن المودال موجود داخل نفس component)
        $this->studentId = $studentId;
        $this->studentName = Student::whereKey($studentId)->value('first_name') ?? '';

        // تأكد النوع صحيح
        if (! in_array($type, ['Positive', 'Slip', 'No Way'])) {
            $type = 'Positive';
        }

        // Reset modal state
        $this->resetBehaviorModalState();
        $this->recentAward = null;
        $this->activeTab = $type;

        // هذا هو النوع اللي هنفتح عليه popup
        $this->pendingType = $type;
        $this->pendingBehaviorType = $type;

        // حمّل agreements لو Slip/NoWay
        $this->loadPunishmentsForModal($this->pendingType);

        $this->loadModalBehaviors($this->pendingType);

        // افتح المودال
        $this->dispatch('open-desc-modal');
    }

    public function startBehavior(int $behaviorId): void
    {
        // الطالب لا يضيف سلوك
        if (($this->userRole !== 'teacher') && ($this->userRole !== 'parent')) {
            return;
        }

        if (! $this->parentLifecycleAllowsBehavior()) {
            return;
        }

        $cardBehavior = RewardDisciplinePoint::query()
            ->whereKey($behaviorId)
            ->where(function ($query) {
                $query->whereNull('student_id')
                    ->orWhere('student_id', $this->studentId);
            })
            ->where('status', 'active')
            ->firstOrFail();

        if ((int) $cardBehavior->teacher_desc === 0) {
            if ($this->userRole === 'parent' && ! $this->syncResolvedTeacherSubjectClassId()) {
                $this->dispatch('toast', type: 'danger', message: ParentBehaviorSubjectResolver::ERROR_MESSAGE);

                return;
            }

            $this->activeTab = $cardBehavior->type;
            $this->applyBehaviorInternal($cardBehavior, null, $cardBehavior->points);

            // مهم جدًا لتحديث الدائرة (counts) من غير refresh
            $this->loadBehaviorCounts();

            return;
        }

        // teacher_desc = 1 => Popup mode
        if ($this->userRole === 'parent') {
            if (! $this->syncResolvedTeacherSubjectClassId()) {
                $this->dispatch('toast', type: 'danger', message: ParentBehaviorSubjectResolver::ERROR_MESSAGE);

                return;
            }

            $this->activeTab = $cardBehavior->type;
            $this->dispatch(
                'openAddBehaviorModal',
                studentId: $this->studentId,
                type: $cardBehavior->type,
                teacherSubjectClassesId: $this->teacherSubjectClassesId,
                academicYearId: $this->academicYearId
            )->to(\App\Livewire\Parent\BehaviorModal::class);

            return;
        }

        if ($this->userRole === 'teacher') {
            $this->activeTab = $cardBehavior->type;
            $this->dispatch(
                'openAddBehaviorModal',
                studentId: $this->studentId,
                type: $cardBehavior->type,
                teacherSubjectClassesId: $this->teacherSubjectClassesId,
                academicYearId: $this->academicYearId,
                categoryTitle: $cardBehavior->title
            )->to(BehaviorModal::class);

            return;
        }

        $this->resetBehaviorModalState();
        $this->recentAward = null;
        $this->activeTab = $cardBehavior->type;
        $this->pendingType = $cardBehavior->type;     // Positive / Slip / No Way
        $this->pendingBehaviorType = $cardBehavior->type;
        $this->pendingBehaviorId = (int) $cardBehavior->id;
        $this->pendingBehaviorTitle = $cardBehavior->title;
        $this->pendingBehaviorPoints = (int) $cardBehavior->points;

        $this->loadPunishmentsForModal($this->pendingType);

        $this->loadModalBehaviors($this->pendingType, (int) $cardBehavior->id);

        $this->dispatch('open-desc-modal');
    }

    protected function loadModalBehaviors(string $type, ?int $preferredBehaviorId = null): void
    {
        $behaviors = RewardDisciplinePoint::query()
            ->where(function ($query) {
                $query->whereNull('student_id')
                    ->orWhere('student_id', $this->studentId);
            })
            ->where('status', 'active')
            ->where('type', $type)
            ->where('teacher_desc', 0)
            ->orderByRaw('COALESCE(sort, 999999) asc')
            ->orderBy('id')
            ->get(['id', 'title', 'type', 'discipline_icon_path', 'teacher_desc', 'selected', 'points']);

        $this->modalBehaviors = $behaviors->map(fn ($b) => [
            'id' => $b->id,
            'title' => $b->title,
            'type' => $b->type,
            'icon_path' => $b->discipline_icon_path,
            'teacher_desc' => $b->teacher_desc,
            'selected' => $b->selected,
            'points' => (int) $b->points,
        ])->toArray();

        $this->selectedBehaviorId = null;
        $this->pointsInput = null;
    }

    public function updatedSelectedBehaviorId($value): void
    {
        $this->resetErrorBag('selectedBehaviorId');

        if (! $value) {
            $this->pointsInput = null;

            return;
        }

        foreach ($this->modalBehaviors as $behavior) {
            if ((int) $behavior['id'] === (int) $value) {
                $this->pointsInput = max(1, (int) ($behavior['points'] ?? 1));

                return;
            }
        }

        $this->selectedBehaviorId = null;
        $this->pointsInput = null;
    }

    public function selectModalBehavior(int $behaviorId): void
    {
        $this->selectedBehaviorId = $behaviorId;
        $this->updatedSelectedBehaviorId($behaviorId);
    }

    public function getSelectedBehaviorTypeProperty(): ?string
    {
        if (! $this->selectedBehaviorId) {
            return $this->pendingType;
        }

        foreach ($this->modalBehaviors as $b) {
            if ((int) $b['id'] === (int) $this->selectedBehaviorId) {
                return $b['type'] ?? $this->pendingType;
            }
        }

        return $this->pendingType;
    }

    protected function applyBehaviorInternal(
        RewardDisciplinePoint $behavior,
        ?string $description = null,
        ?int $pointsMagnitude = null
    ) {
        if ($pointsMagnitude !== null && $pointsMagnitude < 1) {
            throw ValidationException::withMessages([
                'pointsInput' => 'Points must be at least 1.',
            ]);
        }

        $pointsMagnitude = (int) ($pointsMagnitude ?? $behavior->points);
        $isPositive = ($behavior->type === 'Positive');
        $sessionDiscipline = Student_Session_Discipline::create([
            'title' => $behavior->title,
            'discipline_icon_id' => $behavior->discipline_icon_id,
            'discipline_icon_path' => $behavior->discipline_icon_path,
            'student_reward_discipline_id' => $behavior->id,
            'class_session_id' => $this->classSessionId ?? null,
            'teacher_subject_classes_id' => $this->teacherSubjectClassesId,
            'student_id' => $this->studentId,
            'points' => $pointsMagnitude, // ✅ موجب فقط
            'description' => $description,
            'type' => $behavior->type,
        ]);

        $pointsDelta = $isPositive ? $pointsMagnitude : -$pointsMagnitude;
        $teacherSubjectClass = $this->resolveTeacherSubjectClassForStudent();
        $subject_id = $teacherSubjectClass?->subject_id;

        app(RewardProgressionService::class)->applyPointDelta(
            studentId: $this->studentId,
            pointsDelta: $pointsDelta,
            sourceType: 'discipline',
            sourceId: $sessionDiscipline->id,
            grantedBy: (int) Auth::id(),
            academicYearId: $this->academicYearId ?? AcademicYear::currentId(),
            subjectId: $subject_id,
            comment: $description
        );

        // ---- تجميع الطالب (زي منطقك الحالي) ----
        // Toast uses actual saved magnitude
        $this->recentAward = [
            'id' => now()->timestamp,
            'student_name' => $this->studentName,
            'title' => $behavior->title,
            'points' => $pointsMagnitude,
            'type' => $behavior->type,
            'icon_path' => $behavior->discipline_icon_path,
        ];
        $this->activeTab = $behavior->type;
        $this->loadSessionDisciplines();

        $this->loadBehaviorCounts();
        $this->loadPunishmentCounts();
        $this->resetPage('historyPage');
        $this->dispatch('reward-points:updated');

        return $sessionDiscipline;

    }

    public function confirmBehaviorWithDescription(): void
    {
        if (! in_array($this->pendingType, ['Positive', 'Slip', 'No Way'])) {
            $this->addError('selectedBehaviorId', 'Open the behavior popup again before saving.');

            return;
        }

        if (! $this->selectedBehaviorId) {
            $this->addError('selectedBehaviorId', 'Select a behavior first.');

            return;
        }

        if (! $this->parentLifecycleAllowsBehavior()) {
            return;
        }

        $this->validate([
            'pointsInput' => ['required', 'integer', 'min:1'],
        ]);

        if ($this->userRole === 'parent' && ! $this->syncResolvedTeacherSubjectClassId()) {
            $this->addError('selectedBehaviorId', ParentBehaviorSubjectResolver::ERROR_MESSAGE);

            return;
        }

        $behavior = RewardDisciplinePoint::query()
            ->whereKey($this->selectedBehaviorId)
            ->where(function ($query) {
                $query->whereNull('student_id')
                    ->orWhere('student_id', $this->studentId);
            })
            ->where('status', 'active')
            ->where('type', $this->pendingType)
            ->where('teacher_desc', 0)
            ->first();

        if (! $behavior) {
            $this->selectedBehaviorId = null;
            $this->pointsInput = null;
            $this->addError('selectedBehaviorId', 'Select a behavior first.');

            return;
        }

        // 1) Prepare punishment + validate BEFORE saving behavior
        $agreement = null;

        if ($this->selectedPunishmentAgreementId && in_array($this->pendingType, ['Slip', 'No Way'])) {
            $agreement = PunishmentAgreement::query()
                ->whereKey($this->selectedPunishmentAgreementId)
                ->where('student_id', $this->studentId)
                ->where('punishment_type_id', $this->punishmentTypeId)
                ->where('status', 'active')
                ->first();

            if ($agreement) {
                $isCustomized = Str::lower(trim($agreement->title)) === 'customized';

                if ($isCustomized && empty(trim((string) $this->descriptionInput))) {
                    $this->addError('descriptionInput', 'Description is required for Customized Agreement.');

                    return; // ✅ stops everything (behavior NOT saved)
                }
            }
        }
        DB::transaction(function () use ($behavior, $agreement) {

            // 2) Save behavior
            // $this->applyBehaviorInternal(
            //     $behavior,
            //     $this->descriptionInput ?: null,
            //     $this->pointsInput
            // );
            $sessionDiscipline = $this->applyBehaviorInternal(
                $behavior,
                $this->descriptionInput ?: null,
                $this->pointsInput
            );

            // 3) Save punishment (optional)
            if ($agreement) {
                $teacherSubjectClass = $this->resolveTeacherSubjectClassForStudent();
                $subject_id = $teacherSubjectClass?->subject_id;

                Student_punishment::create([
                    'student_id' => $this->studentId,
                    'description' => $this->descriptionInput ?: null, // نفس textarea
                    'punishment_agreement_id' => $agreement->id,
                    'subject_id' => $subject_id,
                    'student_session_discipline_id' => $sessionDiscipline->id,
                    'teacher_subject_class' => $teacherSubjectClass?->id,
                    'created_by_id' => Auth::id(),
                    'created_at' => now()->toDateString(),
                ]);
            }
        });

        $this->loadSessionDisciplines();
        $this->loadBehaviorCounts();
        $this->loadPunishmentCounts();
        $this->resetPage('historyPage');
        $this->dispatch('reward-points:updated');

        $this->resetBehaviorModalState();

        $this->dispatch('close-desc-modal');
    }

    public function cancelBehaviorDescription(): void
    {
        $this->resetBehaviorModalState();
        $this->dispatch('close-desc-modal');

    }

    protected function applyHistoryFilters($query)
    {
        return $query
            ->when($this->historyTypeFilter === 'Positive', fn ($q) => $q->where('ssd.type', 'Positive'))
            ->when($this->historyTypeFilter === 'Slip', fn ($q) => $q->where('ssd.type', 'Slip'))
            ->when($this->historyTypeFilter === 'No Way', fn ($q) => $q->where('ssd.type', 'No Way'))
            ->when($this->historyTypeFilter === 'Consequences', function ($q) {
                $q->whereIn('ssd.type', ['Slip', 'No Way'])
                    ->whereNotNull('sp.id');
            })
            ->when($this->historyStartDate, fn ($q) => $q->whereDate('ssd.created_at', '>=', $this->historyStartDate))
            ->when($this->historyEndDate, fn ($q) => $q->whereDate('ssd.created_at', '<=', $this->historyEndDate));
    }

    protected function applyHistoryDateFilters($query)
    {
        return $query
            ->when($this->historyStartDate, fn ($q) => $q->whereDate('ssd.created_at', '>=', $this->historyStartDate))
            ->when($this->historyEndDate, fn ($q) => $q->whereDate('ssd.created_at', '<=', $this->historyEndDate));
    }

    protected function applyPointsLabDisciplineScope($query, string $tscAlias = 'tsc')
    {
        if ($this->userRole === 'teacher') {
            return $query
                ->where($tscAlias.'.user_teacher_coteacher_id', Auth::id())
                ->where($tscAlias.'.id', $this->teacherSubjectClassesId);
        }

        if (in_array($this->userRole, ['student', 'parent'], true) && $this->selectedSubjectId) {
            return $query->where($tscAlias.'.subject_id', $this->selectedSubjectId);
        }

        return $query;
    }

    protected function applyTaskHistoryScope($query)
    {
        if ($this->userRole === 'teacher') {
            return $query->where('cs.teacher_subject_classes_id', $this->teacherSubjectClassesId);
        }

        if (in_array($this->userRole, ['student', 'parent'], true) && $this->selectedSubjectId) {
            return $query->where('cs.subject_id', $this->selectedSubjectId);
        }

        return $query;
    }

    protected function historyFilterCountsQuery()
    {
        $query = DB::table('student_session_discipline as ssd')
            ->leftJoin('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
            ->leftJoin('student_punishments as sp', 'sp.student_session_discipline_id', '=', 'ssd.id')
            ->where('ssd.student_id', $this->studentId);

        $this->applyPointsLabDisciplineScope($query);

        return $this->applyHistoryDateFilters($query);
    }

    protected function loadHistoryFilterCounts(): void
    {
        $summary = $this->historyFilterCountsQuery()
            ->selectRaw("COALESCE(SUM(CASE WHEN ssd.type = 'Positive' THEN 1 ELSE 0 END), 0) as positive_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN ssd.type = 'Slip' THEN 1 ELSE 0 END), 0) as slip_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN ssd.type = 'No Way' THEN 1 ELSE 0 END), 0) as no_way_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN ssd.type IN ('Slip', 'No Way') AND sp.id IS NOT NULL THEN 1 ELSE 0 END), 0) as consequence_count")
            ->first();

        $this->historyFilterCounts = [
            'Positive' => (int) ($summary->positive_count ?? 0),
            'Slip' => (int) ($summary->slip_count ?? 0),
            'No Way' => (int) ($summary->no_way_count ?? 0),
            'Consequences' => (int) ($summary->consequence_count ?? 0),
        ];
    }

    protected function loadSessionDisciplines(): void
    {
        $this->total_post_point = 0;
        $this->total_negative_point = 0;
        $this->loadHistoryFilterCounts();

        if (in_array($this->userRole, ['student', 'parent'])) {

            $historyQuery = $this->applyHistoryFilters(DB::table('student_session_discipline as ssd')
                ->leftJoin('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
                ->leftJoin('student_punishments as sp', 'sp.student_session_discipline_id', '=', 'ssd.id')
                ->leftJoin('punishment_agreements as pa', 'pa.id', '=', 'sp.punishment_agreement_id')
                ->where('ssd.student_id', $this->studentId));
            $this->applyPointsLabDisciplineScope($historyQuery);

            $this->historyTotal = (clone $historyQuery)->count('ssd.id');

            $this->sessionDisciplines = $historyQuery
                ->orderByDesc('ssd.created_at')
                ->select([
                    'ssd.*',
                    DB::raw('tsc.subject_name as subject_name'),   // ✅ اسم المادة
                    DB::raw('tsc.class_name as class_name'),       // (اختياري) اسم الكلاس
                    DB::raw('pa.title as agreement_title'),
                    DB::raw('sp.description as punishment_description'),
                ])
                ->limit($this->historyPerPage)
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();

            $pointsQuery = DB::table('student_session_discipline as ssd')
                ->leftJoin('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
                ->where('ssd.student_id', $this->studentId);
            $this->applyPointsLabDisciplineScope($pointsQuery);

            $pointsSummary = $pointsQuery
                ->selectRaw("COALESCE(SUM(CASE WHEN ssd.type = 'Positive' THEN ssd.points ELSE 0 END), 0) as positive_points")
                ->selectRaw("COALESCE(SUM(CASE WHEN ssd.type != 'Positive' THEN ssd.points ELSE 0 END), 0) as negative_points")
                ->first();

            $this->total_post_point = (int) ($pointsSummary->positive_points ?? 0);
            $this->total_negative_point = (int) ($pointsSummary->negative_points ?? 0);
        }

        if ($this->userRole == 'teacher') {
            $historyQuery = $this->applyHistoryFilters(DB::table('student_session_discipline as ssd')
                ->join('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
                ->leftJoin('student_punishments as sp', 'sp.student_session_discipline_id', '=', 'ssd.id')
                ->leftJoin('punishment_agreements as pa', 'pa.id', '=', 'sp.punishment_agreement_id')
                ->where('ssd.student_id', $this->studentId));
            $this->applyPointsLabDisciplineScope($historyQuery);

            $this->historyTotal = (clone $historyQuery)->count('ssd.id');

            $this->sessionDisciplines = $historyQuery
                ->orderByDesc('ssd.created_at')
                ->select([
                    'ssd.*',
                    DB::raw('tsc.subject_name as subject_name'),
                    DB::raw('pa.title as agreement_title'),
                    DB::raw('sp.description as punishment_description'),
                ])
                ->limit($this->historyPerPage)
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();

            $pointsQuery = DB::table('student_session_discipline as ssd')
                ->join('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
                ->where('ssd.student_id', $this->studentId);
            $this->applyPointsLabDisciplineScope($pointsQuery);

            $pointsSummary = $pointsQuery
                ->selectRaw("COALESCE(SUM(CASE WHEN ssd.type = 'Positive' THEN ssd.points ELSE 0 END), 0) as positive_points")
                ->selectRaw("COALESCE(SUM(CASE WHEN ssd.type != 'Positive' THEN ssd.points ELSE 0 END), 0) as negative_points")
                ->first();

            $this->total_post_point = (int) ($pointsSummary->positive_points ?? 0);
            $this->total_negative_point = (int) ($pointsSummary->negative_points ?? 0);
        }

    }

    // تحميل العقوبات حسب نوع الـ behavior
    protected function loadPunishmentsForModal(string $behaviorType): void
    {
        $this->punishmentAgreements = [];
        $this->selectedPunishmentAgreementId = null;
        $this->punishmentTypeId = null;

        // Only for Slip / No Way
        if (! in_array($behaviorType, ['Slip', 'No Way'])) {
            return;
        }

        $wantedTitle = $behaviorType === 'Slip' ? 'Minor Slip' : 'Serious Action';

        $type = PunishmentType::query()
            ->where('title', $wantedTitle)
            ->where('active', 1)
            ->first();

        if (! $type) {
            return;
        }

        $this->punishmentTypeId = (int) $type->id;

        // Ensure "Customized" exists for this student + type (safe)
        PunishmentAgreement::firstOrCreate(
            [
                'student_id' => $this->studentId,
                'punishment_type_id' => $this->punishmentTypeId,
                'title' => 'Customized',
            ],
            [
                'status' => 'active',
            ]
        );

        $this->punishmentAgreements = PunishmentAgreement::query()
            ->where('student_id', $this->studentId)
            ->where('punishment_type_id', $this->punishmentTypeId)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get(['id', 'title'])
            ->map(fn ($a) => ['id' => (int) $a->id, 'title' => $a->title])
            ->toArray();
    }

    public function selectPunishment(int $agreementId): void
    {
        $this->selectedPunishmentAgreementId = $agreementId;
        // تنظيف error قديم لو كان Customized
        $this->resetErrorBag('descriptionInput');
        $this->resetErrorBag('punishmentNote');
    }

    public function clearPunishmentSelection(): void
    {
        $this->selectedPunishmentAgreementId = null;

        // لو عندك textarea خاص بالعقوبة
        if (property_exists($this, 'punishmentNote')) {
            $this->punishmentNote = null;
        }

        // لو كان طالع error بسبب Customized
        $this->resetErrorBag('descriptionInput');
        $this->resetErrorBag('punishmentNote');
    }

    // احسب عدد مرات كل behavior للطالب
    protected function loadBehaviorCounts(): void
    {
        $query = DB::table('student_session_discipline as ssd')
            ->leftJoin('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
            ->where('ssd.student_id', $this->studentId);
        $this->applyPointsLabDisciplineScope($query);

        $this->behaviorCounts = $query
            ->selectRaw('student_reward_discipline_id, COUNT(*) as total')
            ->groupBy('student_reward_discipline_id')
            ->pluck('total', 'student_reward_discipline_id')
            ->toArray();

    }

    public function openBehaviorHistory(int $behaviorId): void
    {

        $this->loadBehaviorCounts();

        $this->historyBehaviorId = $behaviorId;
        $this->behaviorHistoryPerPage = 20;
        $this->loadBehaviorHistory();

        $this->showBehaviorHistoryModal = true;
        $this->dispatch('open-behavior-history-modal');
    }

    protected function loadBehaviorHistory(): void
    {
        if (! $this->historyBehaviorId) {
            $this->behaviorHistory = [];
            $this->behaviorHistoryTotal = 0;

            return;
        }

        $query = DB::table('student_session_discipline as ssd')
            ->leftJoin('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
            ->leftJoin('student_punishments as sp', 'sp.student_session_discipline_id', '=', 'ssd.id')
            ->leftJoin('punishment_agreements as pa', 'pa.id', '=', 'sp.punishment_agreement_id')
            ->where('ssd.student_id', $this->studentId)
            ->where('ssd.student_reward_discipline_id', $this->historyBehaviorId);
        $this->applyPointsLabDisciplineScope($query);

        $this->behaviorHistoryTotal = (clone $query)->count('ssd.id');

        $this->behaviorHistory = $query
            ->orderByDesc('ssd.created_at')
            ->select([
                'ssd.*',
                DB::raw('tsc.subject_name as subject_name'),
                DB::raw('pa.title as agreement_title'),
                DB::raw('sp.description as punishment_description'),

            ])
            ->limit($this->behaviorHistoryPerPage)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->toArray();
    }

    public function loadMoreBehaviorHistory(): void
    {
        $this->behaviorHistoryPerPage += 20;
        $this->loadBehaviorHistory();
    }

    public function closeBehaviorHistory(): void
    {
        $this->showBehaviorHistoryModal = false;
        $this->historyBehaviorId = null;
        $this->behaviorHistory = [];
        $this->behaviorHistoryPerPage = 20;
        $this->behaviorHistoryTotal = 0;
        $this->dispatch('close-behavior-history-modal');

    }

    protected function loadPunishmentCounts(): void
    {
        // IDs حسب العناوين (أفضل من الاعتماد على id ثابت)
        $minorSlipTypeId = PunishmentType::query()
            ->where('title', 'Minor Slip')
            ->first()?->id;

        $seriousActionTypeId = PunishmentType::query()
            ->where('title', 'Serious Action')
            ->first()?->id;

        // Slip count
        $slipQuery = DB::table('student_punishments as sp')
            ->join('punishment_agreements as pa', 'pa.id', '=', 'sp.punishment_agreement_id')
            ->join('student_session_discipline as ssd', 'ssd.id', '=', 'sp.student_session_discipline_id')
            ->leftJoin('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
            ->where('sp.student_id', $this->studentId)
            ->where('ssd.type', 'Slip')
            ->when($minorSlipTypeId, fn ($q) => $q->where('pa.punishment_type_id', $minorSlipTypeId));
        $this->applyPointsLabDisciplineScope($slipQuery);
        $this->slipPunishmentsCount = $slipQuery->count();

        // No Way count
        $noWayQuery = DB::table('student_punishments as sp')
            ->join('punishment_agreements as pa', 'pa.id', '=', 'sp.punishment_agreement_id')
            ->join('student_session_discipline as ssd', 'ssd.id', '=', 'sp.student_session_discipline_id')
            ->leftJoin('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
            ->where('sp.student_id', $this->studentId)
            ->where('ssd.type', 'No Way')
            ->when($seriousActionTypeId, fn ($q) => $q->where('pa.punishment_type_id', $seriousActionTypeId));
        $this->applyPointsLabDisciplineScope($noWayQuery);
        $this->noWayPunishmentsCount = $noWayQuery->count();
    }

    public function openPunishmentHistory(string $type): void
    {
        if (! in_array($type, ['Slip', 'No Way'])) {
            return;
        }

        $this->punishmentHistoryType = $type;
        $this->punishmentHistoryPerPage = 20;
        $this->loadPunishmentHistory();

        $this->showPunishmentHistoryModal = true;
        $this->dispatch('open-punishment-history-modal');
    }

    protected function loadPunishmentHistory(): void
    {
        if (! in_array($this->punishmentHistoryType, ['Slip', 'No Way'], true)) {
            $this->punishmentHistory = [];
            $this->punishmentHistoryTotal = 0;

            return;
        }

        $wantedTitle = $this->punishmentHistoryType === 'Slip' ? 'Minor Slip' : 'Serious Action';
        $typeId = PunishmentType::query()
            ->where('title', $wantedTitle)
            ->first()?->id;

        $query = DB::table('student_session_discipline as ssd')
            ->join('student_punishments as sp', 'sp.student_session_discipline_id', '=', 'ssd.id')
            ->join('punishment_agreements as pa', 'pa.id', '=', 'sp.punishment_agreement_id')
            ->leftJoin('punishment_types as pt', 'pt.id', '=', 'pa.punishment_type_id')
            ->leftJoin('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
            ->where('ssd.student_id', $this->studentId)
            ->where('ssd.type', $this->punishmentHistoryType)
            ->when($typeId, fn ($q) => $q->where('pa.punishment_type_id', $typeId));
        $this->applyPointsLabDisciplineScope($query);

        $this->punishmentHistoryTotal = (clone $query)->count('ssd.id');

        $this->punishmentHistory = $query
            ->orderByDesc('ssd.created_at')
            ->select([
                'ssd.*',
                DB::raw('tsc.subject_name as subject_name'),
                DB::raw('pa.title as agreement_title'),
                DB::raw('sp.description as punishment_description'),
                DB::raw('pt.title as punishment_type_title'),
            ])
            ->limit($this->punishmentHistoryPerPage)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->toArray();
    }

    public function loadMorePunishmentHistory(): void
    {
        $this->punishmentHistoryPerPage += 20;
        $this->loadPunishmentHistory();
    }

    public function closePunishmentHistory(): void
    {
        $this->showPunishmentHistoryModal = false;
        $this->punishmentHistoryType = null;
        $this->punishmentHistory = [];
        $this->punishmentHistoryPerPage = 20;
        $this->punishmentHistoryTotal = 0;
        $this->dispatch('close-punishment-history-modal');
    }

    protected function sessionDisciplinesQuery()
    {
        return DB::table('student_session_discipline as ssd')
            ->leftJoin('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
            ->leftJoin('student_punishments as sp', 'sp.student_session_discipline_id', '=', 'ssd.id')
            ->leftJoin('punishment_agreements as pa', 'pa.id', '=', 'sp.punishment_agreement_id')
            ->where('ssd.student_id', $this->studentId)
            ->orderByDesc('ssd.created_at')
            ->select([
                'ssd.*',
                DB::raw('tsc.subject_name as subject_name'),
                DB::raw('pa.title as agreement_title'),
                DB::raw('sp.description as punishment_description'),
            ]);
    }

    protected function loadTaskHistoryIfActive(): void
    {
        if ($this->historyTab !== 'tasks') {
            $this->taskHistoryRows = [];
            $this->taskHistoryTotal = 0;
            $this->taskHistorySummary = ['completed' => 0, 'points' => 0];

            return;
        }

        $this->loadTaskHistory();
    }

    protected function taskHistoryBaseQuery()
    {
        $query = DB::table('session_task_student as sts')
            ->join('session_tasks as st', 'st.id', '=', 'sts.session_task_id')
            ->join('class_sessions as cs', 'cs.id', '=', 'st.class_session_id')
            ->leftJoin('subjects as subjects', 'subjects.id', '=', 'cs.subject_id')
            ->where('sts.student_id', $this->studentId)
            ->where('sts.status', SessionTaskStudent::STATUS_COMPLETED);

        return $this->applyTaskHistoryScope($query);
    }

    protected function loadTaskHistory(): void
    {
        $baseQuery = $this->taskHistoryBaseQuery();
        $summary = (clone $baseQuery)
            ->selectRaw('COUNT(sts.id) as completed_count')
            ->selectRaw('COALESCE(SUM(sts.student_points), 0) as awarded_points')
            ->first();

        $this->taskHistoryTotal = (int) ($summary->completed_count ?? 0);
        $this->taskHistorySummary = [
            'completed' => $this->taskHistoryTotal,
            'points' => (int) ($summary->awarded_points ?? 0),
        ];

        $rows = (clone $baseQuery)
            ->orderByDesc('sts.approved_at')
            ->orderByDesc('sts.id')
            ->limit($this->taskHistoryPerPage)
            ->get([
                'sts.id as pivot_id',
                'sts.session_task_id',
                'sts.student_points',
                'sts.approval_source',
                'sts.approved_by_id',
                'sts.approved_at',
                'st.title as task_title',
                'cs.subject_id',
                'subjects.title as subject_title',
            ]);

        $events = StudentTaskApprovalEvent::query()
            ->whereIn('session_task_student_id', $rows->pluck('pivot_id')->all())
            ->whereIn('event_type', [
                StudentTaskApprovalEvent::TYPE_APPROVED,
                StudentTaskApprovalEvent::TYPE_COMPLETED_WITH_PIN,
                StudentTaskApprovalEvent::TYPE_COMPLETED_BY_PARENT,
                StudentTaskApprovalEvent::TYPE_TRUSTED_AUTO_APPROVED,
            ])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('session_task_student_id')
            ->map(fn ($events) => $events->first());

        $this->taskHistoryRows = $rows
            ->map(function ($row) use ($events): array {
                $event = $events->get($row->pivot_id);
                $source = $event?->source ?: $row->approval_source;

                return [
                    'pivot_id' => (int) $row->pivot_id,
                    'title' => (string) ($row->task_title ?: 'Task'),
                    'subject' => (string) ($row->subject_title ?: 'Subject'),
                    'points' => (int) ($event?->points ?? $row->student_points ?? 0),
                    'source' => $this->taskHistorySourceLabel((string) $source),
                    'date' => $this->taskHistoryDateLabel($event?->created_at ?: $row->approved_at),
                ];
            })
            ->toArray();
    }

    protected function taskHistoryDateLabel(mixed $date): string
    {
        if (! $date) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($date)->format('d M');
        } catch (\Throwable) {
            return '';
        }
    }

    protected function taskHistorySourceLabel(string $source): string
    {
        return match ($source) {
            SessionTaskStudent::SOURCE_PARENT_APPROVAL,
            SessionTaskStudent::SOURCE_PARENT_DIRECT_COMPLETION => 'Parent',
            SessionTaskStudent::SOURCE_TEACHER_APPROVAL => 'Teacher',
            SessionTaskStudent::SOURCE_STUDENT_PIN => 'Student PIN',
            SessionTaskStudent::SOURCE_TRUSTED_CHILD_AUTO => 'Trusted auto',
            default => 'Completed',
        };
    }

    public function refreshPointsLabState(): void
    {
        if ($this->pointsLabInteractionIsOpen()) {
            $this->skipRender();

            return;
        }

        $signature = $this->pointsLabStateSignature();

        if ($signature === $this->pointsLabStateSignature) {
            $this->skipRender();

            return;
        }

        $this->pointsLabStateSignature = $signature;
        $this->loadSessionDisciplines();
        $this->loadBehaviorCounts();
        $this->loadPunishmentCounts();
        $this->loadTaskHistoryIfActive();
        $this->dispatch('reward-points:updated');
    }

    protected function pointsLabInteractionIsOpen(): bool
    {
        return $this->pendingType !== null
            || $this->selectedBehaviorId !== null
            || $this->recentAward !== null
            || $this->showBehaviorHistoryModal
            || $this->showPunishmentHistoryModal;
    }

    protected function pointsLabStateSignature(): string
    {
        $query = DB::table('student_session_discipline as ssd')
            ->leftJoin('teacher_subject_classes as tsc', 'tsc.id', '=', 'ssd.teacher_subject_classes_id')
            ->where('ssd.student_id', $this->studentId);

        $this->applyPointsLabDisciplineScope($query);

        $summary = $query
            ->selectRaw('COUNT(*) as rows_count')
            ->selectRaw('COALESCE(MAX(ssd.id), 0) as max_id')
            ->selectRaw("COALESCE(SUM(CASE WHEN ssd.type = 'Positive' THEN ssd.points ELSE 0 END), 0) as positive_points")
            ->selectRaw("COALESCE(SUM(CASE WHEN ssd.type != 'Positive' THEN ssd.points ELSE 0 END), 0) as negative_points")
            ->first();

        return implode('|', [
            (int) ($summary->rows_count ?? 0),
            (int) ($summary->max_id ?? 0),
            (int) ($summary->positive_points ?? 0),
            (int) ($summary->negative_points ?? 0),
            $this->historyTab === 'tasks' ? $this->taskHistoryStateSignature() : '',
        ]);
    }

    protected function taskHistoryStateSignature(): string
    {
        $summary = $this->taskHistoryBaseQuery()
            ->selectRaw('COUNT(sts.id) as rows_count')
            ->selectRaw('COALESCE(MAX(sts.id), 0) as max_id')
            ->selectRaw('COALESCE(SUM(sts.student_points), 0) as awarded_points')
            ->first();

        return implode(':', [
            (int) ($summary->rows_count ?? 0),
            (int) ($summary->max_id ?? 0),
            (int) ($summary->awarded_points ?? 0),
        ]);
    }

    public function render(): View
    {
        return view('livewire.teacher.reward-discipline-points');
    }
}
