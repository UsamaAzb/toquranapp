<?php

namespace App\Livewire\Teacher;

use App\Models\DailySession;
use App\Models\DailySessionStudent;
use App\Models\SessionMaterial;
use App\Models\StudentsSubject;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
// لو SessionMaterial عندك موجودة في المشروع
use Livewire\Component;

class AssignDailySession extends Component
{
    public bool $show = false;

    public ?int $dailySessionId = null;

    /** UI */
    public array $students = [];     // [{id,name,class_id,class_name}]

    public array $selected = [];     // [student_id, ...]

    #[On('open-assign-daily-session')]
    public function open(int $dailySessionId): void
    {
        $this->resetValidation();
        $this->dailySessionId = $dailySessionId;
        $this->selected = [];

        $daily = DailySession::findOrFail($dailySessionId);
        $subjectId = (int) $daily->subject_id;

        $rows = StudentsSubject::query()
            ->currentYear()
            ->where('status', 'active')
            ->whereHas('gradeLevelSubject', fn ($q) => $q->where('subject_id', $subjectId))
            ->with(['student:id,first_name,current_class_id', 'student.currentClass:id,title', 'gradeLevelSubject:id,subject_id'])
            ->get();

        $this->students = $rows->map(function ($r) {
            $student = $r->student;
            $classId = (int) ($r->class_id ?? ($student->current_class_id ?? 0));
            $className = (string) optional($student?->currentClass)->title;
            $studentName = (string) ($student->first_name ?? ('Student #'.$r->student_id));

            return [
                'id' => (int) $r->student_id,
                'name' => $studentName,
                'class_id' => $classId,
                'class_name' => $className ?: ('Class #'.$classId),
                'student_label' => $studentName.' (#'.$r->student_id.')',
            ];
        })
            ->unique('id')
            ->values()
            ->all();

        $mainId = (int) $daily->main_daily_session_id;

        // كل daily_sessions اللي تحت نفس main (siblings)
        $siblingDailyIds = DailySession::where('main_daily_session_id', $mainId)
            ->pluck('id')->all();

        // subscriptions للطلاب في siblings
        $subs = DailySessionStudent::whereIn('student_id', collect($this->students)->pluck('id'))
            ->whereIn('daily_session_id', $siblingDailyIds)
            ->get()
            ->groupBy('student_id');

        // حط flags لكل طالب
        $this->students = collect($this->students)->map(function ($s) use ($subs, $dailySessionId) {
            $studentSubs = $subs->get($s['id'], collect());

            $activeForCurrent = $studentSubs->first(fn ($r) => (int) $r->daily_session_id === (int) $dailySessionId && (int) $r->is_active === 1);

            $activeOther = $studentSubs->first(fn ($r) => (int) $r->daily_session_id !== (int) $dailySessionId && (int) $r->is_active === 1);

            $s['is_assigned_current'] = (bool) $activeForCurrent;
            $s['assigned_elsewhere'] = (bool) $activeOther;

            return $s;
        })->values()->all();

        // ✅ pre-check للـ assigned الحالي
        $this->selected = collect($this->students)
            ->where('is_assigned_current', true)
            ->pluck('id')
            ->values()
            ->all();

        $this->show = true;
    }

    public function saveAssign(): void
    {

        $this->validate([
            'dailySessionId' => ['required', 'integer', 'exists:daily_sessions,id'],
            'selected' => ['nullable', 'array'],
            'selected.*' => ['integer'],
        ]);

        $daily = DailySession::with(['daily_session_tasks.attachments'])->findOrFail($this->dailySessionId);
        $tasks = $daily->daily_session_tasks;

        if ($tasks->isEmpty()) {
            $this->addError('selected', 'Add at least one task before assigning this automated task set to students.');

            return;
        }

        if ($tasks->contains(function ($task): bool {
            $hasDescription = trim((string) $task->description) !== '';
            $hasAttachments = $task->attachments->isNotEmpty();

            return ! $hasDescription && ! $hasAttachments;
        })) {
            $this->addError('selected', 'Each task needs a description or an attachment before assigning students.');

            return;
        }

        DB::transaction(function () {

            $daily = DailySession::findOrFail($this->dailySessionId);

            $currentDailyId = (int) $daily->id;
            $mainId = (int) $daily->main_daily_session_id;

            // كل daily_sessions تحت نفس main
            $siblingDailyIds = DB::table('daily_sessions')
                ->where('main_daily_session_id', $mainId)
                ->pluck('id')
                ->map(fn ($x) => (int) $x)
                ->all();

            // الطلاب اللي ظاهرين في المودال
            $allShownStudentIds = collect($this->students)
                ->pluck('id')
                ->map(fn ($x) => (int) $x)
                ->all();

            $checkedIds = collect($this->selected ?? [])
                ->map(fn ($x) => (int) $x)
                ->unique()
                ->values()
                ->all();

            $uncheckedIds = array_values(array_diff($allShownStudentIds, $checkedIds));

            // ✅ Checked = activate + start_at now
            foreach ($checkedIds as $studentId) {

                DB::table('daily_session_students')->updateOrInsert(
                    [
                        'student_id' => $studentId,
                        'daily_session_id' => $currentDailyId,
                    ],
                    [
                        'is_active' => 1,
                        'paused_at' => null,
                        'start_at' => now(),              // ✅ تفعيل جديد يبدأ من الآن
                        // last_generated_date نخليه زي ما هو (لا نعدله هنا)
                    ]
                );

                // enforce: واحد فقط active داخل نفس main
                DB::table('daily_session_students')
                    ->where('student_id', $studentId)
                    ->whereIn('daily_session_id', $siblingDailyIds)
                    ->where('daily_session_id', '!=', $currentDailyId)
                    ->where('is_active', 1)
                    ->update([
                        'is_active' => 0,
                        'paused_at' => now(),
                    ]);
            }

            // ✅ Unchecked = pause (stop recurrence)
            foreach ($uncheckedIds as $studentId) {
                DB::table('daily_session_students')
                    ->where('student_id', (int) $studentId)
                    ->where('daily_session_id', $currentDailyId)
                    ->update([
                        'is_active' => 0,
                        'paused_at' => now(),
                    ]);
            }
        });

        $this->show = false;
        $this->dispatch('toast', type: 'success', message: 'Assign saved. Sessions will be generated when the student opens their page.');
    }

    public function render()
    {
        // Grouping by class name في الـ UI
        $grouped = collect($this->students)->groupBy('class_name')->toArray();

        return view('livewire.teacher.assign-daily-session', [
            'groupedStudents' => $grouped,
        ]);
    }
}
