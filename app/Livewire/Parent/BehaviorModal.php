<?php

namespace App\Livewire\Parent;

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
use App\Support\ParentBehaviorSubjectResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class BehaviorModal extends Component
{
    public int $studentId = 0;

    public string $studentName = '';

    public ?string $pendingType = null;

    public array $modalBehaviors = [];

    public ?int $selectedBehaviorId = null;

    public ?int $pointsInput = null;

    public string $descriptionInput = '';

    public ?int $teacherSubjectClassesId = null;

    public ?int $academicYearId = null;

    public array $punishmentAgreements = [];

    public ?int $selectedPunishmentAgreementId = null;

    public ?int $punishmentTypeId = null;

    public ?array $recentAward = null;

    public function mount(): void
    {
        abort_unless(Auth::user()?->hasRole('parent'), 403);
    }

    #[On('openAddBehaviorModal')]
    public function openAddBehaviorModal(
        int $studentId,
        string $type,
        ?int $teacherSubjectClassesId = null,
        ?int $academicYearId = null
    ): void {
        abort_unless(Auth::user()?->hasRole('parent'), 403);

        $this->studentId = $studentId;
        $this->teacherSubjectClassesId = $teacherSubjectClassesId;
        $this->academicYearId = $academicYearId ?? AcademicYear::currentId();

        $this->resetModalState();

        if (! $this->parentAllowsBehavior()) {
            return;
        }

        $this->pendingType = in_array($type, ['Positive', 'Slip', 'No Way'], true) ? $type : 'Positive';
        $this->studentName = Student::query()->whereKey($this->studentId)->value('first_name') ?? '';
        $this->loadBehaviors();
        $this->loadPunishmentsForModal($this->pendingType);

        $this->dispatch('open-parent-behavior-modal');
    }

    public function cancelBehaviorDescription(): void
    {
        $this->resetErrorBag();
        $this->resetModalState();
        $this->dispatch('close-parent-behavior-modal');
    }

    public function clearPunishmentSelection(): void
    {
        $this->selectedPunishmentAgreementId = null;
        $this->resetErrorBag('descriptionInput');
    }

    public function selectPunishment(int $agreementId): void
    {
        $exists = collect($this->punishmentAgreements)
            ->contains(fn (array $agreement): bool => (int) ($agreement['id'] ?? 0) === $agreementId);

        if (! $exists) {
            return;
        }

        $this->selectedPunishmentAgreementId = $agreementId;
        $this->resetErrorBag('descriptionInput');
    }

    public function updatedSelectedBehaviorId($value): void
    {
        $this->resetErrorBag('selectedBehaviorId');
        $this->resetErrorBag('pointsInput');

        $selected = collect($this->modalBehaviors)
            ->first(fn (array $behavior): bool => (int) ($behavior['id'] ?? 0) === (int) $value);

        $this->pointsInput = $selected ? (int) ($selected['points'] ?? 1) : null;
    }

    public function confirmBehaviorWithDescription(): void
    {
        if (! $this->pendingType) {
            $this->addError('selectedBehaviorId', 'Open the behavior popup again before saving.');

            return;
        }

        if (! $this->selectedBehaviorId) {
            $this->addError('selectedBehaviorId', 'Select a behavior first.');

            return;
        }

        if (! $this->parentAllowsBehavior()) {
            return;
        }

        $this->validate(
            $this->behaviorPointRules(),
            $this->behaviorPointMessages()
        );

        $behavior = RewardDisciplinePoint::query()
            ->whereKey($this->selectedBehaviorId)
            ->where(function ($query): void {
                $query->whereNull('student_id')
                    ->orWhere('student_id', $this->studentId);
            })
            ->where('status', 'active')
            ->where('type', $this->pendingType)
            ->where('teacher_desc', 0)
            ->first();

        if (! $behavior) {
            $this->addError('selectedBehaviorId', 'Selected behavior is no longer available.');

            return;
        }

        $agreement = $this->selectedPunishmentAgreementId
            ? PunishmentAgreement::query()
                ->whereKey($this->selectedPunishmentAgreementId)
                ->where('student_id', $this->studentId)
                ->where('punishment_type_id', $this->punishmentTypeId)
                ->where('status', 'active')
                ->first()
            : null;

        if ($agreement && Str::lower(trim($agreement->title)) === 'customized' && trim($this->descriptionInput) === '') {
            $this->addError('descriptionInput', 'Description is required for Customized punishment.');

            return;
        }

        $teacherSubjectClass = $this->resolveParentBehaviorSubject();
        if (! $teacherSubjectClass) {
            $this->addError('selectedBehaviorId', ParentBehaviorSubjectResolver::ERROR_MESSAGE);

            return;
        }

        DB::transaction(function () use ($behavior, $agreement, $teacherSubjectClass): void {
            $points = (int) $this->pointsInput;
            $isPositive = $behavior->type === 'Positive';

            $sessionDiscipline = Student_Session_Discipline::create([
                'title' => $behavior->title,
                'discipline_icon_id' => $behavior->discipline_icon_id,
                'discipline_icon_path' => $behavior->discipline_icon_path,
                'student_reward_discipline_id' => $behavior->id,
                'teacher_subject_classes_id' => $teacherSubjectClass->id,
                'student_id' => $this->studentId,
                'points' => $points,
                'description' => $this->descriptionInput ?: null,
                'type' => $behavior->type,
            ]);

            app(RewardProgressionService::class)->applyPointDelta(
                studentId: $this->studentId,
                pointsDelta: $isPositive ? $points : -$points,
                sourceType: 'discipline',
                sourceId: $sessionDiscipline->id,
                grantedBy: (int) Auth::id(),
                academicYearId: $this->academicYearId ?? AcademicYear::currentId(),
                subjectId: $teacherSubjectClass->subject_id,
                comment: $this->descriptionInput ?: null
            );

            if ($agreement) {
                Student_punishment::create([
                    'student_id' => $this->studentId,
                    'description' => $this->descriptionInput ?: null,
                    'punishment_agreement_id' => $agreement->id,
                    'student_session_discipline_id' => $sessionDiscipline->id,
                    'subject_id' => $teacherSubjectClass->subject_id,
                    'teacher_subject_class' => $teacherSubjectClass->id,
                    'created_by_id' => Auth::id(),
                    'created_at' => now()->toDateString(),
                ]);
            }

            $this->recentAward = [
                'id' => now()->timestamp,
                'student_name' => $this->studentName,
                'title' => $behavior->title,
                'points' => $points,
                'type' => $behavior->type,
                'icon_path' => $behavior->discipline_icon_path,
            ];
        });

        $this->dispatch('reward-points:updated');
        $this->dispatch('reward-discipline-points:refresh', type: $behavior->type);
        $this->cancelBehaviorDescription();
    }

    #[Computed]
    public function selectedBehaviorType(): ?string
    {
        if (! $this->selectedBehaviorId) {
            return $this->pendingType;
        }

        foreach ($this->modalBehaviors as $behavior) {
            if ((int) $behavior['id'] === (int) $this->selectedBehaviorId) {
                return $behavior['type'] ?? $this->pendingType;
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

    public function render()
    {
        return view('livewire.parent.behavior-modal');
    }

    private function resetModalState(): void
    {
        $this->resetErrorBag();
        $this->pendingType = null;
        $this->modalBehaviors = [];
        $this->selectedBehaviorId = null;
        $this->pointsInput = null;
        $this->descriptionInput = '';
        $this->punishmentAgreements = [];
        $this->selectedPunishmentAgreementId = null;
        $this->punishmentTypeId = null;
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

    private function parentAllowsBehavior(): bool
    {
        $user = Auth::user();
        abort_unless($user?->hasRole('parent'), 403);

        $parent = $user->parent_user;
        abort_unless($parent && $parent->students()->where('students.id', $this->studentId)->exists(), 403);

        if (! LifecycleGate::inspect($this->studentId)->denied()) {
            return true;
        }

        $this->addError('selectedBehaviorId', LifecycleGate::NEUTRAL_MESSAGE);
        $this->dispatch('toast', type: 'warning', message: LifecycleGate::NEUTRAL_MESSAGE);

        return false;
    }

    private function resolveParentBehaviorSubject(): ?TeacherSubjectClass
    {
        return app(ParentBehaviorSubjectResolver::class)
            ->resolveForStudent($this->studentId, $this->teacherSubjectClassesId);
    }

    private function loadBehaviors(): void
    {
        $behaviors = RewardDisciplinePoint::query()
            ->where(function ($query): void {
                $query->whereNull('student_id')
                    ->orWhere('student_id', $this->studentId);
            })
            ->where('status', 'active')
            ->where('type', $this->pendingType)
            ->orderByDesc('teacher_desc')
            ->orderByRaw('COALESCE(sort, 999999) asc')
            ->orderBy('id')
            ->get(['id', 'title', 'type', 'points', 'discipline_icon_path', 'teacher_desc', 'selected']);

        $this->modalBehaviors = $behaviors->map(fn ($behavior): array => [
            'id' => (int) $behavior->id,
            'title' => $behavior->title,
            'type' => $behavior->type,
            'points' => (int) $behavior->points,
            'icon_path' => $behavior->discipline_icon_path,
            'teacher_desc' => (int) $behavior->teacher_desc,
            'selected' => $behavior->selected,
        ])->toArray();

        $this->selectedBehaviorId = null;
        $this->pointsInput = null;
    }

    private function loadPunishmentsForModal(string $behaviorType): void
    {
        if (! in_array($behaviorType, ['Slip', 'No Way'], true)) {
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

        PunishmentAgreement::firstOrCreate(
            ['student_id' => $this->studentId, 'punishment_type_id' => $this->punishmentTypeId, 'title' => 'Customized'],
            ['status' => 'active']
        );

        $this->punishmentAgreements = PunishmentAgreement::query()
            ->where('student_id', $this->studentId)
            ->where('punishment_type_id', $this->punishmentTypeId)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get(['id', 'title'])
            ->map(fn ($agreement): array => ['id' => (int) $agreement->id, 'title' => $agreement->title])
            ->toArray();
    }
}
