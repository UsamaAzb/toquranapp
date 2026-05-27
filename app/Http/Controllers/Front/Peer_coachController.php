<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Peer_coach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Peer_coachController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {

            $title = 'Peer Coach';
            $desc = 'Browse peer-coaching activities and structured discussion tasks.';
            $slider = '0';
            $other_lang = 'ar';
            $peer_coach = Peer_coach::orderby('sort')->get();
            $breadcrumb_links = $this->resourceBreadcrumbs('Peer Coach');

            return view('front.peer_coach.index', compact('peer_coach', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
        } else {
            return redirect()->route('login');

        }
    }

    public function get_desc(Request $request)
    {

        if (Auth::check()) {

            $slug1 = $request->slug1;
            $slug2 = $request->slug2;
            if (blank($slug1) || blank($slug2)) {
                abort(404);
            }
            $slug = $slug1.'/'.$slug2;

            $peer_coach = Peer_coach::where('slug', $slug)->firstOrFail();
            $ch_title = $peer_coach->title;
            $title = $ch_title;
            $desc = $ch_title;
            $parent_id = $peer_coach->parent_id;
            $peer_parent = Peer_coach::findOrFail($parent_id);
            $breadcrumb_links = $this->resourceBreadcrumbs('Peer Coach', url('course/peer-coach'), $ch_title);

            return view('front.peer_coach.answer', compact('peer_coach', 'title', 'desc', 'peer_parent', 'parent_id', 'breadcrumb_links'));
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
