<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function get_grades(Request $request)
    {
        $lang = $request->lang;
        $slider = 0;

        $grades = Category::where('subject_id', 1)->where('active', 1)->get();
        if ($lang == 'ar') {
            $title = 'grades';
            $desc = 'grades';
            $other_lang = 'en/subject/language-literature';

            return view('ar.Category.grades', compact('lang', 'grades', 'title', 'desc', 'other_lang', 'slider'));

        } elseif ($lang == 'en') {
            $title = 'grades';
            $desc = 'grades';
            $other_lang = 'ar/subject/language-literature';

            return view('en.Category.grades', compact('lang', 'grades', 'title', 'desc', 'other_lang', 'slider'));

        } else {
            $title = 'grades';
            $desc = 'grades';
            $other_lang = 'ar/subject/language-literature';

            return view('en.Category.grades', compact('lang', 'grades', 'title', 'desc', 'other_lang', 'slider'));
        }
    }

    public function get_grades_details(Request $request)
    {
        $grade = $request->grade;
        $lang = $request->lang;
        $slider = 0;
        $grades = Category::where('subject_id', 1)->where('active', 1)->get();
        if ($lang == 'ar') {
            $title = 'grades';
            $desc = 'grades';
            $other_lang = 'en/subject/language-literature/'.$grade;

            return view('ar.Category.grade_details', compact('lang', 'grades', 'title', 'desc', 'other_lang', 'slider'));

        } elseif ($lang == 'en') {
            $title = 'grades';
            $desc = 'grades';
            $other_lang = 'ar/subject/language-literature/'.$grade;

            return view('en.Category.grade_details', compact('lang', 'grades', 'title', 'desc', 'other_lang', 'slider'));

        } else {
            $title = 'grades';
            $desc = 'grades';
            $other_lang = 'ar/subject/language-literature/'.$grade;

            return view('en.Category.grade_details', compact('lang', 'grades', 'title', 'desc', 'other_lang', 'slider'));
        }
        // $grades=Category::where('subject_id',1)->get();
        //   return view('front.Category.grade_details',compact('grade'));

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        //
    }
}
