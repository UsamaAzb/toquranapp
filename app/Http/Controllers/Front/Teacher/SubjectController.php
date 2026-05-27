<?php

namespace App\Http\Controllers\Front\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    /**
     * Display a listing of the teacher's subjects.
     */
    public function index()
    {
        $subjects = Auth::user()->teacherSubjects;

        return view('teacher.subjects.index', compact('subjects'));
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        // Check if the teacher is assigned to this subject
        if (! Auth::user()->teacherSubjects->contains($subject)) {
            abort(403, 'Unauthorized action.');
        }

        $subject->load(['grades', 'classes']);

        return view('teacher.subjects.show', compact('subject'));
    }
}
