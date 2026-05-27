<?php

namespace App\Livewire\Parent;

use App\Models\Student;
use App\Models\StudentTaskApprovalSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrustedChildApprovalSetting extends Component
{
    public int $studentId;

    public bool $enabled = false;

    public function mount(int $studentId): void
    {
        $this->studentId = $studentId;
        $this->authorizeParent();

        $this->enabled = (bool) StudentTaskApprovalSetting::query()
            ->where('student_id', $this->studentId)
            ->value('trusted_auto_approval_enabled');
    }

    public function updatedEnabled(bool $value): void
    {
        $this->authorizeParent();

        StudentTaskApprovalSetting::updateOrCreate(
            ['student_id' => $this->studentId],
            [
                'trusted_auto_approval_enabled' => $value,
                'updated_by_user_id' => Auth::id(),
            ]
        );
    }

    public function render()
    {
        return view('livewire.parent.trusted-child-approval-setting');
    }

    private function authorizeParent(): void
    {
        $user = Auth::user();
        abort_unless($user?->hasRole('parent'), 403);

        $studentExists = Student::query()
            ->whereKey($this->studentId)
            ->where('parent_id', $user->parent_user?->id)
            ->exists();

        abort_unless($studentExists, 403);
    }
}
