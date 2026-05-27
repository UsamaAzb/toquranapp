<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Support\LifecycleGate;
use Livewire\Component;

class AddBehaviorButton extends Component
{
    public int $studentId;

    public ?int $teacherSubjectClassesId = null;

    public ?int $academicYearId = null;

    public string $buttonClass = 'btn btn-sm  btn-primary';

    public string $iconClass = 'ti tabler-plus';

    public string $label = 'Add Behavior';

    public bool $showLabel = true;

    public bool $disabled = false;

    public ?string $disabledReason = null;

    public function mount(): void
    {
        $this->academicYearId ??= AcademicYear::currentId();
        $this->refreshLifecycleState();
    }

    private function refreshLifecycleState(): void
    {
        if (! auth()->user()?->hasRole('parent')) {
            return;
        }

        $lifecycleGate = LifecycleGate::inspect($this->studentId);
        if ($lifecycleGate->denied()) {
            $this->disabled = true;
            $this->disabledReason = LifecycleGate::NEUTRAL_MESSAGE;
        }
    }

    public function open(string $type): void
    {
        $this->refreshLifecycleState();

        if ($this->disabled) {
            $this->dispatch('toast', type: 'warning', message: $this->disabledReason ?? LifecycleGate::NEUTRAL_MESSAGE);

            return;
        }

        $this->dispatch('openAddBehaviorModal',
            studentId: $this->studentId,
            type: $type,
            teacherSubjectClassesId: $this->teacherSubjectClassesId,
            academicYearId: $this->academicYearId
        )->to(BehaviorModal::class);
    }

    public function render()
    {
        return view('livewire.teacher.add-behavior-button');
    }
}
