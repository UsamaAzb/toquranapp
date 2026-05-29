<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use App\Models\ParentModel;
use App\Models\SchoolProgram;
use App\Models\Services_type;
use App\Models\Student;
use App\Support\SchoolSystemOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the classes.
     */
    public function index()
    {
        $classes = Student::with('gradeLevel')->orderBy('first_name')->get();

        return view('admin.classes.index', compact('classes'));
    }

    public function get_account(Request $request)
    {
        $stu_id = $request->id;
        $student = Student::findOrFail($stu_id);
        $grade_levels = GradeLevel::where('active', 1)->orderBy('level_order', 'asc')->get();
        $programs = SchoolProgram::where('active', 1)->get();
        $services_types = Services_type::where('active', 1)->get();

        return view('admin.Students.view-account', compact('student', 'grade_levels', 'programs', 'services_types'));
    }

    public function updateFromModal(Request $request, Student $student)
    {
        $parent = $student->parent;
        if (! $parent instanceof ParentModel) {
            return response()->json(['message' => 'Parent not found'], 404);
        }

        $data = $request->validate([
            'parent.first_name' => ['required', 'string', 'max:100'],
            'parent.last_name' => ['required', 'string', 'max:100'],
            'parent.email' => ['nullable', 'email', 'max:190', Rule::unique('parents', 'email')->ignore($parent->id)],
            'parent.phone' => ['nullable', 'string', 'max:30', Rule::unique('parents', 'phone')->ignore($parent->id)],

            'student.first_name' => ['required', 'string', 'max:100'],
            'student.last_name' => ['nullable', 'string', 'max:100'],
            'student.age' => ['required', 'integer', 'min:1', 'max:30'],
            'student.school_system' => ['required', Rule::in(SchoolSystemOptions::values())],
            'student.program_id' => ['nullable', 'integer', 'exists:school_program,id'],
            'student.grade_level_id' => ['nullable', 'integer', 'exists:grade_levels,id'],
            'student.service_type_id' => ['required', 'integer', Rule::exists('services_types', 'id')],
        ]);

        DB::transaction(function () use ($data, $parent, $student) {
            if (isset($data['parent'])) {
                $parent->fill($data['parent'])->save();
            }
            if (isset($data['student'])) {
                $data['student']['school_system'] = SchoolSystemOptions::normalize($data['student']['school_system'] ?? null);
                $student->fill($data['student'])->save();
            }
        });

        return response()->json(['message' => 'User updated successfully.']);
    }

    /**
     * Show the security view for a student.
     */
    public function show_security(Request $request)
    {
        $stu_id = $request->id;
        $student = Student::findOrFail($stu_id);
        $grade_levels = GradeLevel::where('active', 1)->orderBy('level_order', 'asc')->get();
        $programs = SchoolProgram::where('active', 1)->get();
        $services_types = Services_type::where('active', 1)->get();

        return view('admin.Students.security', compact('student', 'grade_levels', 'programs', 'services_types'));
    }
}
