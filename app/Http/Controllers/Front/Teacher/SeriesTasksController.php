<?php

namespace App\Http\Controllers\Front\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SeriesTasksController extends Controller
{
    public function subjects(): View
    {
        $teacherSubjectIds = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->pluck('subject_id')
            ->unique();

        $subjects_list = Subject::query()
            ->whereIn('id', $teacherSubjectIds)
            ->where('active', 1)
            ->get();
        $breadcrumb_links = [
            'Automation' => null,
            'Series Tasks' => null,
        ];

        return view('teacher.SeriesTasks.subjects', compact('subjects_list', 'breadcrumb_links'));
    }

    public function board(string $auth_role, int $subject): View
    {
        abort_unless(Subject::query()->whereKey($subject)->exists(), 404);

        $hasSubject = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->where('subject_id', $subject)
            ->availableForTeacher()
            ->exists();

        abort_unless($hasSubject, 403);

        $subjectTitle = Subject::query()->whereKey($subject)->value('title') ?: 'Subject';
        $subjectid = $subject;
        $breadcrumb_links = [
            'Automation' => null,
            'Series Tasks' => route('series-tasks.subjects', ['auth_role' => 'teacher']),
            $subjectTitle => null,
        ];

        return view('teacher.SeriesTasks.tasks', compact('subjectid', 'subjectTitle', 'breadcrumb_links'));
    }
}
