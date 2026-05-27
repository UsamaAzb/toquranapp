<?php

namespace App\Http\Controllers\Front\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherJourneyController extends Controller
{
    protected function findAuthorizedTeacherSubjectClass(int $teacherSubjectId, int $studentId): TeacherSubjectClass
    {
        $teacherSubjectClass = TeacherSubjectClass::query()
            ->whereKey($teacherSubjectId)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->firstOrFail();

        abort_unless(
            StudentsSubject::query()
                ->where('student_id', $studentId)
                ->where('class_subject_id', $teacherSubjectClass->class_subject_id)
                ->where('status', 'active')
                ->exists(),
            403
        );

        return $teacherSubjectClass;
    }

    /**
     * Display a listing of the student's classes.
     */
    public function index(Request $request)
    {

        if ((Auth::check()) && (Auth::user()->hasRole('teacher'))) {

            $user = Auth::user();
            $user_id = $user->id;
            $student_id = (int) $request->student_id;
            $teacherSubjectClass = $this->findAuthorizedTeacherSubjectClass((int) $request->teachersubjectid, $student_id);
            $teachersubjectid = $teacherSubjectClass->id;
            $academicYearId = AcademicYear::currentId();
            $reached_gift = StudentGift::where('student_id', $student_id)->where('academic_year_id', $academicYearId)->where('status', 'reached')->get();
            $pendingGift = StudentGift::where('student_id', $student_id)->where('academic_year_id', $academicYearId)->where('status', 'pending')->first();
            $student = Student::findOrFail($student_id);

            return view('teacher.journey.index', compact('reached_gift', 'pendingGift', 'student', 'teachersubjectid', 'student_id'));
        } else {
            return redirect()->route('login');
        }
    }

    public function board(Request $request)
    {

        if ((Auth::check()) && (Auth::user()->hasRole('teacher'))) {
            $user = Auth::user();
            $user_id = $user->id;
            $student_id = (int) $request->student_id;
            $teacherSubjectClass = $this->findAuthorizedTeacherSubjectClass((int) $request->teachersubjectid, $student_id);
            $teachersubjectid = $teacherSubjectClass->id;
            $academicYearId = AcademicYear::currentId();

            $pendingGift = StudentGift::where('status', 'pending')->where('student_id', $student_id)->where('academic_year_id', $academicYearId)->first();
            $student = Student::findOrFail($student_id);
            $lastReached = StudentGift::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->where('status', StudentGift::STATUS_REACHED)
                ->orderBy('points_required', 'desc')
                ->first();

            return view('teacher.journey.board_gifts', compact('lastReached', 'pendingGift', 'student', 'student_id', 'teachersubjectid'));
        }
    }
}
