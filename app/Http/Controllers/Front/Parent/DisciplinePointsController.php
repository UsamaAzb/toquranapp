<?php

namespace App\Http\Controllers\Front\Parent;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Support\LifecycleGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisciplinePointsController extends Controller
{
    public function index(Request $request)
    {
        $student_id = $request->student_id;

        $user_id = Auth::id();
        $parentModel = ParentModel::where('user_id', $user_id)->firstOrFail();
        $studentModel = $parentModel->students()->findOrFail($student_id);
        $lifecycleGate = LifecycleGate::inspect((int) $student_id);
        if ($lifecycleGate->denied()) {
            return redirect()
                ->route('parent.students')
                ->with('warning', LifecycleGate::NEUTRAL_MESSAGE);
        }

        $stu_name = $studentModel->first_name;
        $breadcrumb_links = [
            $stu_name => url('students'),
            'Points Lab' => null,

        ];

        return view('parent.students.RewardDisciplinePoints.index', [
            'student' => $studentModel,
            'breadcrumb_links' => $breadcrumb_links,
        ]);
    }
}
