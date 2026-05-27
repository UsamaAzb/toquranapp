<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * Display a listing of the grades.
     */
    public function index()
    {
        $grades = Grade::orderBy('level_order')->get();

        return view('admin.grades.index', compact('grades'));
    }

    /**
     * Show the form for creating a new grade.
     */
    public function create()
    {
        $subjects = Subject::orderBy('title')->get();

        return view('admin.grades.create', compact('subjects'));
    }

    /**
     * Store a newly created grade in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'level_order' => 'required|integer|min:1|max:12',
            'code' => 'nullable|string',
            'subjects' => 'nullable|array',
        ]);

        $grade = Grade::create([
            'title' => $validated['title'],
            'level_order' => $validated['level_order'],
            'code' => $validated['code'] ?? null,
        ]);

        if (isset($validated['subjects'])) {
            $grade->subjects()->attach($validated['subjects']);
        }

        return redirect()->route('grades.index')
            ->with('success', 'Grade created successfully.');
    }

    /**
     * Display the specified grade.
     */
    public function show(Grade $grade)
    {
        $grade->load(['subjects', 'classes']);

        return view('admin.grades.show', compact('grade'));
    }

    /**
     * Show the form for editing the specified grade.
     */
    public function edit(Grade $grade)
    {
        $subjects = Subject::orderBy('title')->get();
        $selectedSubjects = $grade->subjects->pluck('id')->toArray();

        return view('admin.grades.edit', compact('grade', 'subjects', 'selectedSubjects'));
    }

    /**
     * Update the specified grade in storage.
     */
    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'level_order' => 'required|integer|min:1|max:12',
            'code' => 'nullable|string',
            'subjects' => 'nullable|array',
        ]);

        $grade->update([
            'title' => $validated['title'],
            'level_order' => $validated['level_order'],
            'code' => $validated['code'] ?? null,
        ]);

        // Sync subjects
        if (isset($validated['subjects'])) {
            $grade->subjects()->sync($validated['subjects']);
        } else {
            $grade->subjects()->detach();
        }

        return redirect()->route('grades.index')
            ->with('success', 'Grade updated successfully.');
    }

    /**
     * Remove the specified grade from storage.
     */
    public function destroy(Grade $grade)
    {
        // Check if grade has classes before deleting
        if ($grade->classes()->count() > 0) {
            return redirect()->route('grades.index')
                ->with('error', 'Cannot delete grade with associated classes.');
        }

        $grade->subjects()->detach();
        $grade->delete();

        return redirect()->route('grades.index')
            ->with('success', 'Grade deleted successfully.');
    }
}
