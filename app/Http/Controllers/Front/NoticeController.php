<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Notice_note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {

            $title = 'Notice & Note';
            $desc = 'Browse Notice and Note close-reading signposts and discussion prompts.';
            $slider = '0';
            $other_lang = 'ar';
            $notice_note = Notice_note::orderby('sort')->get();
            $breadcrumb_links = $this->resourceBreadcrumbs('Notice & Note');

            return view('front.notice.index', compact('notice_note', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
        } else {
            return redirect('en/login');

        }
    }

    public function get_desc(Request $request)
    {

        if (Auth::check()) {

            $slug1 = $request->slug;

            $notice_note = Notice_note::where('slug', $slug1)->firstOrFail();
            $ch_title = $notice_note->title;
            $title = $ch_title;
            $desc = $ch_title;
            // $parent_id=$notice_note->parent_id;
            // $grammar_parent=Notice_note::find($parent_id);
            $breadcrumb_links = $this->resourceBreadcrumbs('Notice & Note', url('course/notice-note'), $ch_title);

            return view('front.notice.answer', compact('notice_note', 'title', 'desc', 'breadcrumb_links'));
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
