<?php

namespace App\Livewire\Teacher;

use App\Models\ClassSession;
use App\Models\MainDailySessionTemplate;
use App\Models\Student;
use App\Models\TeacherSubjectClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class AutomatedTaskGeneratedHistoryPanel extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public bool $show = false;

    public ?int $templateId = null;

    public ?int $studentId = null;

    public string $search = '';

    public int $perPage = 5;

    public function mount(?int $templateId = null, ?int $studentId = null, bool $show = false): void
    {
        if ($templateId !== null) {
            $this->templateId = $this->resolveOwnedTemplateOrFail($templateId)->id;
            $this->studentId = $studentId;
            $this->show = $show;
        }
    }

    #[On('open-automated-task-history-panel')]
    public function open(int $templateId, ?int $studentId = null): void
    {
        $this->templateId = $this->resolveOwnedTemplateOrFail($templateId)->id;
        $this->studentId = $studentId;
        $this->search = '';
        $this->show = true;
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

    public function selectStudent(int $studentId): void
    {
        if ($this->templateId === null) {
            return;
        }

        $this->resolveGeneratedStudentOrFail($this->templateId, $studentId);
        $this->studentId = $studentId;
        $this->resetPage();
    }

    public function render(): View
    {
        if (! $this->show || $this->templateId === null) {
            return view('livewire.teacher.automated-task-generated-history-panel', [
                'template' => null,
                'students' => collect(),
                'selectedStudent' => null,
                'sessions' => null,
            ]);
        }

        $template = $this->resolveOwnedTemplateOrFail($this->templateId);
        $students = $this->generatedStudents($template);

        if ($this->studentId === null && $students->isNotEmpty()) {
            $this->studentId = (int) $students->first()->id;
        }

        $selectedStudent = $this->studentId !== null
            ? $this->resolveGeneratedStudentOrFail($template->id, $this->studentId)
            : null;

        return view('livewire.teacher.automated-task-generated-history-panel', [
            'template' => $template,
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'sessions' => $selectedStudent
                ? $this->generatedSessions($template->id, (int) $selectedStudent->id)
                : null,
        ]);
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

    private function generatedStudents(MainDailySessionTemplate $template): Collection
    {
        return Student::query()
            ->whereIn('students.id', function ($query) use ($template): void {
                $query->select('student_id')
                    ->from('class_sessions')
                    ->where('main_daily_session_template_id', $template->id)
                    ->whereNotNull('student_id')
                    ->distinct();
            })
            ->with([
                'parent:id,first_name,last_name',
                'currentClass:id,title',
            ])
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('students.id', 'like', "%{$search}%")
                        ->orWhereHas('parent', function (Builder $parentQuery) use ($search): void {
                            $parentQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->get();
    }

    private function resolveGeneratedStudentOrFail(int $templateId, int $studentId): Student
    {
        return Student::query()
            ->whereKey($studentId)
            ->whereExists(function ($query) use ($templateId): void {
                $query->selectRaw('1')
                    ->from('class_sessions')
                    ->whereColumn('class_sessions.student_id', 'students.id')
                    ->where('class_sessions.main_daily_session_template_id', $templateId);
            })
            ->with([
                'parent:id,first_name,last_name',
                'currentClass:id,title',
            ])
            ->firstOrFail();
    }

    private function generatedSessions(int $templateId, int $studentId): LengthAwarePaginator
    {
        return ClassSession::query()
            ->where('main_daily_session_template_id', $templateId)
            ->where('student_id', $studentId)
            ->with([
                'tasks' => fn ($query) => $query
                    ->orderBy('sort')
                    ->orderBy('id')
                    ->with([
                        'attachments',
                        'taskStudents' => fn ($taskStudentQuery) => $taskStudentQuery
                            ->where('student_id', $studentId),
                    ]),
            ])
            ->orderByDesc('generated_for_date')
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }
}
