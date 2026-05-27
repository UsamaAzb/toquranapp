@extends('layouts/layoutMaster')

@section('title', $title ?? ($notice_note->title ?? 'Notice & Note'))
@section('meta_description', 'Review this Notice and Note close-reading resource.')

@section('content')
  <!--<link rel="stylesheet" href="{{asset('public/front/css/custom.css')}}">-->
@include('front.partials.assigned-resource-back-button')
<div class="" id="">
    
    <div class="img-header header-overlay ">
    <div class="container">
         <h2 class="header-title border-color" style="font-size:24px">
           @if(request()->filled('return_to'))
             Notice & Note
           @else
             <a href="{{ url('course/notice-note') }}">Notice & Note</a>
           @endif
         </h2>
         <div class="col-md-12 text-center page_inside_text">
<h1 style="color:white;font-size:20px;line-height: 30px!important;">{{$notice_note->title}}</h1>
    </div>
    </div>
</div>
    
    

  <div class="secdiv"></div>

<div class="container box-div row justify-content-center">
    @if(!empty($notice_note->video_link))
    
    <div class="col-lg-9 col-md-10 col-12 mb-3">

                                        
                                        




<video id="sample_video" width="100%" height="100%" controls  controlsList="nodownload">
<source src="{{ \App\Helpers\Helpers::publicAsset($notice_note->video_link) }}" type="video/mp4">
</video>


   </div>
  

@endif
</div>
</div>







@endsection
