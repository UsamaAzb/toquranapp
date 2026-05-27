@extends('layouts/layoutMaster')

@section('title', $title ?? ($episode->title ?? 'Friends Episode'))
@section('meta_description', 'Watch this Friends episode for listening and language practice.')
@section('content')
  <!--<link rel="stylesheet" href="{{asset('public/front/css/custom.css')}}">-->
@include('front.partials.assigned-resource-back-button')
<div class="" id="">
    
    <div class="img-header header-overlay ">
    <div class="container">
         <h2 class="header-title border-color" >{{$season->title}}</h2>
         <div class="col-md-12 text-center page_inside_text">
<h1 style="color:white;font-size:20px;line-height: 30px!important;">{{$episode->title}}</h1>
    </div>
    </div>
</div>
    
    

  <div class="secdiv"></div>

<div class="container box-div row justify-content-center">
    @if(!empty($episode->link))
    
    <div class="col-lg-9 col-md-10 col-12 mb-3">

                                        
                                        
<!--    <video id="sample_video" width="800" height="600" controls>-->
<!--<source src="{{asset($episode->link)}}" type="video/avi">-->
<!--<track label="English" kind="subtitles" srclang="en" src="{{asset($episode->subtitles)}}" default>-->
<!--</video>-->



<video id="sample_video" width="100%" height="100%" controls  controlsList="nodownload">
<source src="{{ \App\Helpers\Helpers::publicAsset($episode->link) }}" type="video/mp4">
@if($episode->subtitles)
<track label="English" kind="subtitles" srclang="en" src="{{ \App\Helpers\Helpers::publicAsset($episode->subtitles) }}" default>
@endif
</video>


   </div>
  

@endif
</div>
</div>







@endsection
