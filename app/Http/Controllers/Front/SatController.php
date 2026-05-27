<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Level_up;
use App\Models\Sat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SatController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {

            $title = 'SAT Practice';
            $desc = 'Browse SAT reading and writing practice sets.';
            $slider = '0';
            $other_lang = 'ar';
            $sats = Sat::orderby('sort')->get();
            $breadcrumb_links = $this->resourceBreadcrumbs('SAT');

            return view('front.sat.index', compact('sats', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
        } else {
            return redirect('en/login');

        }
    }

    public function get_desc(Request $request)
    {

        if (Auth::check()) {

            $slug1 = $request->slug1;
            $slug2 = $request->slug2;
            $slug = $slug1.'/'.$slug2;
            $sat = Sat::where('slug', $slug2)->firstorFail();
            $ch_title = $sat->title;
            $title = $ch_title;
            $desc = $ch_title;
            $parent_id = $sat->parent_id;
            $sat_parent = Sat::find($parent_id);
            $breadcrumb_links = $this->resourceBreadcrumbs('SAT', url('course/sat'), $ch_title);

            return view('front.sat.answer', compact('sat_parent', 'sat', 'title', 'desc', 'breadcrumb_links'));
        } else {
            return redirect()->route('login');
        }
    }

    public function get_tutriols_level(Request $request)
    {
        if (Auth::check()) {

            $title = 'Level up Tutorials';
            $desc = 'Level up Tutorials';
            $slider = '0';
            $other_lang = 'ar';
            $levelups = Level_up::orderby('sort')->get();
            $breadcrumb_links = $this->resourceBreadcrumbs('Level up Tutorials');

            return view('front.Levelup.index', compact('levelups', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
        } else {
            return redirect('en/login');

        }
    }

    public function get_tutriols_lesson(Request $request)
    {
        if (Auth::check()) {

            $slug = $request->slug;
            $level = Level_up::where('slug', $slug)->firstOrFail();
            $lev_title = $level->title;
            $title = $lev_title;
            $desc = $lev_title;
            $slider = '0';
            $other_lang = 'ar';
            $breadcrumb_links = $this->resourceBreadcrumbs('Level up Tutorials', url('tutriols/level-up'), $lev_title);

            return view('front.Levelup.level', compact('level', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
        } else {
            return redirect('en/login');

        }
    }

    private function resourceBreadcrumbs(string $resourceTitle, ?string $resourceUrl = null, ?string $currentTitle = null): array
    {
        $links = [];
        $isAssignedResourceOpen = request()->filled('return_to');

        if (! $isAssignedResourceOpen && Auth::user()?->hasAnyRole(['admin', 'teacher'])) {
            $links['Library'] = route('teacher.get_library');
        }

        $links[$resourceTitle] = $currentTitle && ! $isAssignedResourceOpen ? $resourceUrl : null;

        if ($currentTitle) {
            $links[$currentTitle] = null;
        }

        return $links;
    }
}
