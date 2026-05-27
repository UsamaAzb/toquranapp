<?php

namespace App\Http\Controllers\Front\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GiveDisciplinePointsController extends Controller
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

    public function index(Request $request)
    {
        $student_id = (int) $request->student_id;
        $teacherSubjectClass = $this->findAuthorizedTeacherSubjectClass((int) $request->teachersubjectid, $student_id);
        $teachersubjectid = $teacherSubjectClass->id;

        $studentModel = Student::findOrFail($student_id);

        return view('teacher.RewardDisciplinePoints.index', [
            'student' => $studentModel,
            'teacherSubjectClassId' => $teachersubjectid,
        ]);
    }
}
