<?php

namespace App\Livewire\Student;

use Livewire\Component;

class StudentTabsHeader extends Component
{
    public ?int $studentId = null;

    public ?int $teacherSubjectId = null;

    public function mount(?int $studentId = null, ?int $teacherSubjectId = null): void
    {
        $this->studentId = $studentId;
        $this->teacherSubjectId = $teacherSubjectId;
    }

    public function render()
    {

        $cards = [
            [
                'title' => 'My Subjects',
                'icon' => 'ti tabler-stack-front',
                'color' => 'primary',
                'link' => url('student/classes'),
            ],
        ];
        if ($this->studentId !== null) {
            $cards[] = [
                'title' => 'Reward System',
                'icon' => 'ti tabler-bell',
                'color' => 'success',
                'link' => url("student/journey/board/{$this->studentId}"),
            ];

            if ($this->teacherSubjectId !== null) {
                $cards[] = [
                    'title' => 'Consequence Agreement',
                    'icon' => 'ti tabler-heart-handshake',
                    'color' => 'danger',
                    'link' => url("student/consequence-agreement/{$this->studentId}/{$this->teacherSubjectId}"),
                ];
                $cards[] = [
                    'title' => 'Discipline Points',
                    'icon' => 'ti tabler-brand-google-fit',
                    'color' => 'danger',
                    'link' => url("student/discipline-points/{$this->studentId}/{$this->teacherSubjectId}"),                ];
            }
        }



        return view('livewire.student.student-tabs-header', compact('cards'));
    }
}
