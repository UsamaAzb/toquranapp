<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\PunishmentAgreement;
use App\Models\PunishmentType;
use App\Models\RewardDisciplinePoint;
use App\Models\Student;
use App\Models\Student_punishment;
use App\Models\Student_Session_Discipline;
use App\Models\TeacherSubjectClass;
use App\Services\RewardProgressionService;
use App\Support\LifecycleGate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class BehaviorModal extends Component
{
    private const BEHAVIOR_TYPES = ['Positive', 'Slip', 'No Way'];

    public string $userRole = 'teacher';

    public int $studentId = 0;

    public string $studentName = '';

    public ?string $categoryTitle = null;

    public ?string $pendingType = null; // Positive/Slip/No Way

    public array $modalBehaviors = [];

    public ?int $selectedBehaviorId = null;

    public ?int $pointsInput = null; // magnitude positive only

    public string $descriptionInput = '';

    public ?int $teacherSubjectClassesId = null;

    public ?int $academicYearId = null;

    // punishments
    public array $punishmentAgreements = [];

    public ?int $selectedPunishmentAgreementId = null;

    public bool $hasCustomizedPunishmentAgreement = false;

    public ?int $punishmentTypeId = null;

    public ?array $recentAward = null;

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

    protected function resolveTeacherSubjectClass(): ?TeacherSubjectClass
    {
        if (! $this->teacherSubjectClassesId) {
            return null;
        }

        if ($this->userRole === 'teacher') {
            return TeacherSubjectClass::query()
                ->whereKey($this->teacherSubjectClassesId)
                ->where('user_teacher_coteacher_id', Auth::id())
                ->availableForTeacher()
                ->withActiveStudentSubject($this->studentId)
                ->firstOrFail();
        }

        if ($this->userRole === 'parent') {
            $parentModel = Auth::user()?->parent_user;
            abort_unless(
                $parentModel && $parentModel->students()->where('students.id', $this->studentId)->exists(),
                403
            );

            $student = Student::query()
                ->whereKey($this->studentId)
                ->firstOrFail(['id', 'current_class_id', 'grade_level_id']);

            return TeacherSubjectClass::query()
                ->whereKey($this->teacherSubjectClassesId)
                // Match the current parent student mapping in ParentStudentController.
                ->where('subject_id', (int) config('toquran.parent_behavior_subject_id', 16))
                ->where('class_id', $student->current_class_id)
                ->where('grade_id', $student->grade_level_id)
                ->availableForTeacher()
                ->withActiveStudentSubject($this->studentId)
                ->first();
        }

        return null;
    }

    public function mount(): void
    {
        $user = Auth::user();
        if ($user?->hasRole('teacher')) {
            $this->userRole = 'teacher';
        } elseif ($user?->hasRole('parent')) {
            $this->userRole = 'parent';
        } elseif ($user?->hasRole('student')) {
            $this->userRole = 'student';
        } else {
            $this->userRole = 'other';
        }
    }

    // ✅ ده اللي الزرار بيناديه
    #[On('openAddBehaviorModal')]
    public function openAddBehaviorModal(
        int $studentId,
        string $type,
        ?int $teacherSubjectClassesId = null,
        ?int $academicYearId = null,
        ?string $categoryTitle = null
    ): void {
        if (! in_array($this->userRole, ['teacher', 'parent'])) {
            return;
        }

        $this->resetBehaviorModalState();
        $this->recentAward = null;
        $this->studentId = $studentId;
        $this->teacherSubjectClassesId = $teacherSubjectClassesId;
        $this->academicYearId = $academicYearId ?? AcademicYear::currentId();
        $this->categoryTitle = $categoryTitle;

        if (! $this->parentLifecycleAllowsBehavior()) {
            return;
        }

        $this->pendingType = $this->normalizeBehaviorType($type);

        $this->studentName = Student::whereKey($this->studentId)->value('first_name') ?? '';
        $this->loadModalBehaviors();
        $this->loadPunishmentsForModal($this->pendingType);

        // ✅ افتح bootstrap modal من خلال event
        $this->dispatch('open-teacher-behavior-modal');
    }

    protected function normalizeBehaviorType(?string $type): string
    {
        return in_array($type, self::BEHAVIOR_TYPES, true) ? $type : 'Positive';
    }

    protected function resetBehaviorModalState(): void
    {
        $this->resetErrorBag();
        $this->categoryTitle = null;
        $this->pendingType = null;
        $this->modalBehaviors = [];
        $this->selectedBehaviorId = null;
        $this->pointsInput = null;
        $this->descriptionInput = '';
        $this->punishmentAgreements = [];
        $this->selectedPunishmentAgreementId = null;
        $this->hasCustomizedPunishmentAgreement = false;
        $this->punishmentTypeId = null;
    }

    protected function loadModalBehaviors(): void
    {
        if (! $this->pendingType) {
            $this->modalBehaviors = [];

            return;
        }

        $behaviors = RewardDisciplinePoint::query()
            ->where(function ($query) {
                $query->whereNull('student_id')
                    ->orWhere('student_id', $this->studentId);
            })
            ->where('status', 'active')
            ->where('type', $this->pendingType)
            ->where('teacher_desc', 0)
            ->orderByRaw('COALESCE(sort, 999999) asc')
            ->orderBy('id')
            ->get(['id', 'title', 'type', 'discipline_icon_path', 'teacher_desc', 'selected', 'points']);

        $this->modalBehaviors = $behaviors->map(fn ($behavior) => [
            'id' => (int) $behavior->id,
            'title' => $behavior->title,
            'type' => $behavior->type,
            'icon_path' => $behavior->discipline_icon_path,
            'teacher_desc' => (int) $behavior->teacher_desc,
            'selected' => $behavior->selected,
            'points' => (int) $behavior->points,
        ])->toArray();
    }

    public function cancelBehaviorDescription(): void
    {
        $this->resetBehaviorModalState();
        $this->dispatch('close-teacher-behavior-modal');
    }

    public function updatedSelectedBehaviorId($value): void
    {
        $this->resetErrorBag('selectedBehaviorId');

        if (! $value) {
            $this->pointsInput = null;

            return;
        }

        foreach ($this->modalBehaviors as $behavior) {
            if ((int) ($behavior['id'] ?? 0) === (int) $value) {
                $this->pointsInput = max(1, (int) ($behavior['points'] ?? 1));

                return;
            }
        }

        $this->selectedBehaviorId = null;
        $this->pointsInput = null;
    }

    public function clearRecentAward(): void
    {
        $this->recentAward = null;
    }

    protected function loadPunishmentsForModal(string $behaviorType): void
    {
        $this->punishmentAgreements = [];
        $this->selectedPunishmentAgreementId = null;
        $this->hasCustomizedPunishmentAgreement = false;
        $this->punishmentTypeId = null;

        if (! in_array($behaviorType, ['Slip', 'No Way'], true)) {
            return;
        }

        $wantedTitle = $behaviorType === 'Slip' ? 'Minor Slip' : 'Serious Action';
        $type = PunishmentType::where('title', $wantedTitle)->where('active', 1)->first();
        if (! $type) {
            return;
        }

        $this->punishmentTypeId = (int) $type->id;

        $this->punishmentAgreements = PunishmentAgreement::query()
            ->where('student_id', $this->studentId)
            ->where('punishment_type_id', $this->punishmentTypeId)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get(['id', 'title'])
            ->map(fn ($a) => ['id' => (int) $a->id, 'title' => $a->title])
            ->toArray();

        $this->hasCustomizedPunishmentAgreement = collect($this->punishmentAgreements)
            ->contains(fn ($agreement) => Str::lower(trim($agreement['title'] ?? '')) === 'customized');
    }

    public function selectPunishment(int $agreementId): void
    {
        $this->selectedPunishmentAgreementId = $agreementId;
        $this->resetErrorBag('descriptionInput');
    }

    public function clearPunishmentSelection(): void
    {
        $this->selectedPunishmentAgreementId = null;
        $this->resetErrorBag('descriptionInput');
    }

    public function confirmBehaviorWithDescription(): void
    {
        if (! in_array($this->pendingType, self::BEHAVIOR_TYPES, true)) {
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

        $this->validate(
            $this->behaviorPointRules(),
            $this->behaviorPointMessages()
        );

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

        // validate customized BEFORE saving
        $agreement = null;
        if ($this->selectedPunishmentAgreementId && in_array($this->pendingType, ['Slip', 'No Way'], true)) {
            if ($this->selectedPunishmentAgreementId === -1) {
                if (empty(trim($this->descriptionInput))) {
                    $this->addError('descriptionInput', 'Description is required for Customized punishment.');

                    return;
                }

                $agreement = PunishmentAgreement::firstOrCreate(
                    [
                        'student_id' => $this->studentId,
                        'punishment_type_id' => $this->punishmentTypeId,
                        'title' => 'Customized',
                    ],
                    ['status' => 'active']
                );
            } else {
                $agreement = PunishmentAgreement::query()
                    ->whereKey($this->selectedPunishmentAgreementId)
                    ->where('student_id', $this->studentId)
                    ->where('punishment_type_id', $this->punishmentTypeId)
                    ->where('status', 'active')
                    ->first();
            }

            if ($agreement && Str::lower(trim($agreement->title)) === 'customized' && empty(trim($this->descriptionInput))) {
                $this->addError('descriptionInput', 'Description is required for Customized punishment.');

                return;
            }
        }

        try {
            $teacherSubjectClass = $this->resolveTeacherSubjectClass();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->addError('selectedBehaviorId', 'Unable to resolve the assigned subject for this student.');

            return;
        }

        if ($this->userRole === 'parent' && ! $teacherSubjectClass) {
            $this->addError('selectedBehaviorId', 'This student is not linked to the configured parent behavior subject yet.');

            return;
        }

        DB::transaction(function () use ($behavior, $agreement, $teacherSubjectClass) {
            $points = (int) $this->pointsInput;
            $isPositive = ($behavior->type === 'Positive');

            $sessionDiscipline = Student_Session_Discipline::create([
                'title' => $behavior->title,
                'discipline_icon_id' => $behavior->discipline_icon_id,
                'discipline_icon_path' => $behavior->discipline_icon_path,
                'student_reward_discipline_id' => $behavior->id,
                'teacher_subject_classes_id' => $this->teacherSubjectClassesId,
                'student_id' => $this->studentId,
                'points' => $points,
                'description' => $this->descriptionInput ?: null,
                'type' => $behavior->type,
            ]);

            $subject_id = $teacherSubjectClass?->subject_id;

            app(RewardProgressionService::class)->applyPointDelta(
                studentId: $this->studentId,
                pointsDelta: $isPositive ? $points : -$points,
                sourceType: 'discipline',
                sourceId: $sessionDiscipline->id,
                grantedBy: (int) Auth::id(),
                academicYearId: $this->academicYearId ?? AcademicYear::currentId(),
                subjectId: $subject_id,
                comment: $this->descriptionInput ?: null
            );

            if ($agreement) {
                Student_punishment::create([
                    'student_id' => $this->studentId,
                    'description' => $this->descriptionInput ?: null,
                    'punishment_agreement_id' => $agreement->id,
                    'student_session_discipline_id' => $sessionDiscipline->id,
                    'subject_id' => $subject_id,
                    'teacher_subject_class' => $this->teacherSubjectClassesId,
                    'created_by_id' => Auth::id(),
                    'created_at' => now()->toDateString(),
                ]);
            }
            // Toast uses actual saved magnitude
            $this->recentAward = [
                'id' => now()->timestamp,
                'student_name' => $this->studentName,
                'title' => $behavior->title,
                'points' => $points,
                'type' => $behavior->type,
                'icon_path' => $behavior->discipline_icon_path,
            ];
        });

        $this->dispatch('reward-discipline-points:refresh', type: $this->pendingType)->to(RewardDisciplinePoints::class);
        $this->dispatch('reward-points:updated');
        $this->cancelBehaviorDescription();
    }

    #[Computed]
    public function selectedBehaviorType(): ?string
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

    #[Computed]
    public function behaviorTone(): string
    {
        return match ($this->pendingType) {
            'Positive' => 'success',
            'Slip' => 'warning',
            'No Way' => 'danger',
            default => 'primary',
        };
    }

    /** @return array<int, int> */
    #[Computed]
    public function behaviorPointOptions(): array
    {
        return match ($this->selectedBehaviorType) {
            'Slip' => range(1, 5),
            'No Way' => range(5, 10),
            default => range(1, 10),
        };
    }

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function selectableModalBehaviors(): array
    {
        return collect($this->modalBehaviors)
            ->where('teacher_desc', 0)
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function selectedModalBehavior(): array
    {
        foreach ($this->selectableModalBehaviors as $behavior) {
            if ((int) ($behavior['id'] ?? 0) === (int) $this->selectedBehaviorId) {
                return $behavior;
            }
        }

        return [];
    }

    public function behaviorAssetUrl(?string $path): ?string
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

    /** @return array{0: int, 1: int} */
    private function behaviorPointBounds(): array
    {
        return match ($this->selectedBehaviorType) {
            'Slip' => [1, 5],
            'No Way' => [5, 10],
            default => [1, 10],
        };
    }

    /** @return array<string, array<int, string>> */
    private function behaviorPointRules(): array
    {
        [$min, $max] = $this->behaviorPointBounds();

        return [
            'pointsInput' => ['required', 'integer', 'min:'.$min, 'max:'.$max],
        ];
    }

    /** @return array<string, string> */
    private function behaviorPointMessages(): array
    {
        [$min, $max] = $this->behaviorPointBounds();

        return [
            'pointsInput.required' => 'Choose points before saving.',
            'pointsInput.integer' => 'Choose a whole point value.',
            'pointsInput.min' => "Choose points from {$min} to {$max}.",
            'pointsInput.max' => "Choose points from {$min} to {$max}.",
        ];
    }

    public function render()
    {
        return view('livewire.teacher.behavior-modal');
    }
}
