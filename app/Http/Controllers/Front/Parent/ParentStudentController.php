<?php

namespace App\Http\Controllers\Front\Parent;

use App\Http\Controllers\Controller;
use App\Models\SessionTaskStudent;
use App\Models\Student;
use App\Models\TeacherSubjectClass;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParentStudentController extends Controller
{
    /**
     * Display a listing of the teacher's subjects.
     */
    public function index(): View|RedirectResponse
    {
        if ((Auth::check()) && (Auth::user()->hasRole('parent'))) {
            $user = Auth::user();
            $students = $user->parent_user->students()
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->orderBy('students.id')
                ->get();
            $subjectId = (int) config('toquran.parent_behavior_subject_id', 15);
            $parentFirstName = $user->parent_user?->first_name
                ?? Str::before((string) ($user->name ?? ''), ' ');
            $parentFirstName = trim((string) $parentFirstName) !== '' ? $parentFirstName : 'there';

            $classIds = $students->pluck('current_class_id')->filter()->unique()->values();
            $gradeIds = $students->pluck('grade_level_id')->filter()->unique()->values();

            $map = TeacherSubjectClass::query()
                ->where('subject_id', $subjectId)
                ->whereIn('class_id', $classIds)
                ->whereIn('grade_id', $gradeIds)
                ->availableForTeacher()
                ->withActiveStudentSubject()
                ->get(['id', 'class_id', 'grade_id'])
                ->mapWithKeys(fn ($row) => [
                    $row->class_id.'-'.$row->grade_id => $row->id,
                ]);

            foreach ($students as $student) {
                $key = $student->current_class_id.'-'.$student->grade_level_id;
                $student->teacher_subject_classes_id = $map[$key] ?? null;
            }

            $reviewCounts = SessionTaskStudent::query()
                ->select('student_id', DB::raw('COUNT(*) as review_count'))
                ->whereIn('student_id', $students->pluck('id'))
                ->whereIn('status', [
                    SessionTaskStudent::STATUS_IN_REVIEW,
                    SessionTaskStudent::STATUS_LEGACY_PENDING,
                ])
                ->groupBy('student_id')
                ->pluck('review_count', 'student_id')
                ->map(fn ($count): int => (int) $count);

            $breadcrumb_links = [
                'My Children' => null,

            ];

            return view('parent.students.my-children', compact('students', 'reviewCounts', 'breadcrumb_links', 'parentFirstName'));
        } else {
            return redirect()->route('login');

        }
    }

    public function taskApprovals(int $student): View
    {
        abort_unless(Auth::check() && Auth::user()->hasRole('parent'), 403);

        $studentModel = Student::query()->findOrFail($student);
        abort_unless(
            Auth::user()->parent_user
                && Auth::user()->parent_user->students()->where('students.id', $studentModel->id)->exists(),
            403
        );

        return view('parent.students.task-approvals', [
            'student' => $studentModel,
        ]);
    }
}
