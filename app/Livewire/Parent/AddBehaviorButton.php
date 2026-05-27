<?php

namespace App\Livewire\Parent;

use App\Models\AcademicYear;
use App\Support\LifecycleGate;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AddBehaviorButton extends Component
{
    public int $studentId;

    public ?int $teacherSubjectClassesId = null;

    public ?int $academicYearId = null;

    public string $buttonClass = 'btn btn-sm btn-primary';

    public string $iconClass = 'ti tabler-plus';

    public string $label = 'Add behavior';

    public bool $showLabel = true;

    public bool $disabled = false;

    public ?string $disabledReason = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('parent'), 403);

        $this->academicYearId ??= AcademicYear::currentId();
        $this->refreshAccessState();
    }

    public function open(string $type): void
    {
        $this->refreshAccessState();

        if ($this->disabled) {
            $this->dispatch('toast', type: 'warning', message: $this->disabledReason ?? LifecycleGate::NEUTRAL_MESSAGE);

            return;
        }

        $this->dispatch(
            'openAddBehaviorModal',
            studentId: $this->studentId,
            type: $type,
            teacherSubjectClassesId: $this->teacherSubjectClassesId,
            academicYearId: $this->academicYearId
        )->to(BehaviorModal::class);
    }

    #[Computed]
    public function ariaLabel(): string
    {
        if ($this->disabledReason) {
            return $this->disabledReason;
        }

        return match ($this->label) {
            'Points lab' => 'Open points lab',
            'Behavior' => 'Add behavior',
            default => $this->label,
        };
    }

    public function render(): View
    {
        return view('livewire.parent.add-behavior-button');
    }

    private function refreshAccessState(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasRole('parent'), 403);

        $ownsStudent = $user->parent_user
          && $user->parent_user->students()->where('students.id', $this->studentId)->exists();
        abort_unless($ownsStudent, 403);

        $lifecycleGate = LifecycleGate::inspect($this->studentId);
        if ($lifecycleGate->denied()) {
            $this->disabled = true;
            $this->disabledReason = LifecycleGate::NEUTRAL_MESSAGE;

            return;
        }

        $this->disabled = false;
        $this->disabledReason = null;
    }
}
