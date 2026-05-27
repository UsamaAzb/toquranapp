<?php

namespace App\Http\Controllers\Front\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentConsequenceAgreementController extends Controller
{
    /**
     * Display a listing of the teacher's subjects.
     */
    public function get_agreement(Request $request)
    {

        if ((Auth::check()) && (Auth::user()->hasRole('student'))) {

            $user = Auth::user();
            $user_id = $user->id;
            $student = Student::where('user_id', $user_id)->first();

            if (! $student) {
                abort(404, 'Student record not found');
            }

            $student_id = $student->id;
            // $subjects = Auth::user()->teacherSubjects;
            // $teachersubjectid=$request->teachersubjectid;
            //   $class_subject_id=TeacherSubjectClass::where('id',$teachersubjectid)->value('class_subject_id');
            $show_bar = 'true';
            $breadcrumb_links = [
                'Consequence Agreement' => null,

            ];

            return view('student.ConsequenceAgreement.index', compact('show_bar', 'breadcrumb_links', 'student_id', 'student'));
        } else {
            return redirect()->route('login');
        }
    }
}
