@extends('layouts/layoutMaster')

@section('title', $title ?? ($background->title ?? 'Background Reading'))
@section('meta_description', 'Review this background reading and its supporting material.')

@section('content')
  <!--<link rel="stylesheet" href="{{asset('public/front/css/custom.css')}}">-->
@include('front.partials.assigned-resource-back-button')
<div class="" id="">
    
    <div class="img-header header-overlay ">
    <div class="container">
         <h2 class="header-title border-color" style="font-size:24px">
           @if(request()->filled('return_to'))
             {{ $background_parent->title }}
           @else
             <a href="{{ url('course/background') }}">{{ $background_parent->title }}</a>
           @endif
         </h2>
         <div class="col-md-12 text-center page_inside_text">
<h1 style="color:white;font-size:20px;line-height: 30px!important;">{{$background->title}}</h1>
    </div>
    </div>
</div>
    
    

  <div class="secdiv"></div>

<div class="container box-div row justify-content-center">
    @if(!empty($background->pdf_link))
    
    <div class="col-lg-12 col-md-12 col-12 mb-3">

                                        
                                        

 <iframe id="iframe" src="{{ \App\Helpers\Helpers::publicAsset($background->pdf_link) }}#toolbar=0" width="100%" height="800">
    </iframe>

<!--<video id="sample_video" width="100%" height="100%" controls  controlsList="nodownload">-->
<!--<source src="{{asset($background->pdf_link)}}" type="video/mp4">-->
<!--</video>-->


   </div>
  

@endif
</div>
</div>







@endsection
