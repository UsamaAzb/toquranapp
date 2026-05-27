<?php

namespace App\Http\Controllers\Front\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    /**
     * Display a listing of the student's classes.
     */
    public function index()
    {
        // Classes are loaded via Auth::user()->studentClasses in the view
        return view('student.classes.index');
    }

    /**
     * Display the specified class.
     */
    public function show(ClassGroup $class)
    {
        // Check if the student is enrolled in this class
        if (! Auth::user()->studentClasses->contains($class)) {
            abort(403, 'Unauthorized action.');
        }

        $class->load(['grade', 'subjects', 'teachers']);

        return view('student.classes.show', compact('class'));
    }
}
