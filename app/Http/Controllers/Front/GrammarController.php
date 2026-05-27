<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Grammar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrammarController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {

            $title = 'Grammar Practice';
            $desc = 'Browse grammar references and practice pathways.';
            $slider = '0';
            $other_lang = 'ar';
            $grammars = Grammar::orderby('sort')->get();
            $breadcrumb_links = $this->resourceBreadcrumbs('Grammar');

            return view('front.grammar.index', compact('grammars', 'title', 'desc', 'slider', 'other_lang', 'breadcrumb_links'));
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
            $grammar = Grammar::where('slug', $slug)->firstOrFail();
            $ch_title = $grammar->title;
            $title = $ch_title;
            $desc = $ch_title;
            $parent_id = $grammar->parent_id;
            $grammar_parent = Grammar::find($parent_id);
            $breadcrumb_links = $this->resourceBreadcrumbs('Grammar', url('course/grammar'), $ch_title);

            return view('front.grammar.answer', compact('grammar_parent', 'grammar', 'title', 'desc', 'breadcrumb_links'));
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
