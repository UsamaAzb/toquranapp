<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Court_video;
use App\Models\Student;
use App\Models\Ted_video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    public function show_ted(Request $request)
    {
        if (Auth::check()) {
            // if(Auth::user()->hasRole('student')){
            $videos = Ted_video::all();
            $breadcrumb_links = $this->resourceBreadcrumbs('TED');

            return view('front.youtube_videos.ted', compact('videos', 'breadcrumb_links'));
            // }

        } else {
            return redirect('en/login');
        }
    }

    public function show_court(Request $request)
    {
        if (Auth::check()) {
            // if(Auth::user()->hasRole('student')){
            $stu = Auth::user()->student;
            //             $course_id=$stu->course_id;
            // if($course_id==2){
            $videos = Court_video::orderby('title', 'asc')->get();
            $breadcrumb_links = $this->resourceBreadcrumbs('Court');

            return view('front.youtube_videos.court', compact('videos', 'breadcrumb_links'));
        } else {
            return redirect('en/login');
        }

        // }

    }

    private function resourceBreadcrumbs(string $resourceTitle): array
    {
        $links = [];

        if (! request()->filled('return_to') && Auth::user()?->hasAnyRole(['admin', 'teacher'])) {
            $links['Library'] = route('teacher.get_library');
        }

        $links[$resourceTitle] = null;

        return $links;
    }
}
