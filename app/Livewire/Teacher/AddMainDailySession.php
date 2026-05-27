<?php

namespace App\Livewire\Teacher;

use App\Models\MainDailySession;
use Livewire\Component;

class AddMainDailySession extends Component
{
    public int $subjectId;

    public function mount(int $subjectId): void
    {
        $this->subjectId = $subjectId;
    }

    public function addMainDailySession(): void
    {
        // default title (لو عندك title محدد في DB/Seed عدّله هنا)
        $defaultTitle = 'New Main Session';

        $main = MainDailySession::create([
            'subject_id' => $this->subjectId,
            'title' => $defaultTitle,
        ]);

        // نعمل refresh للـ board
        $this->dispatch('main-daily-session-added', id: (int) $main->id);

        // (اختياري) لو عندك toast event شغال:
        // $this->dispatch('toast', type: 'success', message: 'Main session added.');
    }

    public function render()
    {
        return view('livewire.teacher.add-main-daily-session');
    }
}
