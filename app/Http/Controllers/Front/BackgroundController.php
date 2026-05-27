<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Background;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackgroundController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {

            $title = 'Background Reading';
            $desc = 'Browse background readings and context-building materials.';
            $slider = '0';
            $other_lang = 'ar';
            $backgrounds = Background::orderby('sort')->get();
            $breadcrumb_links = $this->resourceBreadcrumbs('Background');

            return view('front.background.index', compact('backgrounds', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
        } else {
            return redirect('en/login');

        }
    }

    public function get_desc(Request $request)
    {

        if (Auth::check()) {

            $slug1 = $request->slug;
            $slug = 'background/'.$slug1;
            // $slug2=$request->slug2;
            // $slug=$slug1.'/'.$slug2;
            $background = Background::where('slug', $slug)->firstOrFail();
            $ch_title = $background->title;
            $title = $ch_title;
            $desc = $ch_title;
            $parent_id = $background->parent_id;
            $background_parent = Background::findOrFail($parent_id);
            $breadcrumb_links = $this->resourceBreadcrumbs('Background', url('course/background'), $ch_title);

            return view('front.background.answer', compact('background_parent', 'background', 'title', 'desc', 'breadcrumb_links'));
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
