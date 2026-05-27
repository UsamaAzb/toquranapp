<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\GradeLevel;
use App\Models\User;
use Illuminate\Http\Request;

class ClassesController extends Controller
{
    /**
     * Display a listing of the classes.
     */
    public function index()
    {
        $classes = ClassModel::with('gradeLevel')->orderBy('title')->get();

        return view('admin.classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new class.
     */
    public function create()
    {
        $gradeLevels = GradeLevel::where('active', 1)->orderBy('level_order')->get();
        $teachers = User::role('teacher')->orderBy('first_name')->get();
        $students = User::role('student')->orderBy('first_name')->get();

        return view('admin.classes.create', compact('gradeLevels', 'teachers', 'students'));
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_img' => 'nullable|string',
            'subjects' => 'nullable|array',
            'students' => 'nullable|array',
        ]);

        $class = ClassModel::create([
            'title' => $validated['title'],
            'grade_level_id' => $validated['grade_level_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'class_img' => $validated['class_img'] ?? null,
        ]);

        // Attach subjects if provided
        if (isset($validated['subjects'])) {
            $class->subjects()->attach($validated['subjects']);
        }

        // Attach students with required pivot data
        if (isset($validated['students'])) {
            foreach ($validated['students'] as $studentId) {
                $class->students()->attach($studentId, [
                    'from_date' => now(),
                    'status' => 'current',
                ]);
            }
        }

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified class.
     */
    public function show(ClassModel $class)
    {
        $class->load(['gradeLevel', 'subjects', 'teacherSubjectClasses', 'students']);

        return view('admin.classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(ClassModel $class)
    {
        $gradeLevels = GradeLevel::where('active', 1)->orderBy('level_order')->get();
        $students = User::role('student')->orderBy('first_name')->get();

        $selectedSubjects = $class->subjects->pluck('id')->toArray();
        $selectedStudents = $class->students->pluck('id')->toArray();

        return view('admin.classes.edit', compact(
            'class', 'gradeLevels', 'students',
            'selectedSubjects', 'selectedStudents'
        ));
    }

    /**
     * Update the specified class in storage.
     */
    public function update(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_img' => 'nullable|string',
            'subjects' => 'nullable|array',
            'students' => 'nullable|array',
        ]);

        $class->update([
            'title' => $validated['title'],
            'grade_level_id' => $validated['grade_level_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'class_img' => $validated['class_img'] ?? null,
        ]);

        // Sync subjects
        if (isset($validated['subjects'])) {
            $class->subjects()->sync($validated['subjects']);
        } else {
            $class->subjects()->detach();
        }

        // Sync students with pivot data
        if (isset($validated['students'])) {
            $syncData = [];
            foreach ($validated['students'] as $studentId) {
                $syncData[$studentId] = ['from_date' => now(), 'status' => 'current'];
            }
            $class->students()->sync($syncData);
        } else {
            $class->students()->detach();
        }

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(ClassModel $class)
    {
        // Detach all relationships before deleting
        $class->subjects()->detach();
        $class->students()->detach();

        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class deleted successfully.');
    }
}
