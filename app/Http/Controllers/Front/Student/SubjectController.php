<?php

namespace App\Http\Controllers\Front\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    /**
     * Display a listing of the student's subjects.
     */
    public function index()
    {
        // Get all subjects from the student's classes
        $subjects = collect();
        foreach (Auth::user()->studentClasses as $class) {
            $subjects = $subjects->merge($class->subjects);
        }
        $subjects = $subjects->unique('id');

        return view('student.subjects.index', compact('subjects'));
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        // Check if the student has access to this subject through their classes
        $hasAccess = false;
        foreach (Auth::user()->studentClasses as $class) {
            if ($class->subjects->contains($subject)) {
                $hasAccess = true;
                break;
            }
        }

        if (! $hasAccess) {
            abort(403, 'Unauthorized action.');
        }

        $subject->load(['teachers', 'classes']);

        return view('student.subjects.show', compact('subject'));
    }
}
