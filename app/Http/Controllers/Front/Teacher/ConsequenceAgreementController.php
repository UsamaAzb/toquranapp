<?php

namespace App\Http\Controllers\Front\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsequenceAgreementController extends Controller
{
    /**
     * Display a listing of the teacher's subjects.
     */
    public function get_agreement(Request $request)
    {

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! Auth::user()->hasRole('teacher')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'student_id' => 'required|integer',
            'teachersubjectid' => 'required|integer',
        ]);
        $student_id = $request->student_id;
        $teachersubjectid = $request->teachersubjectid;

        return view('teacher.ConsequenceAgreement.index', compact('student_id', 'teachersubjectid'));

    }
}
