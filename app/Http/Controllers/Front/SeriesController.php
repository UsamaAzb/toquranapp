<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Series_episode;
use App\Models\Series_season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeriesController extends Controller
{
    public function tv_series_avatar(Request $request)
    {
        if (Auth::check()) {

            $title = 'Avatar Episodes';
            $desc = 'Browse Avatar episodes for story-based listening and language practice.';
            $slider = '0';
            $other_lang = 'ar';
            $seasons = Series_season::where('type_id', 2)->get();
            if (isset($request->d)) {
                $story_d = $request->d;
            } else {
                $story_d = 0;
            }
            $breadcrumb_links = $this->resourceBreadcrumbs('Avatar');

            //  dd($seasons);
            return view('front.Series.avatar_index', compact('story_d', 'seasons', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
        } else {
            return redirect('en/login');

        }
    }

    public function get_tv_series_avatar(Request $request)
    {

        if (Auth::check()) {

            $slug1 = $request->slug1;
            $slug2 = $request->slug2;
            $slug = $slug1.'/'.$slug2;

            $episode = Series_episode::where('slug', $slug)->where('type_id', 2)->first();

            if (! $episode) {
                abort(404, 'Episode not found');
            }

            $ch_title = $episode->title;
            $title = $ch_title;
            $desc = $ch_title;
            $season_id = $episode->series_season_id;
            $season = Series_season::find($season_id);
            $breadcrumb_links = $this->resourceBreadcrumbs('Avatar', url('tv_series/avatar'), $ch_title);

            return view('front.Series.avatar_episodes', compact('episode', 'season', 'title', 'desc', 'breadcrumb_links'));

        } else {
            return redirect('en/login');

        }
    }

    public function tv_series_friends(Request $request)
    {
        if (Auth::check()) {
            // if(Auth::user()->hasRole('student')){
            // $pivot_id = Auth::user()->pivot_id;
            // $stu=Auth::user()->student;
            // $course_id=$stu->course_id;
            // if($course_id==2){

            $title = 'Friends Episodes';
            $desc = 'Browse Friends episodes for language practice with familiar scenes and dialogue.';
            $slider = '0';
            $other_lang = 'ar';
            $seasons = Series_season::where('type_id', 1)->get();
            if (isset($request->d)) {
                $story_d = $request->d;
            } else {
                $story_d = 0;
            }
            $breadcrumb_links = $this->resourceBreadcrumbs('Friends');

            return view('front.Series.friends_index', compact('story_d', 'seasons', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
        } else {
            return redirect('en/login');
        }
    }

    //  }}
    public function get_tv_series_friends(Request $request)
    {
        if (Auth::check()) {

            $pivot_id = Auth::user()->pivot_id;
            $slug1 = $request->slug1;
            $slug2 = $request->slug2;
            $slug = $slug1.'/'.$slug2;
            $episode = Series_episode::where('slug', $slug)->where('type_id', 1)->first();

            if (! $episode) {
                abort(404, 'Episode not found');
            }

            $ch_title = $episode->title;
            $title = $ch_title;
            $desc = $ch_title;
            $season_id = $episode->series_season_id;
            $season = Series_season::find($season_id);
            $breadcrumb_links = $this->resourceBreadcrumbs('Friends', url('tv_series/friends'), $ch_title);
            //  dd($season);

            return view('front.Series.friends_episodes', compact('episode', 'season', 'title', 'desc', 'breadcrumb_links'));
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
