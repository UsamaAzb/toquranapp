<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Level_up;
use App\Models\Story;
use App\Models\Story_chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {

            $title = 'Listen & Read';
            $desc = 'Browse independent reading stories and chapters for listening and reading practice.';
            $slider = '0';
            $other_lang = 'ar';
            $stories = Story::orderby('sort')->get();
            if (isset($request->d)) {
                $story_d = $request->d;
            } else {
                $story_d = 0;
            }
            $breadcrumb_links = $this->resourceBreadcrumbs('Listen & Read');

            return view('front.Story.index', compact('story_d', 'stories', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
        } else {
            return redirect()->route('login');

        }
    }

    public function get_chapters(Request $request)
    {
        // dd('tyuyt');
        if (Auth::check()) {

            $slug1 = $request->slug1;
            $slug2 = $request->slug2;
            $slug = $slug1.'/'.$slug2;
            $chapter = Story_chapter::where('slug', $slug)->first();
            if (! $chapter) {
                abort(404);
            }
            $ch_title = $chapter->title;
            $title = $ch_title;
            $desc = $ch_title;
            $story_id = $chapter->story_id;
            $story = Story::find($story_id);
            $breadcrumb_links = $this->resourceBreadcrumbs('Listen & Read', url('reading/listen-read'), $ch_title);

            return view('front.Story.chapter', compact('chapter', 'story', 'title', 'desc', 'breadcrumb_links'));
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
            return redirect()->route('login');

        }
    }

    public function get_tutriols_lesson(Request $request)
    {
        if (Auth::check()) {

            $slug = $request->slug;
            $level = Level_up::where('slug', $slug)->first();
            if (! $level) {
                abort(404);
            }
            $lev_title = $level->title;
            $title = $lev_title;
            $desc = $lev_title;
            $slider = '0';
            $other_lang = 'ar';
            $breadcrumb_links = $this->resourceBreadcrumbs('Level up Tutorials', url('tutriols/level-up'), $lev_title);

            return view('front.Levelup.level', compact('level', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
        } else {
            return redirect()->route('login');

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
