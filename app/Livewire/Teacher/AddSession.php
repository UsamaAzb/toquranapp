<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\ClassSession;
use App\Models\SessionMaterial;
use App\Models\TeacherSubjectClass;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AddSession extends Component
{
    public int $teacherSubjectClassId;

    public function mount(int $teacherSubjectClassId): void
    {
        $this->teacherSubjectClassId = $teacherSubjectClassId;
    }

    public function addSession(): void
    {
        try {
            $tsc = TeacherSubjectClass::query()
                ->where('user_teacher_coteacher_id', Auth::id())
                ->availableForTeacher()
                ->withActiveStudentSubject()
                ->findOrFail($this->teacherSubjectClassId);

            $newSessionId = DB::transaction(function () use ($tsc) {
                $academicYearId = AcademicYear::currentId();

                $unit = Unit::firstOrCreate(
                    [
                        'teacher_subject_classes_id' => $tsc->id,
                        'unit_type_id' => 1,
                        'academic_year_id' => $academicYearId,
                    ],
                    [
                        'title' => 'term_one_'.$tsc->class_name,
                        'subject_id' => $tsc->subject_id,
                        'class_id' => $tsc->class_id,
                        'teacher_id' => $tsc->user_teacher_coteacher_id,
                        'grade_level_id' => $tsc->grade_id,
                        'status' => 'published',
                        'is_interdisciplinary' => 0,
                    ]
                );

                $now = Carbon::now();
                $session = ClassSession::create(
                    [
                        'teacher_subject_classes_id' => $tsc->id,
                        'class_id' => $tsc->class_id,
                        'subject_id' => $tsc->subject_id,
                        'grade_id' => $tsc->grade_id,
                        'teacher_id' => $tsc->user_teacher_coteacher_id,
                        'date' => $now->toDateString(),        // if column type is DATE
                        'session_start_time' => $now->format('H:i'),
                        'session_end_time' => $now->copy()->addMinutes(60)->format('H:i'),
                        'unit_id' => $unit->id,
                        'class_subject_id' => $tsc->class_subject_id,
                        'title' => $now->format('j F Y'),
                    ]);
                SessionMaterial::create([
                    'teacher_subject_classes_id' => $tsc->id,
                    'subject_id' => $tsc->subject_id,
                    'grade_id' => $tsc->grade_id,
                    'teacher_id' => $tsc->user_teacher_coteacher_id,
                    'session_id' => $session->id,
                    'unit_id' => $unit->id,
                    'status' => 'draft',
                    'assign_to_all' => 'all',
                ]);

                return $session->id;
            });
            // أحداث للتحديث/التنبيه في الواجهة
            $this->dispatch('session-added', id: $newSessionId); // خليه ينعش ليست الجلسات لو بتسمعيه في كومبوننت آخر
            $this->dispatch('toast', type: 'success', message: 'Session added successfully.');

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'danger', message: 'Failed to add session.');
        }
    }

    public function render()
    {
        // الواجهة هنا بس للزر — مفيش مودال
        return view('livewire.teacher.add-session');
    }
}
