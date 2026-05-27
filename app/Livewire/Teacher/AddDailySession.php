<?php

namespace App\Livewire\Teacher;

use App\Models\DailySession;
use App\Models\MainDailySession;
use App\Models\TeacherSubjectClass;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AddDailySession extends Component
{
    public int $subjectId;

    public int $mainDailySessionId;

    public function mount(int $subjectId, int $mainDailySessionId): void
    {
        $this->subjectId = $subjectId;
        $this->mainDailySessionId = $mainDailySessionId;
    }

    protected function resolveOwnedMainDailySessionOrFail(): MainDailySession
    {
        TeacherSubjectClass::query()
            ->where('subject_id', $this->subjectId)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->firstOrFail();

        return MainDailySession::query()
            ->whereKey($this->mainDailySessionId)
            ->where('subject_id', $this->subjectId)
            ->firstOrFail();
    }

    public function addDailySession(): void
    {
        try {
            $mainDailySession = $this->resolveOwnedMainDailySessionOrFail();
            $defaultTitle = 'New Daily Session';

            $daily = DailySession::create([
                'main_daily_session_id' => $mainDailySession->id,
                'title' => $defaultTitle,
                'subject_id' => (int) $mainDailySession->subject_id,
            ]);

            $this->dispatch('daily-session-added', dailySessionId: (int) $daily->id);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'danger', message: 'Failed to add daily session.');
        }
    }

    public function render()
    {
        return view('livewire.teacher.add-daily-session');
    }
}
