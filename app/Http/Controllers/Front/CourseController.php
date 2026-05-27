<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Audio_unit;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function get_levels(Request $request)
    {
        $user = Auth::user();

        if (! $user->hasRole('student')) {
            abort(403);
        }

        $student = $user->student;

        if (! $student) {
            abort(404);
        }

        if ((int) $student->course_id !== 2) {
            abort(403, 'Access denied: Course not available for your account.');
        }

        $levelIds = array_filter(array_map('trim', explode(',', (string) $student->level_id)));

        $all_levels = Level::whereIn('id', $levelIds)->orderBy('id', 'ASC')->get();

        return view('front.levels', compact('all_levels'));
    }

    public function get_units(Request $request)
    {
        $user = Auth::user();

        if (! $user->hasRole('student')) {
            abort(403);
        }

        $student = $user->student;

        if (! $student) {
            abort(404);
        }

        if ((int) $student->course_id !== 2) {
            abort(403, 'Access denied: Course not available for your account.');
        }

        $user_id = $student->id;
        $id = $request->id;
        $lang = $request->lang;
        $title = 'courses';
        $desc = 'courses';

        $levelIds = array_values(array_filter(array_map('trim', explode(',', (string) $student->level_id))));
        $all_levels = Level::whereIn('id', $levelIds)->orderBy('id', 'ASC')->get();
        $first_level = $levelIds[0] ?? null;

        if (! $first_level) {
            abort(404);
        }

        $targetLevel = $id ?: $first_level;

        // Verify requested level is in the student's assigned levels
        if (! in_array((string) $targetLevel, $levelIds, true)) {
            abort(403, 'Access denied: Level not assigned to your account.');
        }

        $units = Audio_unit::where('level_id', $targetLevel)->where('active', 1)->orderBy('order', 'ASC')->get();
        $level = Level::find($targetLevel);

        return view('front.take_course', compact('level', 'all_levels', 'units', 'user_id', 'student', 'lang', 'title', 'desc'));
    }
}
